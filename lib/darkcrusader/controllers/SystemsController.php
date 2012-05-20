<?php
/**
 * Home Controller
 * Controls the index of Dark Crusader
 *
 * Copyright (c) 2012, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\controllers;

use darkcrusader\controllers\Controller;
use hydrogen\view\View;

use darkcrusader\models\SystemModel;
use darkcrusader\models\ScanModel;

class SystemsController extends Controller {
	
	public function index() {
		if (!isset($_GET["name"])) {
			View::load('systems/index');
			return;
		}

		$sm = SystemModel::getInstance();
		$system = $sm->getSystem(false, $_GET["name"]); 
		$historicalStats = $sm->getHistoricalSystemStats($system->id, 15);

		if ($this->checkAuth("access_scans", false)) {
			$scans = ScanModel::getInstance()->getScansForSystem($system->id);
			if (count($scans) > 0)
				View::setVar("scans", $scans);
			else
				View::setVar("scans", "none");
		}

		View::load('systems/system', array(
			"system" => $system,
			"historicalStats" => $historicalStats
		));
	}
}
?>