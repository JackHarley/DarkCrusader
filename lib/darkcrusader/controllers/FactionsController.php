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

use darkcrusader\models\FactionModel;
use darkcrusader\factions\exceptions\NoSuchFactionException;

class FactionsController extends Controller {
	
	public function index() {
		if (!isset($_GET["name"])) {
			View::load('factions/index', array(
				"factions" => FactionModel::getInstance()->getFactionsCached()
			));
			return;
		}

		$_GET["name"] = urldecode($_GET["name"]);

		$fm = FactionModel::getInstance();
		$faction = new \stdClass();
		
		try {
			$faction->name = $fm->searchFactionName($_GET["name"]);
			$faction->number_of_owned_systems = $fm->getNumberOfOwnedSystems($_GET["name"]);
			$faction->number_of_owned_station_systems = $fm->getNumberOfOwnedStationSystems($_GET["name"]);

			$charts = $fm->createFactionCharts($_GET["name"]);
		}
		catch (NoSuchFactionException $e) {
			$this->alert("error", "No faction matching that name was found. For a faction to be in our database they must control at least one system. Also, please ensure you are searching for the faction by name, NOT tag");
			View::load("factions/index");
			return;
		}

		$faction->systems_chart_url = $charts["systems"];
		$faction->station_systems_chart_url = $charts["station_systems"];

		View::load('factions/faction', array(
			"faction" => $faction
		));
	}
}
?>