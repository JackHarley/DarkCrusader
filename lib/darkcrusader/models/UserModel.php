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
use darkcrusader\sqlbeans\LinkedCharacterBean;
use darkcrusader\sqlbeans\CharacterLinkRequestBean;

use darkcrusader\permissions\PermissionSet;

use darkcrusader\user\exceptions\UsernameAlreadyRegisteredException;
use darkcrusader\user\exceptions\NoSuchUserException;
use darkcrusader\user\exceptions\PasswordIncorrectException;
use darkcrusader\user\exceptions\CannotSetCharacterAsDefaultWithoutAPIKeyException;
use darkcrusader\user\exceptions\CharacterIsAlreadyLinkedException;
use darkcrusader\user\exceptions\UserDoesNotHaveSufficientFundsException;

use darkcrusader\exceptions\FormIncorrectlyFilledOutException;

use darkcrusader\oe\exceptions\APIKeyInvalidException;

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

		$this->addUser($username, $password, 0, $ugb->id);
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
	public function login($username, $password) {

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
	 * Force logs a user in without a password or username
	 * 
	 * @param int $user user id
	 */
	public function forceLogin($user) {
		$_SESSION["userID"] = $user;
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
			$ub->clearance_level = 0;
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
	 * @param int $clearanceLevel intelligence clearance level
	 * @param int $group user group id to register under
	 */
	public function addUser($username, $password, $clearanceLevel, $groupID) {
		$ub = new UserBean;
		$ub->username = $username;
		$ub->passhash = $this->_calculatePasswordHash($password);
		$ub->group_id = $groupID;
		$ub->user_clearance_level = $clearanceLevel;
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
	 * @param int $clearanceLevel intelligence clearance level
	 * @param int $group user group id to register under
	 */
	public function updateUser($id, $username=false, $password=false, $clearanceLevel=false, $groupID=false) {

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
		if ($clearanceLevel !== false)
			$ub->user_clearance_level = $clearanceLevel;

		$ub->update();

		return true;
	}

	/**
	 * Gets the linked characters associated with a user
	 * 
	 * @param int $user user id
	 * @return array array of LinkedCharacterBeans
	 */
	public function getLinkedCharacters($user) {
		$q = new Query("SELECT");
		$q->where("user_id = ?", $user);
		$q->orderby("is_default", "DESC");
		$lcbs = LinkedCharacterBean::select($q, true);
		return $lcbs;
	}

	/**
	 * Gets any character link requests for a user
	 * 
	 * @param int $user user id
	 * @return array array of CharacterLinkRequestBeans
	 */
	public function getCharacterLinkRequests($user) {
		$q = new Query("SELECT");
		$q->where("user_id = ?", $user);
		$clrbs = CharacterLinkRequestBean::select($q, true);
		return $clrbs;
	}

	/**
	 * Approves a character link request
	 * 
	 * @param int $id character link request id
	 */
	public function approveCharacterLinkRequest($id) {
		$q = new Query("SELECT");
		$q->where("id = ?", $id);
		$clrbs = CharacterLinkRequestBean::select($q);
		$clrb = $clrbs[0];
		
		$default = ($clrb->api_key) ? true : false;
		$this->addLinkedCharacter($clrb->user_id, $clrb->character_name, $clrb->api_key, $default);

		$clrb->delete();
	}

	/**
	 * Adds a linked character to a user
	 * 
	 * @param int $user user id
	 * @param string $characterName character name
	 * @param string $key api access key (optional)
	 * @param boolean $default should this character be the default
	 */
	public function addLinkedCharacter($user, $characterName, $key=false, $default=false) {
		$lcb = new LinkedCharacterBean;
		$lcb->user_id = $user;
		$lcb->character_name = $characterName;
		if ($key)
			$lcb->api_key = $key;
		if ($default)
			$lcb->is_default = 1;
		$lcb->insert();
	}

	/**
	 * Sets a linked character as default
	 * 
	 * @param int $id linked character id
	 */
	public function setDefaultCharacter($id) {
		// get the linked character
		$q = new Query("SELECT");
		$q->where("linked_characters.id = ?", $id);
		$lcbs = LinkedCharacterBean::select($q, true);
		$lcb = $lcbs[0];

		// if it doesn't have an api key, it can't be default
		if (!$lcb->api_key)
			throw new CannotSetCharacterAsDefaultWithoutAPIKeyException;

		// set all linked characters as not default
		$q = new Query("UPDATE");
		$q->table("linked_characters");
		$q->where("user_id = ?", $lcb->user_id);
		$q->set("is_default = ?", 0);
		$q->prepare()->execute();

		// set linked character as default
		$lcb->is_default = 1;
		$lcb->update();
	}

	/**
	 * Deletes a linked character
	 * 
	 * @param int $id linked character id
	 */
	public function deleteLinkedCharacter($id) {
		// get the linked character
		$q = new Query("SELECT");
		$q->where("linked_characters.id = ?", $id);
		$lcbs = LinkedCharacterBean::select($q);
		$lcb = $lcbs[0];

		// delete
		if ($lcb)
			$lcb->delete();
	}

	/**
	 * Deletes a character link request
	 * 
	 * @param int $id character link request id
	 */
	public function deleteCharacterLinkRequest($id) {
		$q = new Query("SELECT");
		$q->where("character_link_requests.id = ?", $id);
		$clrbs = CharacterLinkRequestBean::select($q);
		$clrb = $clrbs[0];

		if ($clrb)
			$clrb->delete();
	}

	/**
	 * Requests a character link
	 * 
	 * @param int $user user id
	 * @param string $characterName character name
	 * @param string $key api access key (optional)
	 */
	public function requestCharacterLink($user, $characterName, $key=false) {
		
		// if a key was supplied, test it's valid
		if ($key) {
			$keyIsValid = OuterEmpiresModel::getInstance()->testAccessKey($key);
			if ($keyIsValid === false)
				throw new APIKeyInvalidException;
		}

		// check the character isn't already linked
		$q = new Query("SELECT");
		$q->where("character_name = ?", $characterName);
		$lcbs = LinkedCharacterBean::select($q);
		$lcb = $lcbs[0];
		if ($lcb)
			throw new CharacterIsAlreadyLinkedException;

		// if a key was supplied we can also insta verify the character and either
		// exit with invalid key or instantly add a linked character
		if ($key) {
			$keyCharacterInfo = OuterEmpiresModel::getInstance()->getCharacterInfo(false, $key);
			if ($keyCharacterInfo->name != $characterName)
				throw new APIKeyInvalidException;

			$this->addLinkedCharacter($user, $characterName, $key, true);

		}
		else { // if no key, manual link request has to be done
			// now add the link request to the db
			$rand = rand(1,500);
			$this->addCharacterLinkRequest($user, $characterName, $rand, $key);
		}
	}

	/**
	 * Adds a character link request to the DB
	 * 
	 * @param int $user user id
	 * @param string $characterName character name
	 * @param int $verificationAmount amount of credits to be transferred for verification
	 * @param string $key api access key (optional)
	 */
	public function addCharacterLinkRequest($user, $characterName, $verificationAmount, $key=false) {
		$clrb = new CharacterLinkRequestBean;
		$clrb->character_name = $characterName;
		$clrb->verification_amount = $verificationAmount;
		if ($key)
			$clrb->api_key = $key;
		$clrb->set("date_requested", "NOW()", true);
		$clrb->user_id = $user;
		$clrb->insert();
	}

	/**
	 * Gets the default character for a user
	 * 
	 * @param int $user user id
	 * @return LinkedCharacterBean default character or boolean false if no character
	 */
	public function getDefaultCharacter($user) {
		$q = new Query("SELECT");
		$q->where("user_id = ?", $user);
		$q->where("is_default = ?", 1);

		$lcbs = LinkedCharacterBean::select($q, true);
		if ($lcbs[0])
			return $lcbs[0];
		else
			return false;
	}

	/**
	 * Subscribes a user to premium for the duration specified
	 * 
	 * @param int $user user id
	 * @param string $duration 'day', 'week', '30days' or 'lifetime'
	 * @param boolean $isFree set to true to not charge the user for the extension
	 */
	public function subscribeUserToPremium($user, $duration="30days", $isFree=false) {
		$user = $this->getUser($user);

		$cost = array();
		$cost["day"] = 5000;
		$cost["week"] = 18000;
		$cost["30days"] = 50000;
		$cost["lifetime"] = 500000;

		// check if user has the amount necessary and then subtract it
		if (!$isFree) {
			if ($user->balance < $cost[$duration])
				throw new UserDoesNotHaveSufficientFundsException;

			$user->balance -= $cost[$duration];
		}

		// add premium subscription

		// if we have a date in which premium ends, use it as the time to add to
		if ($user->premium_until) {
			$timePremiumEnds = strtotime($user->premium_until);

			// if the date is in the past, use the current time
			if ($timePremiumEnds < time())
				$timePremiumEnds = time();
		}
		else {
			$timePremiumEnds = time();
		}

		// now add to it and convert it back into a mysql date
		switch ($duration) {
			case "day":
				$timePremiumEnds += (60 * 60 * 24);
			break;
			case "week":
				$timePremiumEnds += (60 * 60 * 24 * 7);
			break;
			case "30days": // used instead of month to avoid "JULY HAD 31 DAYS WHYD I ONLY GET 30 DAYS"
				$timePremiumEnds += (60 * 60 * 24 * 30);
			break;
			case "lifetime": // 100 years, that should do everyone for life ;p
				$timePremiumEnds += (60 * 60 * 24 * 30 * 12 * 100);
			break;
			default:
				throw new FormIncorrectlyFilledOutException;
			break;
		}

		$user->premium_until = date("Y-m-d H:i:s", $timePremiumEnds);

		// apply the changes
		$user->update();

	}

	/**
	 * Checks if a user is premium
	 * 
	 * @param int $user user id to check
	 * @return boolean true if user is premium, otherwise false
	 */
	public function checkIfUserIsPremium($user) {
		$user = $this->getUser($user);

		if ($user->group->premium == 1)
			return true;

		if ($user->premium_until) {
			$timeEnds = mktime($user->premium_until);
			if ($timeEnds > time())
				return true;
		}

		return false;
	}

	/**
	 * Gets a user's clearance level (returns whichever is higher, user clearance, or user group
	 * clearance)
	 * 
	 * @param int $user user id
	 * @return int clearance level
	 */
	public function getClearanceLevel($user) {
		$user = $this->getUser($user);

		if ($user->group->group_clearance_level > $user->user_clearance_level)
			return $user->group->group_clearance_level;
		else
			return $user->user_clearance_level;
	}

	/**
	 * Gets all users in an array of user group ids
	 * 
	 * @param array $userGroups array of user group ids
	 * @return array array of UserBeans
	 */
	public function getUsersInUserGroups($userGroups) {
		$q = new Query("SELECT");
		
		foreach($userGroups as $userGroup) {
			$q->where("group_id = ?", $userGroup, "OR");
		}
		
		return UserBean::select($q, true);
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
