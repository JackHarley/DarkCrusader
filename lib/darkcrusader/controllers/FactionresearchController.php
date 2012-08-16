<?php
/**
 * Faction Research Controller
 * Controls the faction research centre
 *
 * Copyright (c) 2012, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\controllers;

use darkcrusader\controllers\Controller;
use hydrogen\view\View;

use darkcrusader\models\FactionResearchModel;

class FactionresearchController extends Controller {
	
	public function index() {
		$this->checkAuth("access_faction_research");

		$fm = FactionResearchModel::getInstance();

		View::load('faction_research/index', array(
			"latestBlueprints" => $fm->getLatestBlueprints(5),
			"researchers" => $fm->getResearcherNames()
		));
	}

	public function researcher() {
		$this->checkAuth("access_faction_research");

		View::load('faction_research/researcher', array(
			"blueprints" => FactionResearchModel::getInstance()->getBlueprintsResearchedBy($_GET["name"]),
			"researcherName" => $_GET["name"]
		));
	}
}
?>