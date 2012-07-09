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

class UtilitiesController extends Controller {
	
	public function index() {
		$this->checkAuth("access_site");

		View::load('utilities/index');
	}

	public function canimakeit() {
		$this->checkAuth(array(
			"access_site"
		));

		if ($_POST["submit"]) {
			$canIMakeIt = SystemModel::getInstance()->canPlayerMakeItThereAndToStationWithFuel($_POST["current_system"], $_POST["destination_system"], $_POST["fuel"], $_POST["fuel_consumption_per_ly"]);

			if ($canIMakeIt)
				$this->alert("success", "You have enough fuel to jump to " . $_POST["destination_system"] . " and then on to " . $canIMakeIt . " (which has a station)");
			else
				$this->alert("warning", "If you jump to " . $_POST["destination_system"] . " you will not be able to get back to a station!");
		}

		View::load('utilities/can_i_make_it');
	}
}
?>