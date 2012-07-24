<?php
/**
 * Controller
 * Base for all other controllers
 *
 * Copyright (c) 2012, Jack Harley
 * All Rights Reserved
 */

namespace darkcrusader\controllers;

use darkcrusader\models\UserModel;

use hydrogen\config\Config;
use hydrogen\view\View;
use hydrogen\errorhandler\ErrorHandler;

class Controller extends \hydrogen\controller\Controller {

	/**
	 * Checks if the user has any permission
	 * in the supplied array.
	 * If the user does not have one or more of the permissions in the supplied
	 * array, false will be returned, and if $endIfNoPermission is set to true,
	 * then the permission denied page will be loaded and execution killed
	 *
	 * @param mixed $permissions array of permissions to check in addition to access_site or
	 * a single permission
	 * @param boolean $endIfNoPermission set to true to load permission denied page and
	 * stop execution if one of the array permissions is not granted
	 */
	public function checkAuth($permissions=array(), $endIfNoPermission=true) {
		$user = UserModel::getInstance()->getActiveUser();

		if (!is_array($permissions))
			$permissions = array($permissions);

		foreach($permissions as $permission) {
			if (!$user->permissions->hasPermission($permission)) {
				if ($endIfNoPermission)
					$this->permissionDenied();

				return false;
			}
		}

		return true;
	}

	/**
	 * Checks that a valid default character has been set up and has an API key associated for
	 * the currently active user
	 * 
	 * @param boolean $endIfNotSetUp set to true to load an error page with links on setting up a
	 * default character if a character has not been set up
	 */
	public function checkForValidCharacterAndAPIKey($endIfNotSetUp=true) {
		$um = UserModel::getInstance();
		$user = $um->getActiveUser();
		$char = $um->getDefaultCharacter($user->id);

		if (!$char->api_key) {
			if ($endIfNotSetUp)
				$this->noValidCharacter();
			return false;
		}

		return true;
	}

	/**
	 * Checks if the inputs given exist
	 * 
	 * @param array $inputs array of inputs to check
	 * @param string $method either "post" or "get", defaults to "post"
	 * @return boolean true if inputs all exist and are not empty, otherwise false
	 */
	public function checkFormInput($inputs=array(), $method="post") {
		
		if ((!is_array($inputs)) && (is_string($inputs)))
			$inputs = array($inputs);

		foreach($inputs as $input) {
			switch($method) {
				case "post":
					if ((!isset($_POST[$input])) || ($_POST[$input] == ""))
						return false;
				break;
				case "get":
					if ((!isset($_GET[$input])) || ($_GET[$input] == ""))
						return false;
				break;
			}
		}

		return true;
	}

	/**
	 * Sends redirect header to another page and ends execution if $endExecution is true
	 *
	 * @param string $path path in form '/forums/forum/1'
	 * @param boolean $endExecution set to true to die() after sending header
	 */
	public function redirect($path="", $endExecution=true) {
		
		// preserve alerts as a cookie
		try {
			$alerts = View::getVar("alerts");
		}
		catch (\hydrogen\view\exceptions\NoSuchVariableException $e) {
		}

		if ($alerts)
			setcookie("alerts", serialize($alerts), time()+60*60*24*30*12, "/");

		header('Location: ' . Config::getVal('general', 'app_url') . $path);
		
		if ($endExecution)
			die();
	}

	/**
	 * Loads a not found page and kills execution
	 */
	public function notFound() {
		ErrorHandler::sendHttpCodeHeader(ErrorHandler::HTTP_NOT_FOUND);
		View::load("404");
		die();
	}

	/**
	 * Loads a permission denied page and kills execution
	 */
	public function permissionDenied() {
		ErrorHandler::sendHttpCodeHeader(ErrorHandler::HTTP_UNAUTHORIZED);
		View::load("permission_denied");
		die();
	}

	/**
	 * Loads a no valid character page and kills execution
	 */
	public function noValidCharacter() {
		View::load("no_valid_character");
		die();
	}

	/**
	 * Sets an alert for the page to be loaded
	 *
	 * @param string $type alert type: success, info, warning, error
	 * @param string $message alert message
	 */
	public function alert($type, $message) {
		try {
			$alerts = View::getVar("alerts");
		}
		catch (\hydrogen\view\exceptions\NoSuchVariableException $e) {
			$alerts = array();
		}

		$alerts[] = array(
			"type" => $type,
			"message" => $message
		);

		View::setVar("alerts", $alerts);
	}

	/**
	 * Sets up some basic view vars about the active user and other things
	 * Usually only manually run in a controller if there's been a change to the active user and
	 * the view needs to have the latest info
	 */
	public function initializeViewVariables() {
		$um = UserModel::getInstance();
		$activeUser = $um->getActiveUser(false);
		if ($activeUser->username)
			View::setVar("activeUser", $activeUser);
		View::setVar("siteName", Config::getVal("general", "site_name"));
		View::setVar("siteBankCharacterName", Config::getRequiredVal("general", "site_bank_character_name"));
		if (Config::getVal("general", "google_analytics_code"))
			View::setVar("googleAnalyticsCode", Config::getVal("general", "google_analytics_code"));
		if ($activeUser->permissions->hasPermission("access_admin_panel"))
			View::setVar("userIsAdmin", "yes");
		if ($activeUser->permissions->hasPermission("access_faction_bank"))
			View::setVar("userCanAccessFactionBank", "yes");
		if ($um->checkIfUserIsPremium($activeUser->id))
			View::setVar("userIsPremium", "yes");
		if ($activeUser->permissions->hasPermission("access_scans"))
			View::setVar("userCanAccessScans", "yes");
	}
}

?>