<?php
/**
 * Scans Controller
 * Scans database
 *
 * Copyright (c) 2011, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\controllers;

use darkcrusader\controllers\Controller;
use hydrogen\view\View;

use darkcrusader\models\SystemModel;
use darkcrusader\models\ScanModel;
use darkcrusader\classes\UserPermissionSet;

class ScansController extends Controller {
	
	public function index() {
		$this->checkAuth("access_scans");

		$latestScans = ScanModel::getInstance()->getLatestScans(10);

		View::load('scans/index', array(
			"latestScans" => $latestScans
		));
	}

	public function submit() {
		$this->checkAuth("access_scans");

		if ($_POST["submit"]) {
			$addedScan = ScanModel::getInstance()->addScanPaste($_POST["scanPaste"], $_POST["scanner"]);
			View::setVar("scan", $addedScan);
		}

		View::load('scans/submit');
	}
}
?>