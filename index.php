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
use darkcrusader\models\SiteBankModel;
use darkcrusader\controllers\InstallController;
use darkcrusader\controllers\Controller;

// check if db is not installed, if not, redirect user to install
$im = InstallModel::getInstance();
$ic = InstallController::getInstance();
if ($im->checkIfDatabaseIsInstalled() !== true) {
    if (strpos($_SERVER["PATH_INFO"], "install") === false) {
		$ic->install();
        die();
    }
}
if ($im->checkIfDatabaseIsUpToDate() !== true) {
    if (strpos($_SERVER["PATH_INFO"], "install") === false) {
		$ic->upgrade();
		die();
    }
}

// Set some vars for the base view
$um = UserModel::getInstance();
$activeUser = $um->getActiveUser();
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

// Get any new site bank transactions and so processing stuff
$sbm = SiteBankModel::getInstance();
$sbm->updateDB();
$sbm->processAnyUnprocessedTransfers();

// Any preserved alerts from redirect? If so display them and clear cookie
if ($_COOKIE["alerts"]) {
    View::setVar("alerts", unserialize($_COOKIE["alerts"]));
    setcookie("alerts", "null", time()-60*60*24*30*12*20); // expire it 20 years ago :)
}

// Check if there's any characters awaiting verification, if so let the user know they need to do so
$characters = $um->getCharacterLinkRequests($activeUser->id);
$c = Controller::getInstance();
foreach($characters as $character) {
    $c->alert("info", "You still need to verify your character " . $character->character_name . ". To verify it, simply type /transfercredits " . Config::getRequiredVal("general", "site_bank_character_name") . "," . $character->verification_amount . " into OE chat while logged in as " . $character->character_name . ". Alternatively, you can delete this link request from Character Management.");
}

// Add the dispatcher rules
Dispatcher::addHomeMatchRule('\darkcrusader\controllers\HomeController', "index");
Dispatcher::addPathInfoAutoMapRule('\darkcrusader\controllers', "Controller");
Dispatcher::addMatchAllRule('\darkcrusader\controllers\ErrorController', "notFound");

// Annd GO!
Dispatcher::dispatch();

?>