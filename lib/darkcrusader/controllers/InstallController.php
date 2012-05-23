<?php
/**
 * Project Dark Crusader
 * Copyright (c) 2012, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\controllers;

use hydrogen\view\View;

use darkcrusader\controllers\Controller;
use darkcrusader\models\InstallModel;

define("ALLOW_INSTALL", false);

/**
 * Install Controller
 * Handles installation
 */
class InstallController extends Controller {

	/**
	 * Redirect to installer
	 */
	public function index() {
		$this->redirect("/index.php/install/install");
	}

	/**
	 * Database installer
	 */
	public function install() {
		if (ALLOW_INSTALL) {
	        if (!isset($_POST["submit"]))
				View::load('install/install');
			else {
				InstallModel::getInstance()->installDatabase($_POST["username"], $_POST["password"], true);
				View::load('install/database_complete');
			}
		}
	}

	/**
	 * Database upgrader
	 */
	public function upgrade() {
		if (ALLOW_INSTALL) {
			if (!isset($_POST["submit"]))
				View::load('install/upgrade');
			else {
				InstallModel::getInstance()->installDatabase($_POST["username"], $_POST["password"], false);
				View::load('install/database_complete');
			}
		}
	}
}
?>