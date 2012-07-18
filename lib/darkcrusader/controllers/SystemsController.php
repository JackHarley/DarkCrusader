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

use darkcrusader\systems\exceptions\NoSuchSystemException;

class SystemsController extends Controller {
	
	public function index() {
		$this->redirect("/index.php/systems/stats");
	}

	public function system() {
		$this->checkAuth("access_system_stats");

		$sm = SystemModel::getInstance();

		try {
			$system = $sm->getSystem(false, $_GET["name"]);
		}
		catch (NoSuchSystemException $e) {
			$this->alert("error", "No system by that name was found, please check your spelling and try again");
			$this->redirect("/index.php/stats");
		}

		$closestStationSystem = $sm->getNearestStationSystemToSystem(false, $system->x, $system->y);

		$historicalStats = $sm->getHistoricalSystemStatsCached($system->id, 15);

		if ($this->checkAuth("access_scans", false)) {
			$scans = ScanModel::getInstance()->getScansForSystem($system->id);
			if (count($scans) > 0)
				View::setVar("scans", $scans);
			else
				View::setVar("scans", "none");
		}

		View::load('systems/system', array(
			"system" => $system,
			"historicalStats" => $historicalStats,
			"closestStationSystem" => $closestStationSystem
		));
	}

	public function stats() {
		$sm = SystemModel::getInstance();
		$sm->generateControlledSystemsByFactionGraphCached();
		$sm->generateControlledSystemsGraphCached();

		View::load('systems/stats');
		return;
	}
}
?>