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

class StatsController extends Controller {
	
	public function index() {
		$this->checkAuth("access_site");

		View::load('stats');
	}
}
?>