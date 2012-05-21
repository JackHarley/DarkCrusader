<?php
/**
 * Players Controller
 * Controls the player stats
 *
 * Copyright (c) 2012, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\controllers;

use darkcrusader\controllers\Controller;
use hydrogen\view\View;

class PlayersController extends Controller {
	
	public function index() {
		$this->checkAuth("access_site");

		View::load('players/index');
	}
}
?>