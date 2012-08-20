<?php
/**
 * Project Dark Crusader
 * Copyright (c) 2012, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\controllers;

use hydrogen\view\View;
use hydrogen\config\Config;
use hydrogen\recache\RECacheManager;

use darkcrusader\controllers\Controller;
use darkcrusader\models\UserModel;
use darkcrusader\models\UserGroupModel;
use darkcrusader\models\PermissionsModel;

class AdminController extends Controller {

	/**
	 * Admin index
	 */
	public function index() {
		$this->checkAuth();

	 	View::load("admin/index");
	}


	/**
	 * User Admin
	 *
	 * @param string $subpanel subpanel
	 * @param string $act action to perform
	 */
	public function user($subpanel=false, $act=false) {

		$this->checkAuth();

		// models
		$um = UserModel::getInstance();
		$ugm = UserGroupModel::getInstance();
		$pm = PermissionsModel::getInstance();

		// Check if a subpanel has been selected
		if ($subpanel) {

			// Work out what subpanel to show
			switch ($subpanel) {

				case "user":

					// Work out what act to complete (if there is one)
					switch ($act) {

						case "lookup":

							// loading a view here, so return
							View::load('admin/user/user/lookup', array(
								"user" => $um->getUser($_GET["id"]))
							);
							return;

						break;

						case "del":

							$um->deleteUser($_GET["id"]);
							$this->alert("success", "User deleted successfully");

						break;

						case "ban":

							$um->banUser($_GET["id"]);
							$this->alert("success", "User deleted, and IP banned successfully");

						break;

						case "add":
							if (!isset($_POST["submit"])) {

								// loading a view here, so return
								View::load('admin/user/user/add', array(
									"groups" => $ugm->getUserGroupList())
								);
								return;
							}
							else {
								$um->addUser($_POST["username"], $_POST["password"], $_POST["clearance_level"], $_POST["group"]);
								$this->alert("success", "User added successfully");
							}

						break;

						case "edit":

							if (!isset($_POST["submit"])) {

								// loading a view here, so return
								View::load('admin/user/user/edit', array(
									"user" => $um->getUser($_GET["id"]),
									"groups" => $ugm->getUserGroupList())
								);
								return;
							}
							else {
								$um->updateUser($_GET["id"], $_POST["username"], false, $_POST["clearance_level"], $_POST["group"]);
								$this->alert("success", "User updated successfully");
							}
						break;

						case "impersonate":

							$user = $um->getUser($_GET["id"]);

							if ($user->group->group_name == "root_admin") {
								$this->alert("error", "You do not have permission to impersonate a Root Admim");
							}
							else {
								$um->forceLogin($_GET["id"]);
								$this->redirect("/index.php");
							}

						break;

					} // end of switch

					// list users (any return messages will have been set)
					// and return
					View::load('admin/user/user/list', array(
						"userArray" => $um->getUserList())
					);
					return;

				break;

				case "group":

					// Work out what act to complete (if there is one)
					switch ($act) {

						case "lookup":

							$ps = $ugm->getUserGroupPermissions($_GET["id"], "granted");

							// loading a view here, so return
							View::load('admin/user/group/lookup', array(
								"group" => $ugm->getUserGroup($_GET["id"]),
								"perms" => $ps->permissionBeans)
							);
							return;

						break;

						case "edit":

							if (!$_POST["submit"]) {

								$grantedPs = $ugm->getUserGroupPermissions($_GET["id"], "granted");
								$nonGrantedPs = $ugm->getUserGroupPermissions($_GET["id"], "nongranted");

								// loading a view here, so return
								View::load('admin/user/group/edit', array(
									"group" => $ugm->getUserGroup($_GET["id"]),
									"yesPerms" => $grantedPs->permissionBeans,
									"noPerms" => $nonGrantedPs->permissionBeans)
								);
								return;
							}
							else {

								$ugm->updateUserGroup($_GET["id"], $_POST["group_name"], $_POST["description"], $_POST["premium"], $_POST["clearance_level"], $_POST);

								$this->alert("success", "Group updated successfully");
							}
						break;

						case "del":

							$result = $ugm->deleteUserGroup($_GET["id"]);
							$this->alert("success", "Group deleted successfully");

						break;

						case "add":

							if (!$_POST["submit"]) {

								// loading a view here, so return
								View::load('admin/user/group/add', array(
									"perms" => $pm->getAllPermissions())
								);
								return;
							}
							else {

								$ugm->addUserGroup($_POST["group_name"], $_POST["description"], $_POST["premium"], $_POST["clearance_level"], $_POST);
								$this->alert("success", "Group added successfully");
							}
						break;
					} // end of act switch

					// load the group list and return
					View::load('admin/user/group/list', array(
						"groupArray" => $ugm->getUserGroupList())
					);
					return;

				break;

				/* TO(RE)DO: bans */

			} // end of subpanel switch
		} // end of subpanels
	} // end of user


	/**
	 * Maintenance area
	 *
	 * @param string $subpanel subpanel
	 * @param string $act action to perform
	 */
	public function maintenance($subpanel=false, $act=false) {

		$this->checkAuth();

		switch ($subpanel) {
			case "cache":
				switch ($act) {
					case "clearall":
						RECacheManager::getInstance()->clearAll();
						$this->alert("success", "All caches flushed successfully");
					break;
				}

				View::load('admin/maintenance/cache');
			break;
		}
	}

	/**
	 * Checks whether a user has ACP access, and if not, loads a permission denied
	 * page
	 * Then checks whether the user has admin authenticated, and if not, loads the admin
	 * authentication form
	 */
	public function checkAuth() {
		parent::checkAuth("access_admin_panel", true);
	}

	/**
	 * Sets an alert for the page to be loaded
	 * Note that this WILL overwrite any existing alert
	 *
	 * @param string $type alert type: success, info, warning, error
	 * @param string $message alert message
	 */
	public function alert($type, $message) {
		View::setVar("notification", array(
			"type" => $type,
			"message" => $message)
		);
	}

}
?>