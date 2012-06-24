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

	public function colonised($scale=5) {
		View::load('maps/map', array(
			"systems" => SystemModel::getInstance()->getColonisedSystemsLatestStats(),
			"scale" => $scale,
			"width" => 10000,
			"height" => 10000,
			"display_government_system_names" => false,
			"top_padding" => 600,
			"left_elimination" => 0
		));
	}

	public function government($scale=2) {
		View::load('maps/map', array(
			"systems" => SystemModel::getInstance()->getGovernmentSystemsLatestStats(),
			"scale" => $scale,
			"width" => 4000,
			"height" => 3000,
			"display_government_system_names" => true,
			"top_padding" => 100,
			"left_elimination" => 4500
		));
	}
}
?>