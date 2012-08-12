<?php
/**
 * Empire Controller
 * Controls all sorts of empire related features including sales, stored items, etc
 *
 * Copyright (c) 2012, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\controllers;

use darkcrusader\controllers\Controller;

use darkcrusader\models\MarketModel;
use darkcrusader\models\PremiumPersonalBankModel;
use darkcrusader\models\UserModel;
use darkcrusader\models\StoredItemsModel;

use hydrogen\view\View;

class EmpireController extends Controller {
	
	public function checkAuth($permissions=array(), $endIfNoPermission=true) {
		if (!UserModel::getInstance()->checkIfUserIsPremium(UserModel::getInstance()->getActiveUser()->id)) {
			if ($endIfNoPermission)
				$this->permissionDenied();
		}

		parent::checkAuth($permissions, $endIfNoPermission);
	}

	public function index() {
		$this->checkAuth("access_empire");

		View::load('empire/index');
	}

	public function seller() {
		$this->checkAuth("access_empire");

		$user = UserModel::getInstance()->getActiveUser();

		$period = ($_GET["period"]) ? $_GET["period"] : "forever";

		PremiumPersonalBankModel::getInstance()->updateDB($user->id);
		$topCustomers = MarketModel::getInstance()->getTopCustomers($user->id, $period, 10);

		View::load('empire/seller', array(
			"topCustomers" => $topCustomers,
			"period" => $period
		));
	}

	public function resources() {
		$this->checkAuth("access_empire");

		View::load('empire/resources', array(
			"resources" => StoredItemsModel::getInstance()->getStoredResources(UserModel::getInstance()->getActiveUser()->id),
		));
	}
}
?>