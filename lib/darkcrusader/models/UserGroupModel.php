<?php
/**
 * Project Dark Crusader
 * Copyright (c) 2012, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\models;

use hydrogen\model\Model;
use hydrogen\database\Query;

use darkcrusader\sqlbeans\UserGroupBean;
use darkcrusader\sqlbeans\GroupPermissionBean;

use darkcrusader\models\UserModel;
use darkcrusader\models\PermissionsModel;

use darkcrusader\permissions\PermissionSet;

class UserGroupModel extends Model {

	protected static $modelID = "usergroup";

	/**
	 * addUserGroup
	 * Adds a new user group
	 *
	 * @param string $name unformatted group name e.g. root_admin
	 * @param string $description formatted group name e.g. Root Admin
	 * @param boolean $premium "yes" for unlimited premium, otherwise "no"
	 * @param int $clearanceLevel intelligence clearance level
	 * @param array $postForPerms $_POST array to iterate over for perms
	 * @param array $pbs array of permission beans to grant
	 * @return mixed boolean true if successful, error string
	 *				 if failure
	 */
	public function addUserGroup($name, $description, $premium, $clearanceLevel, $postForPerms=false, $pbs=false) {
		$ugb = new UserGroupBean;
		$ugb->group_name = $name;
		$ugb->description = $description;
		$ugb->premium = ($premium == "yes") ? 1 : 0;
		$ugb->group_clearance_level = $clearanceLevel;

		$ugb->insert();

		$q = new Query("SELECT");
		$q->where("group_name = ?", $name);

		$ugbs = UserGroupBean::select($q);
		$ugb = $ugbs[0];

		if ($postForPerms) {
			$pm = PermissionsModel::getInstance();

			$allPerms = $pm->getAllPermissions();

			foreach($allPerms as $perm) {
				if ($postForPerms[$perm->id]) {
					if ($postForPerms[$perm->id] == "no")
						$this->modifyUserGroupPermission($ugb->id, $perm->id, 0);
					else if ($postForPerms[$perm->id] == "yes")
						$this->modifyUserGroupPermission($ugb->id, $perm->id, 1);
				}
			}
		}

		if ($pbs) {
			foreach ($pbs as $pb) {
				$this->modifyUserGroupPermission($ugb->id, $pb->id, 1);
			}
		}

		return true;
	}

	/**
	 * getGroupList
	 * Gets a list of groups in alphabetical order and
	 * returns them as an array
	 *
	 * @return array array of UserGroupBean objects
	 */
	public function getUserGroupList() {

		// Create the query
		$q = new Query("SELECT");

		// Select them using UserGroupBean
		$ugbs = UserGroupBean::select($q);

		// Return the user array
		return $ugbs;
	}

	/**
	 * getUserGroup
	 * Gets a group and returns it as a
	 * \darkcrusader\sqlbeans\UserGroupBean object
	 * Pass only 1 of the 2 parameters, ensure you
	 * pass false/null as the first parameter if required
	 *
	 * @param int $id group id
	 * @param string $name group name
	 * @return mixed \darkcrusader\sqlbeans\UserGroupBean object
	 *		     or boolean false if no such group
	 */
	public function getUserGroup($id=false, $name=false) {

		// Make the query for the group
		$q = new Query("SELECT");

		if ($id)
			$q->where("id = ?", $id);
		if ($name)
			$q->where("group_name = ?", $name);

		$q->limit(1);
		$ugbs = UserGroupBean::select($q);
		$ugb = $ugbs[0];

		// No group? Exit
		if (!$ugb)
			return false;

		// Return the group object
		return $ugb;
	}

	/**
	 * Get user group permissions
	 *
	 * @param int $id user group id
	 * @param string $get either 'granted' or 'nongranted'
	 * @return darkcrusader\permissions\PermissionSet permission set
	 */
	public function getUserGroupPermissions($id, $get="granted") {

		$pm = PermissionsModel::getInstance();

		$ugb = $this->getUserGroup($id);
		$gpbs = $ugb->getPermissions();

		if ($get == "granted")
			$ps = $pm->constructPermissionSet($gpbs);
		else if ($get == "nongranted")
			$ps = $pm->constructNonGrantedPermissionSet($gpbs);

		return $ps;
	}

	/**
	 * Updates the user group with the given info, and passed it, will
	 * iterate over a $_POST array and update perms.
	 *
	 * @param int $id user group ID
	 * @param string $name unformatted group name e.g. root_admin
	 * @param string $description formatted group name e.g. Root Admin
	 * @param int $clearanceLevel intelligence clearance level
	 * @param string $colour hex group colour
	 * @param array $postForPerms $_POST array to iterate over for perms
	 */
	public function updateUserGroup($id, $groupName=false, $description=false, $premium=false, $clearanceLevel=false, $postForPerms=false) {
		$q = new Query("SELECT");
		$q->where("id = ?", $id);

		$ugbs = UserGroupBean::select($q);
		$ugb = $ugbs[0];

		if ($groupName)
			$ugb->group_name = $groupName;

		if ($description)
			$ugb->description = $description;

		if ($premium)
			$ugb->premium = ($premium == "yes") ? 1 : 0;

		if ($clearanceLevel !== false)
			$ugb->group_clearance_level = $clearanceLevel;

		if ($postForPerms) {
			$pm = PermissionsModel::getInstance();

			$allPerms = $pm->getAllPermissions();

			foreach($allPerms as $perm) {
				if ($postForPerms[$perm->id]) {
					if ($postForPerms[$perm->id] == "no")
						$this->modifyUserGroupPermission($ugb->id, $perm->id, 0);
					else if ($postForPerms[$perm->id] == "yes")
						$this->modifyUserGroupPermission($ugb->id, $perm->id, 1);
				}
			}
		}

		$ugb->update();

		return true;
	}

	/**
	 * deleteUserGroup
	 * Deletes the group which has the id
	 * specified
	 *
	 * @param int $id group id to delete
	 */
	public function deleteUserGroup($id) {

		$q = new Query("DELETE");
		$q->from("user_groups");
		$q->where("id = ?", $id);
		$stmt = $q->prepare();
		$stmt->execute();

		$q = new Query("DELETE");
		$q->from("group_permissions");
		$q->where("group_id = ?", $id);
		$stmt = $q->prepare();
		$stmt->execute();
	}

	/**
	 * getNumberOfUserGroups
	 * Gets the number of user groups registered
	 *
	 * @return int number of user groups
	 */
	public function getNumberOfUserGroups() {

		$q = new Query("SELECT");
		$q->from("user_groups");
		$q->field("id");
		$stmt = $q->prepare();
		$stmt->execute();

		$i = 0;
		while($stmt->fetchObject())
			$i++;

		return $i;
	}

	/**
	 * getUserGroupStats
	 * Gets an array of user groups and the number of users
	 * each has
	 *
	 * @return array array of associative arrays
	 */
	public function getUserGroupStats() {

		$ugbs = UserGroupBean::select();
		$um = UserModel::getInstance();

		$r = array();
		foreach($ugbs as $ugb) {
			$userCount = $um->getNumberOfUsers($ugb->id);
			$r[] = array(
				"userCount" => $userCount,
				"description" => $ugb->description);
		}

		return $r;
	}

	/**
	 * Modify a user group permission
	 *
	 * @param int user group id
	 * @param int permission id to grant
	 * @param int value to set, granted=1, denied=0, inherit=-1
	 */
	public function modifyUserGroupPermission($group, $permission, $value) {

		$q = new Query("SELECT");
		$q->where("group_id = ?", $group);
		$q->where("permission_id = ?", $permission);
		$gpbs = GroupPermissionBean::select($q);
		$gpb = $gpbs[0];

		if ($gpb) {

			// to inherit, there simply needs to be no entry for the perm,
			// so delete
			if ($value == -1) {
				$gpb->delete();
				return;
			}

			// else, either set record with 0/1
			$gpb->value = $value;
			$gpb->update();
		}
		else {
			if ($value == -1)
				return;

			$gpb = new GroupPermissionBean;
			$gpb->group_id = $group;
			$gpb->permission_id = $permission;
			$gpb->value = $value;
			$gpb->insert();
		}
	}

}