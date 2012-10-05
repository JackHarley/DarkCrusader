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
use darkcrusader\models\BlueprintsModel;
use darkcrusader\models\PremiumPersonalBankModel;
use darkcrusader\models\UserModel;
use darkcrusader\models\StoredItemsModel;
use darkcrusader\models\FactionResearchModel;

use hydrogen\view\View;

use darkcrusader\oe\exceptions\TooManyTransactionsToFetchException;

class EmpireController extends Controller {
	
	public function checkAuth($permissions=array(), $endIfNoPermission=true) {
		parent::checkAuth($permissions, $endIfNoPermission);

		$this->checkForValidCharacterAndAPIKey();
	}

	public function index() {
		$this->checkAuth("access_empire");

		$um = UserModel::getInstance();
		$user = $um->getActiveUser();

		if ($um->checkIfUserIsPremium($user->id)) {
			$bm = PremiumPersonalBankModel::getInstance();
			
			try {
				$bm->updateDB($user->id);
			}
			catch (TooManyTransactionsToFetchException $e) {
				View::load('personal_bank/full_update_required');
				return;
			}

			$workerCostsLastWeek = $bm->getWorkerCosts($user->id, "last7days");
			View::setVar("workerCostsLastWeek", $workerCostsLastWeek);

			$marketSalesLastWeek = $bm->getMarketSales($user->id, "last7days");
			View::setVar("marketSalesLastWeek", $marketSalesLastWeek);

			View::setVar("profitOrLossLastWeek", ($marketSalesLastWeek - $workerCostsLastWeek));
		}

		View::load('empire/index');
	}

	public function seller() {
		$this->checkAuth("access_empire");

		$um = UserModel::getInstance();
		$user = $um->getActiveUser();

		if (!$um->checkIfUserIsPremium($user->id))
			$this->permissionDenied();

		$period = ($_GET["period"]) ? $_GET["period"] : "forever";

		try {
			PremiumPersonalBankModel::getInstance()->updateDB($user->id);
		}
		catch (TooManyTransactionsToFetchException $e) {
			View::load('personal_bank/full_update_required');
			return;
		}

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

	public function manufacturing($act=false) {

		$this->checkAuth("access_empire");

		$sim = StoredItemsModel::getInstance();
		$cm = ColoniesModel::getInstance();
		$bm = BlueprintsModel::getInstance();

		$user = UserModel::getInstance()->getActiveUser();

		$cm->updateDB($user->id);
		$sim->updateDB($user->id);

		switch($act) {
			case "blueprintresources":
				for($i=1; isset($_POST["resourcename" . $i]); $i++) {
					$bm->addBlueprintResource($_POST["blueprint"], $_POST["resourcename" . $i], $_POST["resourcequantity" . $i]);
				}
				$this->redirect("/index.php/empire/manufacturing");
			break;
		}

		// step 3: show route to take
		if ($_POST["submit2"]) {
			$result = $cm->calculateOptimalManufacturingRoute($_POST["blueprint"], $_POST, $_POST["fuel"], $_POST["fuel_per_lightyear"], $_POST["ship_storage_capacity"], $_POST["start_system"], $_POST["manufacturing_colony_name"], $user->id);
			
			if ($result->handycapResource)
				View::setVar("handycapResource", $result->handycapResource);

			View::load('manufacturing/route', array(
				"instructions" => $result->instructions,
				"handycap" => $result->handycap,
				"items" => $result->items,
				"blueprint" => $result->blueprintDescription
			));
		}
		// step 2: choose colonies you're willing to collect from (IF RESOURCES FOR BP ARE UNKNOWN, PROMPT!),
		//         manufacturing colony to dropoff at, ship storage capacity, start location
		else if ($_POST["submit"]) {
			
			if ($resources = $bm->getBlueprintResources($_POST["blueprint"])) {
				$resourceOccurences = array();
				foreach($resources as $resource) {
					$working = $sim->getOccurencesOfResource($user->id, $resource->resource_name);

					$resourceOccurences = array_merge($resourceOccurences, $working);
				}

				View::load("manufacturing/route_settings", array(
					"resourceOccurences" => $resourceOccurences,
					"manufacturingColonies" => $cm->getColonies($user->id, "manufacturing"),
					"blueprintDescription" => $_POST["blueprint"]
				));
			}
			else {
				View::load('manufacturing/blueprint_resources', array(
					"blueprintDescription" => $_POST["blueprint"]
				));
			}
		}
		// step 1: pick blueprint
		else {
			View::load('manufacturing/select_blueprint', array(
				"blueprints" => $sim->getStoredBlueprints($user->id)
			));
		}
	}
}
?>