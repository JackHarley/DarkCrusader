<?php
/**
 * Info Controller
 * Controls the info page
 *
 * Copyright (c) 2011, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\controllers;

use darkcrusader\controllers\Controller;
use hydrogen\view\View;

class InfoController extends Controller {
	
	public function index() {
		$this->checkAuth("access_site");

		View::load('info');
	}
}
?>