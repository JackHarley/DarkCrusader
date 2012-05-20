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

class LocalityController extends Controller {

	public function index() {
		$this->checkAuth("access_locality_stats");

		if (!$this->checkFormInput(array("q", "s", "r", "l"), "get")) {
			View::load('locality/index');
			return;
		}
		
		if ($this->checkAuth("access_scans", false))
			View::setVar("hasAccessToScans", true);
		
		$user = UserModel::getInstance()->getActiveUser();

		$sm = SystemModel::getInstance();
		$systems = $sm->getSystemsInLocalityWithScanStats($_GET["q"], $_GET["s"], $_GET["r"], $_GET["l"], $user->id);
		$numberOfSystems = $sm->getNumberOfSystemsInLocalityCached($_GET["q"], $_GET["s"], $_GET["r"], $_GET["l"]);
		$numberOfSystemsWithScan = $sm->getNumberOfSystemsInLocalityWithAtLeastOneScanCached($_GET["q"], $_GET["s"], $_GET["r"], $_GET["l"]);
		$numberOfSystemsWithScanByUser = $sm->getNumberOfSystemsInLocalityWithAtLeastOneScanCached($_GET["q"], $_GET["s"], $_GET["r"], $_GET["l"], $user->id);

		View::load('locality/locality', array(
			"systems" => $systems,
			"location" => $_GET["q"] . ":" . $_GET["s"] . ":" . $_GET["r"] . ":" . $_GET["l"],
			"number_of_systems" => $numberOfSystems,
			"number_of_systems_with_scan" => $numberOfSystemsWithScan,
			"number_of_systems_with_scan_by_user" => $numberOfSystemsWithScanByUser
		));
	}
}
?>