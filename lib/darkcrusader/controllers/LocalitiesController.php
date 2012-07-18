<?php
/**
 * Locality Controller
 * Locality info
 *
 * Copyright (c) 2012, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\controllers;

use darkcrusader\controllers\Controller;
use hydrogen\view\View;

use darkcrusader\models\SystemModel; 
use darkcrusader\models\UserModel;
use darkcrusader\models\ScanModel;

class LocalitiesController extends Controller {

	public function index() {
		$this->redirect("/index.php/stats");
	}

	public function locality() {
		$this->checkAuth("access_locality_stats");

		if (!$this->checkFormInput(array("q", "s", "r", "l"), "get")) {
			$this->redirect("/index.php/stats");
		}
		
		if ($this->checkAuth("access_scans", false))
			View::setVar("hasAccessToScans", true);
		
		$user = UserModel::getInstance()->getActiveUser();

		$sm = SystemModel::getInstance();
		$systems = $sm->getSystemsInLocalityWithScanStats($_GET["q"], $_GET["s"], $_GET["r"], $_GET["l"], $user->id);
		$numberOfSystems = $sm->getNumberOfSystemsInLocalityCached($_GET["q"], $_GET["s"], $_GET["r"], $_GET["l"]);
		$numberOfSystemsWithScan = $sm->getNumberOfSystemsInLocalityWithAtLeastOneScanCached($_GET["q"], $_GET["s"], $_GET["r"], $_GET["l"]);
		$numberOfSystemsWithScanByUser = $sm->getNumberOfSystemsInLocalityWithAtLeastOneScanCached($_GET["q"], $_GET["s"], $_GET["r"], $_GET["l"], $user->id);

		View::load('localities/locality', array(
			"systems" => $systems,
			"location" => $_GET["q"] . ":" . $_GET["s"] . ":" . $_GET["r"] . ":" . $_GET["l"],
			"number_of_systems" => $numberOfSystems,
			"number_of_systems_with_scan" => $numberOfSystemsWithScan,
			"number_of_systems_with_scan_by_user" => $numberOfSystemsWithScanByUser
		));
	}

	public function scanplan() {
		$this->checkAuth(array(
			"access_locality_stats",
			"access_scans"
		));

		if (!$this->checkFormInput(array("q", "s", "r", "l", "fuel_capacity", "fuel_consumption_per_lightyear"), "post")) {
			View::load('localities/scan_plan_form');
			return;
		}

		$displaySystemsScannedByUser = ($_POST["do_not_display_systems_scanned_by_user"]) ? false : true;
		$displayScannedSystems = ($_POST["do_not_display_scanned_systems"]) ? false : true;
		$instructions = ScanModel::getInstance()->createScanningRouteForLocality($_POST["q"], $_POST["s"], $_POST["r"], $_POST["l"], $_POST["start_location"], $_POST["fuel_capacity"], $_POST["fuel_consumption_per_lightyear"], $displaySystemsScannedByUser, $displayScannedSystems);

		View::load('localities/scan_plan', array(
			"instructions" => $instructions
		));

	}
}
?>