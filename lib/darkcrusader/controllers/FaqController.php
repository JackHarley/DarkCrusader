<?php
/**
 * Home Controller
 * Controls the FAQ
 *
 * Copyright (c) 2012, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\controllers;

use darkcrusader\controllers\Controller;
use hydrogen\view\View;

class FaqController extends Controller {
	
	public function index() {
		$this->checkAuth("access_site");

		View::load('faq');
	}
}
?>