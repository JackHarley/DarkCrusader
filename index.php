<?php
/**
 * Dark Crusader
 *
 * Index.php, loads all the required libraries and dispatches
 * the request
 *
 * Copyright (c) 2012, Jack Harley
 * All Rights Reserved
 */

// Initialize
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
ini_set("display_errors", "On");
session_start();
date_default_timezone_set("Europe/Dublin");

// Include libraries
require_once(__DIR__ . '/lib/hydrogen/hydrogen.inc.php');
require_once(__DIR__ . '/lib/darkcrusader/darkcrusader.inc.php');
require_once(__DIR__ . '/lib/pChart/class/pData.class.php');
require_once(__DIR__ . '/lib/pChart/class/pPie.class.php');
require_once(__DIR__ . '/lib/pChart/class/pDraw.class.php');
require_once(__DIR__ . '/lib/pChart/class/pImage.class.php');
require_once(__DIR__ . '/lib/pChart/class/pCache.class.php');

// Load classes
use hydrogen\errorhandler\ErrorHandler;
use hydrogen\controller\Dispatcher;
use hydrogen\view\View;
use hydrogen\config\Config;
use darkcrusader\models\UserModel;
use darkcrusader\models\InstallModel;

// check if db is not installed, if not, redirect user to install
$im = InstallModel::getInstance();
if ($im->checkIfDatabaseIsInstalled() !== true) {
    if (strpos($path, "install") === false) {
        $ic = darkcrusader\controllers\InstallController::getInstance();
		$ic->install();
        die();
    }
}
if ($im->checkIfDatabaseIsUpToDate() !== true) {
    if (strpos($path, "install") === false) {
        $ic = darkcrusader\controllers\InstallController::getInstance();
		$ic->upgrade();
		die();
    }
}

$activeUser = UserModel::getInstance()->getActiveUser();
View::setVar("activeUser", $activeUser);
View::setVar("siteName", Config::getVal("general", "site_name"));
if ($activeUser->permissions->hasPermission("access_admin_panel"))
    View::setVar("userIsAdmin", "yes");
if ($activeUser->permissions->hasPermission("access_faction_bank"))
    View::setVar("userCanAccessFactionBank", "yes");

// Add the dispatcher rules
Dispatcher::addHomeMatchRule('\darkcrusader\controllers\HomeController', "index");
Dispatcher::addPathInfoAutoMapRule('\darkcrusader\controllers', "Controller");
Dispatcher::addMatchAllRule('\darkcrusader\controllers\ErrorController', "notFound");

// Annd GO!
Dispatcher::dispatch();

?>