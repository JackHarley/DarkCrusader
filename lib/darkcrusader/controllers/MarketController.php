<?php
/**
 * Market Controller
 * Controls handy market features
 *
 * Copyright (c) 2012, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\controllers;

use darkcrusader\controllers\Controller;

use darkcrusader\models\MarketModel;
use darkcrusader\models\UserModel;

use hydrogen\view\View;

class MarketController extends Controller {
	
	public function index() {
		$this->checkAuth("access_market");

		View::load('market/index');
	}

	public function seller() {
		$this->checkAuth("access_market_seller_overview");

		$um = UserModel::getInstance();

		$user = $um->getActiveUser();

		if (!$um->checkIfUserIsPremium($user->id))
			$this->permissionDenied();

		$topCustomers = MarketModel::getInstance()->getTopCustomers($user->id, 10);

		View::load('market/seller', array(
			"topCustomers" => $topCustomers
		));
	}
}
?>