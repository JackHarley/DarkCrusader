<?php
/**
 * Home Controller
 * Controls the index of Dark Crusader
 *
 * Copyright (c) 2011, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\controllers;

use darkcrusader\controllers\Controller;
use hydrogen\view\View;

use darkcrusader\models\SystemModel;

class SystemsController extends Controller {
	
	public function index() {
		if (!isset($_GET["name"])) {
			View::load('systems/index');
			return;
		}

		$sm = SystemModel::getInstance();
		$system = $sm->getSystem(false, $_GET["name"]);
		$historicalStats = $sm->getHistoricalSystemStats($system->id, 30);

		View::load('systems/system', array(
			"system" => $system,
			"historicalStats" => $historicalStats
		));
	}
}
?>