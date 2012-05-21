<?php
/**
 * User Model
 * 
 * Copyright (c) 2012, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\models;

use hydrogen\model\Model;
use hydrogen\config\Config;
use hydrogen\database\Query;
use hydrogen\database\DatabaseEngineFactory;

use darkcrusader\sqlbeans\UserBean;
use darkcrusader\sqlbeans\UserGroupBean;
use darkcrusader\sqlbeans\AutologinBean;

use darkcrusader\permissions\PermissionSet;

use darkcrusader\user\exceptions\UsernameAlreadyRegisteredException;
use darkcrusader\user\exceptions\NoSuchUserException;
use darkcrusader\user\exceptions\PasswordIncorrectException;

use darkcrusader\models\UserGroupModel;
use darkcrusader\models\PermissionsModel;

class UserModel extends Model {

	protected static $modelID = "user";
	protected static $cachedActiveUser;

	/**
	 * userIsLoggedIn
	 * Checks if a user is logged in
	 *
	 * @return boolean true if a user is logged in,
	 *			 false if not.
	 */
	public function userIsLoggedIn() {
		if ($_SESSION["userID"])
			return true;

		if ($_COOKIE["public_key"]) {
			// Split the cookie into a user ID and public key
			$working = str_split($_COOKIE["public_key"]);
			$cookieLength = strlen($_COOKIE["public_key"]);
			$userIDLength = $cookieLength - 32;

			$userID = substr($_COOKIE["public_key"], 0, $userIDLength);
			$publicKey = substr($_COOKIE["public_key"], -32);

			// Attempt to pull a row from the database
			$query = new Query("SELECT");
			$query->where("user_id = ?", $userID);
			$query->where("public_key = ?", $publicKey, "AND");
			$abs = AutologinBean::select($query, true);
			$ab = $abs[0];

			// No result? Unset cookie and return false
			if (!$ab) {
				setcookie("public_key", "byebye", time()-3600, "/");
				return false;
			}

			// Get the user from the mapping attached to the autologin bean
			$ub = $ab->getMapped("user");

			// Generate the private key again
			$privateKey = md5(md5($ub->passhash) . md5($_SERVER["HTTP_USER_AGENT"]));

			/* Check if the curent private key matches the originally generated
			 * one. If it does not, someone might have stolen the cookie
			 * or the user might have changed their password
			 */
			if ($privateKey != $ab->private_key) {
				setcookie("public_key", "byebye", time()-3600, "/");
				return false;
			}

			// Login the user to the session
			$_SESSION["userID"] = $ub->id;

			// Return true (a user is logged in)
			return true;
		}

		return false;
	}

	/**
	 * Checks if an email address has been registered
	 * @param string $email email address
	 * @return boolean true if email is registered, false if not
	 */
	public function emailIsRegistered($email) {
		$q = (new Query("SELECT"))->where("email LIKE ?", $email);

		$ubs = UserBean::select($q);
		return ($ubs[0]) ? true : false;

	}

	/**
	 * register
	 * Registers a user
	 *
	 * @param string $username username
	 * @param string $password password
	 * @return boolean true if successful
	 * @throws UsernameAlreadyRegisteredException if username is taken
	 */
	public function register($username, $password) {
		$ugb = UserGroupModel::getInstance()->getUserGroup(false, "user");

		$q = new Query("SELECT");
		$q->where("username LIKE ?", $username);
		$ubs = UserBean::select($q);
		if ($ubs[0])
			throw new UsernameAlreadyRegisteredException;

		$this->addUser($username, $password, $ugb->id);
	}

	/**
	 * login
	 * Logs a user in
	 *
	 * @param string $username username to log in
	 * @param string $password password of the user
	 * @throws NoSuchUserException if user does not exist
	 * @throws PasswordIncorrectException if password is incorrect
	 */
	public function login($username, $password, $adminAuth=false) {

		// Attempts to pull a database record of the user
		$query = new Query("SELECT");
		$query->where("username LIKE ?", $username);
		$query->limit(1);
		$userBeans = UserBean::select($query, true);
		$userBean = $userBeans[0];

		if (!$userBean)
			throw new NoSuchUserException;

		// Load in the details into more friendly variable names
		$correctPasshash = $userBean->passhash;

		// Test the given passhash against the correct one
		if (!$this->_isPasswordCorrect($correctPasshash, $password))
			throw new PasswordIncorrectException;

		// Set the session variable
		$_SESSION["userID"] = $userBean->id;
	}

	/**
	 * createAutologin
	 * Creates an autologin/"remember me" for a year (which
	 * might as well be indefinite) for the current logged in user
	 *
	 * @return mixed boolean true if successful, error string if
	 *		     failure
	 */
	public function createAutologin() {

		/// Check if the user is logged in
		if (!$this->userIsLoggedIn())
			return "Error! Unable to create autologin, no user logged in.";

		// Grab the user's info
		$user = $this->getActiveUser(false);
		if (!$user)
			return "Error! Unable to create autologin, no user logged in.";

		// Clear all autologins for this user currently set
		// ^^ Why exactly? lol
		/*$query = new Query("DELETE");
		$query->from("autologin");
		$query->where("user_id = ?", $user->id);
		$stmt = $query->prepare();
		$stmt->execute();*/

		// Generate a public and private key
		$publicKey = md5($user->email . microtime() . rand(1,100000));
		$privateKey = md5(md5($user->passhash) . md5($_SERVER["HTTP_USER_AGENT"]));

		// Add the autologin to the database
		$ab = new AutologinBean;
		$ab->user_id = $user->id;
		$ab->public_key = $publicKey;
		$ab->private_key = $privateKey;
		$ab->set("created_on", "NOW()", true);
		$ab->insert();

		// Set the cookie
		$result = setcookie("public_key", $user->id . $publicKey, time()+60*60*24*365, "/");

		return true;
	}

	/**
	 * Logout
	 * Logs the current user out
	 *
	 * @return mixed boolean true if successful, error
	 *		     string if failure
	 */
	public function logout() {
		if ($this->userIsLoggedIn() === false)
			return "Error! User is not logged in";

		$user = $this->getActiveUser();

		$query = new Query("DELETE");
		$query->from("autologin");
		$query->where("user_id = ?", $user->id);
		$stmt = $query->prepare();
		$stmt->execute();

		unset($_SESSION["userID"]);
		return true;

	}

	/**
	 * getActiveUser
	 * Gets the current user as a UserBean
	 *
	 * @return UserBean current user
	 */
	public function getActiveUser($cache=true) {

		// Check if we already retrieved stats (if cache is allowed)
		if (($cache) && (static::$cachedActiveUser))
			return static::$cachedActiveUser;

		// Check if there's a user logged in
		if ($this->userIsLoggedIn()) {
			// Make the query for the user
			$query = new Query("SELECT");
			$query->where("users.id = ?", $_SESSION["userID"]);
			$query->limit(1);
			$ubs = UserBean::select($query, true);
			$ub = $ubs[0];
		}

		// If we don't have a user, return a fake Guest user
		if (!$ub) {
			$q = new Query("SELECT");
			$q->where("group_name = ?", "guest");
			$ugbs = UserGroupBean::select($q);
			$ugb = $ugbs[0];

			$ub = new \stdClass;
			$ub->permissions = PermissionsModel::getInstance()->constructPermissionSet($ugb->getPermissions(), false);

		}

		static::$cachedActiveUser = $ub;
		return $ub;
	}

	/**
	 * addUser
	 * Adds a new user
	 *
	 * @param string $username username to register
	 * @param string $password password to register
	 * @param int $group user group id to register under
	 */
	public function addUser($username, $password, $groupID) {
		$ub = new UserBean;
		$ub->username = $username;
		$ub->passhash = $this->_calculatePasswordHash($password);
		$ub->group_id = $groupID;
		$ub->insert();
	}

	/**
	 * deleteUser
	 * Deletes a user by ID
	 *
	 * @param int $id user ID to delete
	 * @return mixed boolean true if successful, error string
	 *				 if failure
	 */
	public function deleteUser($id) {
		$u = $this->getUser($id);
		if (!$u)
			return "Error! No user with that ID";
		$u->delete();

		return true;
	}

	/**
	 * getUserList
	 * Gets a list of users in alphabetical order and
	 * returns them as an array
	 *
	 * @param int page number, default return everything
	 * @return array array of UserBean objects
	 */
	public function getUserList($pageNumber=false) {

		// Create the query
		$query = new Query("SELECT");
		$query->orderby("username", "ASC");

		if ($pageNumber) {
			$startRecord = $pageNumber * 9;
			$startRecord = $startRecord - 9;

			$query->limit(9, $startRecord);
		}

		// Select them using UserBean
		$userArray = UserBean::select($query, true);

		// Return the user array
		return $userArray;
	}

	/**
	 * getUser
	 * Gets a user by id and returns it as a
	 * \darkcrusader\user\UserWrapper object
	 *
	 * @param int $id user id
	 * @return UserBean user bean
	 */
	public function getUser($id=false, $username=false) {

		// Make the query for the user
		$q = new Query("SELECT");

		if ($id)
			$q->where("users.id = ?", $id);

		if ($username)
			$q->where("username = ?", $username);

		$q->limit(1);
		$ubs = UserBean::select($q, true);
		$ub = $ubs[0];

		// No user? EXCEPTION
		if (!$ub)
			throw new NoSuchUserException();

		// Return a user object
		return $ub;
	}

	/**
	 * Gets an array of UserBeans for the array of user IDs
	 * provided
	 *
	 * @param array array of user IDs
	 * @return array array of UserBeans
	 */
	public function getUsers($ids) {
		$q = new Query("SELECT");

		foreach ($ids as $id)
			$q->where("users.id = ?", $id, "OR");

		return UserBean::select($q, true);

	}

	/**
	 * Gets an array of UserBeans for the array of usernames
	 * provided
	 *
	 * @param array array of usernames
	 * @return array array of UserBeans
	 */
	public function getUsersFromUsernames($usernames) {
		$q = new Query("SELECT");

		foreach ($usernames as $username)
			$q->where("username = ?", $username, "OR");

		return UserBean::select($q, true);
	}


	/**
	 * updateUser
	 * Updates the given user ID with the given
	 * info
	 * Mote that you can pass false/null as any of the
	 * params to leave it as it is
	 *
	 * @param string $username new username
	 * @param string $password new password
	 * @param int $group user group id to register under
	 */
	public function updateUser($id, $username=false, $password=false, $groupID=false) {

		// Make the query for the user
		$q = new Query("SELECT");
		$q->where("id = ?", $id);
		$q->limit(1);
		$ubs = UserBean::select($q);
		$ub = $ubs[0];

		// No user? Exit
		if (!$ub)
			return "Error! No user with that ID";

		// Update
		if ($username)
			$ub->username = $username;
		if ($password)
			$ub->passhash = $this->_calculatePasswordHash($password);
		if ($groupID)
			$ub->group_id = $groupID;

		$ub->update();

		return true;
	}
	/**
	 * getStats
	 * Gets user statistics
	 *
	 * @return array associative array with stats
	 */
	public function getStats() {

		$ugm = UserGroupModel::getInstance();

		// get user, group and forced group count
		$userCount = $this->getNumberOfUsers();
		$userGroupCount = $ugm->getNumberOfUserGroups();

		return array(
			"userCount" => $userCount,
			"userGroupCount" => $userGroupCount);
	}

	/**
	 * getNumberOfUsers
	 * Gets the number of users registered
	 *
	 * @param int $group optionally, a group id and
	 *			   this function will only count
	 *			   users in that group
	 * @return int number of users
	 */
	public function getNumberOfUsers($group=false) {

		$q = new Query("SELECT");
		$q->from("users");
		$q->field("id");

		if ($group)
			$q->where("group_id = ?", $group);

		$stmt = $q->prepare();
		$stmt->execute();

		$i = 0;
		while($stmt->fetchObject())
			$i++;

		return $i;
	}

    /**
     * _calculatePasswordHash
     * Get the password hash (to store in the database) for a specific password.
     *
     * @param string $password The password to calculate a hash for
     * @return string password hash for storage
     */
    private function _calculatePasswordHash($password) {

		// generate a salt, with the dictionary ./0-9A-Za-z which is 22 characters long
        $salt = '';
        $dictionary = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        while (strlen($salt) < 22) {
            $salt .= $dictionary[rand(0, strlen($dictionary) - 1)];
        }

        // now get the cost parameter
        $cost_parameter = 4;

        // check that the cost parameter makes sense. we don't want badly-hashed passwords
        if ($cost_parameter < 4 || $cost_parameter > 31) {
            throw new \Exception("bcrypt_cost_parameter is invalid. It must be between 4 and 31.");
        }

        // and now we go for it:
        return crypt($password, '$2a$' . str_pad((string)$cost_parameter, 2, '0', STR_PAD_LEFT) . '$' . $salt);
    }

    /**
     * _isPasswordCorrect
     * Check if a user's password is correct, based upon a hashed and a unhashed password.
     *
     * @param string $password_from_db The password from the DB, or other trusted source
     * @param string $password_from_web The RAW password from the web, or source attempting to authenticate
     * @return boolean true if correct, false if wrong
     */
    private function _isPasswordCorrect($password_from_db, $password_from_web) {
        return (crypt($password_from_web, $password_from_db) == $password_from_db);
    }
}
?>