<?php
/**
 * Controller
 * Base for all other controllers
 *
 * Copyright (c) 2011, Jack Harley
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
	 * Sends redirect header to another page and ends execution if $endExecution is true
	 *
	 * @param string $path path in form '/forums/forum/1'
	 * @param boolean $endExecution set to true to die() after sending header
	 */
	public function redirect($path="", $endExecution=true) {
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
}

?>