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

// Request begins
define('START_REQUEST', microtime(true));

// Initialize
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
use darkcrusader\oe\exceptions\APIQueryFailedException;
use darkcrusader\sqlbeans\LoggedActionBean;

// Error Reporting
if (Config::getRequiredVal("general", "display_errors") == "On")
    error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
ini_set("display_errors", Config::getRequiredVal("general", "display_errors"));

// If accessed from jjutilities, change some config values so that we load jjutilities views instead of swatfire ones
if ($_SERVER["HTTP_HOST"] == "jjutilities.co.cc") {
    Config::setVal("general", "site_name", "JJ Utilities");
    Config::setVal("general", "app_url", "http://jjutilities.co.cc");
    Config::setVal("view", "folder", "themes/jjutilities");
    Config::setVal("view", "url_path", "themes/jjutilities");
    Config::setVal("general", "site_bank_character_name", "Jedi Utilities");
    Config::setVal("general", "site_bank_api_access_key", "7430009cc5");
}

// Check if db is not installed, if not, redirect user to install
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

$c = Controller::getInstance();

// Set some vars for the base view
$c->initializeViewVariables();

// Any preserved alerts from redirect? If so display them and clear cookie
if ($_COOKIE["alerts"]) {
    View::setVar("alerts", unserialize($_COOKIE["alerts"]));
    setcookie("alerts", "null", time()-60*60*24*30*12*20, "/"); // expire it 20 years ago :)
}

// Check if there's any characters awaiting verification, if so let the user know they need to do so
$um = UserModel::getInstance();
$activeUser = $um->getActiveUser();
if ($activeUser->username) {
    $characters = $um->getCharacterLinkRequests($activeUser->id);
    foreach($characters as $character) {
        $c->alert("info", "You still need to verify your character " . $character->character_name . ". To verify it, simply type /transfercredits " . Config::getRequiredVal("general", "site_bank_character_name") . "," . $character->verification_amount . " into OE chat while logged in as " . $character->character_name . ", then just click [Update] next to Site Bank in the sidebar. Alternatively, you can delete this link request from Character Management.");
    }
}

// Get any new site bank transactions and so processing stuff
$sbm = SiteBankModel::getInstance();
if ($_GET["dositebankupdate"]) {
    
    try {
        $sbm->updateDB();
    }
    catch (APIQueryFailedException $e) {
        $c->alert("warning", "Site bank balances update failed, please try again. If this problem persists contact an admin");
        $failed = true;
    }

    if (!$failed)
        $c->alert("success", "All site bank balances updated successfully");
}

$sbm->processAnyUnprocessedTransfers();

$lab = new LoggedActionBean;
$lab->user_id = $activeUser->id;
$lab->type = "page_load";
$lab->description = $activeUser->username . " accessed /index.php" . $_SERVER["PATH_INFO"];
foreach($_GET as $key => $value)
    $lab->description .= "?" . $key . '=' . $value;

$lab->set("date", "NOW()", true);
$lab->insert();

// Add the dispatcher rules
Dispatcher::addHomeMatchRule('\darkcrusader\controllers\HomeController', "index");
Dispatcher::addPathInfoAutoMapRule('\darkcrusader\controllers', "Controller");
Dispatcher::addMatchAllRule('\darkcrusader\controllers\ErrorController', "notFound");

// Annd GO!
Dispatcher::dispatch();

?>