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

class HomeController extends Controller {
	
	public function index() {
		$this->checkAuth("access_site");

		View::load('index');
	}
}
?>