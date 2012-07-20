<?php
/**
 * Stats Controller
 * Controls the stats index
 *
 * Copyright (c) 2012, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\controllers;

use darkcrusader\controllers\Controller;
use hydrogen\view\View;

use darkcrusader\models\FactionModel;
use darkcrusader\models\PlayerModel;

class StatsController extends Controller {
	
	public function index() {
		$this->checkAuth("access_site");

		$factions = FactionModel::getInstance()->getFactionsCached();

		if ($this->checkAuth("access_player_statistics", false))
			View::setVar("canAccessPlayerStatistics", "yes");

		if ($this->checkAuth("access_system_stats", false))
			View::setVar("canAccessSystemStatistics", "yes");

		if ($this->checkAuth("access_locality_stats", false))
			View::setVar("canAccessLocalityStatistics", "yes");

		if ($this->checkAuth("access_faction_stats", false))
			View::setVar("canAccessFactionStatistics", "yes");

		View::load('stats', array(
			"factions" => $factions,
			"numberOfFactionsWeKnowOf" => count($factions),
			"numberOfPlayersOnFile" => PlayerModel::getInstance()->getNumberOfPlayersOnFile()
		));
	}
}
?>