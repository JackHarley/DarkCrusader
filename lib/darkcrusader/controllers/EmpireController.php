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
use darkcrusader\models\ColoniesModel;
use darkcrusader\models\PremiumPersonalBankModel;
use darkcrusader\models\UserModel;
use darkcrusader\models\StoredItemsModel;
use darkcrusader\models\FactionResearchModel;

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

		$user = UserModel::getInstance()->getActiveUser();

		if (in_array($user->group->id, array(1,5,6,7,8)))
			FactionResearchModel::getInstance()->updateDB($user->id);

		$bm = PremiumPersonalBankModel::getInstance();
		$bm->updateDB($user->id);

		$workerWagesPastWeek = $bm->getWorkerCosts($user->id, "last7days");
		$marketSalesPastWeek = $bm->getMarketSales($user->id, "last7days");
		$profitPastWeek = $marketSalesPastWeek - $workerWagesPastWeek;

		View::load('empire/index', array(
			"workerCostsLastWeek" => $workerWagesPastWeek,
			"marketSalesLastWeek" => $marketSalesPastWeek,
			"profitOrLossLastWeek" => $profitPastWeek
		));
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

	public function colonies($act=false) {
		$this->checkAuth("access_empire");

		$user = UserModel::getInstance()->getActiveUser();

		$cm = ColoniesModel::getInstance();
		$sim = StoredItemsModel::getInstance();

		$cm->updateDB($user->id);
		$sim->updateDB($user->id);

		switch ($act) {
			case "classify":
				$cm->classifyColony($user->id, $_POST["id"], $_POST["primary_activity"]);
			break;
			case "colony":
				$colony = $cm->getColony($_GET["id"]);

				if ($colony->user_id != $user->id)
					$this->permissionDenied();
				
				View::load('empire/colony', array(
					"colony" => $colony
				));
				return;
			break;
		}

		View::load('empire/colonies', array(
			"unclassifiedColonies" => $cm->getColonies($user->id, ""),
			"miningColonies" => $cm->getColonies($user->id, "mining"),
			"processingColonies" => $cm->getColonies($user->id, "processing"),
			"refiningColonies" => $cm->getColonies($user->id, "refining"),
			"manufacturingColonies" => $cm->getColonies($user->id, "manufacturing"),
			"researchColonies" => $cm->getColonies($user->id, "research"),
			"defenseColonies" => $cm->getColonies($user->id, "defense")
		));
	}
}
?>