<?php
/**
 * Maps Controller
 * Controls the maps
 *
 * Copyright (c) 2012, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\controllers;

use darkcrusader\controllers\Controller;
use hydrogen\view\View;
use darkcrusader\models\SystemModel;

class MapsController extends Controller {
	
	public function index() {
		View::load('maps/index');
	}

	public function colonised($scale=7, $special=false) {
		$sm = SystemModel::getInstance();

		if ($special) {
			$system = $sm->getSystem(false, $special);
			View::setVar("specialSystem", $sm->getSystemStats($system->id));
		}

		View::load('maps/map', array(
			"systems" => $sm->getColonisedSystemsLatestStats(),
			"scale" => $scale,
			"width" => 10000,
			"height" => 10000,
			"display_government_system_names" => false,
			"top_padding" => 600,
			"left_elimination" => 0
		));
	}

	public function government($scale=2, $special=false) {
		$sm = SystemModel::getInstance();

		if ($special) {
			$system = $sm->getSystem(false, $special);
			View::setVar("specialSystem", $sm->getSystemStats($system->id));
		}

		View::load('maps/map', array(
			"systems" => $sm->getGovernmentSystemsLatestStats(),
			"scale" => $scale,
			"width" => 4000,
			"height" => 3000,
			"display_government_system_names" => true,
			"top_padding" => 100,
			"left_elimination" => 4500
		));
	}

	public function stations($scale=8, $special=false) {
		$sm = SystemModel::getInstance();

		if ($special) {
			$system = $sm->getSystem(false, $special);
			View::setVar("specialSystem", $sm->getSystemStats($system->id));
		}

		View::load('maps/map', array(
			"systems" => $sm->getStationSystemsLatestStats(),
			"scale" => $scale,
			"width" => 10000,
			"height" => 10000,
			"display_government_system_names" => true,
			"top_padding" => 600,
			"left_elimination" => 0
		));
	}

	public function good10resources($scale=5, $special=false) {
		$this->checkAuth("access_scans");

		$sm = SystemModel::getInstance();

		if ($special) {
			$system = $sm->getSystem(false, $special);
			View::setVar("specialSystem", $sm->getSystemStats($system->id));
		}

		View::load('maps/map', array(
			"systems" => array_merge($sm->getSystemsWithGood10ResourcesLatestStats(), $sm->getStationSystemsLatestStats()),
			"scale" => $scale,
			"width" => 10000,
			"height" => 10000,
			"display_government_system_names" => false,
			"top_padding" => 600,
			"left_elimination" => 0
		));
	}
}
?>