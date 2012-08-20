<?php
/**
 * Bank Model
 * Handles the faction bank stuff
 *
 * Copyright (c) 2012, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\models;

use hydrogen\database\Query;
use hydrogen\recache\RECacheManager;

use darkcrusader\models\PersonalBankModel;
use darkcrusader\models\UserModel;
use darkcrusader\models\OuterEmpiresModel;

use darkcrusader\sqlbeans\PersonalBankTransactionBean;

class PremiumPersonalBankModel extends PersonalBankModel {
	
	protected static $modelID = "PremiumPersonalBank";

	/**
	 * Updates the DB with any new transactions for the specified user
	 * 
	 * @param int $user user id
	 */
	public function updateDB($user) {

		// work out how many days we need to get to ensure we update the db fully
		$q = new Query("SELECT");
		$q->orderby("date", "DESC");
		$q->where("user_id = ?", $user);
		$q->limit(1);

		$pbtbs = PersonalBankTransactionBean::select($q);

		$latestTransferKnown = $pbtbs[0];

		if (!$latestTransferKnown) {
			$daysToGet = 0; // fetch all (paul will be mad though)
		}
		else {
			$timeNow = time();
			$timeThen = strtotime($latestTransferKnown->date);

			$secondsSinceUpdate = $timeNow - $timeThen;

			$daysToGet = ceil($secondsSinceUpdate / (3600 * 24));
		}

		$transactions = OuterEmpiresModel::getInstance()->getPlayerBankTransactions($user, $daysToGet);

		foreach($transactions as $transaction) {

			// add to db with duplicate check enabled
			$this->addTransaction(
				$user, 
				$transaction->type, 
				$transaction->direction, 
				$transaction->amount, 
				$transaction->balance, 
				$transaction->date, 
				$transaction->description, 
				true
			);
		}
	}

	/**
	 * Gets the current bank balance
	 * 
	 * @return int number of credits in bank
	 */
	public function getCurrentBankBalance($user) {
		$q = new Query("SELECT");
		$q->orderby("date", "DESC");
		$q->where("user_id = ?", $user);
		$q->limit(1);

		$btbs = PersonalBankTransactionBean::select($q);
		return $btbs[0]->balance;
	}

	/**
	 * Gets a user's richest moment (highest balance)
	 *
	 * @param int $user user id
	 * @return PersonalTransactionBean transaction at which balance was highest
	 */
	public function getRichestMoment($user) {
		$q = new Query("SELECT");
		$q->orderby("balance", "DESC");
		$q->where("user_id = ?", $user);
		$q->limit(1);

		$btbs = PersonalBankTransactionBean::select($q);
		return $btbs[0];
	}

	/**
	 * Gets the latest x transactions and returns them
	 * 
	 * @param int $user user id
	 * @param int $limit number of transactions to get
	 */
	public function getLatestTransactions($user, $limit=10) {
		$q = new Query("SELECT");
		$q->orderby("date", "DESC");
		$q->where("user_id = ?", $user);
		$q->limit($limit);

		return PersonalBankTransactionBean::select($q, true);
	}

	/**
	 * Get worker wages paid for a time period
	 * 
	 * @param int $user user id
	 * @param string $period time period, 'forever', 'last24hours', 'last7days' or 'last30days'
	 * @return int worker wages paid
	 */
	public function getWorkerCosts($user, $period="forever") {
		$q = new Query("SELECT");
		$q->where("direction = ?", "out");
		$q->where("user_id = ?", $user);
		$q->where("type = ?", "Colony");

		switch($period) {
			case "last24hours":
				$q->where("date > DATE_SUB(NOW(), INTERVAL 1 DAY)");
			break;
			case "last7days":
				$q->where("date > DATE_SUB(NOW(), INTERVAL 7 DAY)");
			break;
			case "last30days":
				$q->where("date > DATE_SUB(NOW(), INTERVAL 30 DAY)");
			break;
		}

		$btbs = PersonalBankTransactionBean::select($q);

		$total = 0;
		foreach($btbs as $btb) {
			$total += $btb->amount;
		}

		return $total;
	}

	/**
	 * Get market sales total for a time period
	 * 
	 * @param int $user user id
	 * @param string $period time period, 'forever', 'last24hours', 'last7days' or 'last30days'
	 * @return int total market sales
	 */
	public function getMarketSales($user, $period="forever") {
		$q = new Query("SELECT");
		$q->where("direction = ?", "in");
		$q->where("user_id = ?", $user);
		$q->where("type = ?", "Market");

		switch($period) {
			case "last24hours":
				$q->where("date > DATE_SUB(NOW(), INTERVAL 1 DAY)");
			break;
			case "last7days":
				$q->where("date > DATE_SUB(NOW(), INTERVAL 7 DAY)");
			break;
			case "last30days":
				$q->where("date > DATE_SUB(NOW(), INTERVAL 30 DAY)");
			break;
		}

		$btbs = PersonalBankTransactionBean::select($q);

		$total = 0;
		foreach($btbs as $btb) {
			$total += $btb->amount;
		}

		return $total;
	}

	/**
	 * Gets an associative array of types of transaction and the amount of credits each
	 * type generated for the time period
	 * 
	 * @param int $user user id
	 * @param string $period time period, 'forever', 'last24hours', 'last7days' or 'last30days'
	 * @param string $direction 'in' for income, 'out' for expenditure
	 * @return array Type => Credits 
	 */
	public function getTransactionTypes($user, $period="forever", $direction) {
		$q = new Query("SELECT");
		$q->where("direction = ?", $direction);
		$q->where("user_id = ?", $user);

		switch($period) {
			case "last24hours":
				$q->where("date > DATE_SUB(NOW(), INTERVAL 1 DAY)");
			break;
			case "last7days":
				$q->where("date > DATE_SUB(NOW(), INTERVAL 7 DAY)");
			break;
			case "last30days":
				$q->where("date > DATE_SUB(NOW(), INTERVAL 30 DAY)");
			break;
		}

		$btbs = PersonalBankTransactionBean::select($q);

		$types = array();

		foreach($btbs as $btb) {
			if (!$types[$btb->type])
				$types[$btb->type] = 0;

			$types[$btb->type] += $btb->amount;
		}

		// sort types by amount of credits earned
		$orderedTypes = array();
		while (count($types) > 0) {
			$best = array("type" => null, "credits" => 0);

			foreach($types as $type => $credits) {
				if ($credits > $best["credits"]) {
					$best["type"] = $type;
					$best["credits"] = $credits;
				}

			}

			$orderedTypes[] = $best;
			unset($types[$best["type"]]);
		}

		return $orderedTypes;
	}

	/**
	 * Generates an income/expenditure graph for the specified user and returns the name of the file in
	 * /graphs/
	 * 
	 * @param int $user user id
	 * @param string $period time period, 'forever', 'last24hours', 'last7days' or 'last30days'
	 * @param string $direction 'in' for income, 'out' for expenditure
	 * @return string name of image in /graphs/
	 */
	public function generateTransactionTypesGraph($user, $period="forever", $direction) {
		$bestTypes = $this->getTransactionTypes($user, $period, $direction);

		$credits = array();
		$types = array();
		foreach($bestTypes as $type) {
			$credits[] = $type["credits"];
			$types[] = $type["type"];
		}

		$data = new \pData;
		$data->addPoints($credits, "Credits");
		$data->addPoints($types, "Types");
		$data->setSerieDescription("Types", "Types");
		$data->setSerieDescription("Credits", "Credits");
		$data->setAbscissa("Types");

		$myPicture = new \pImage(700,450,$data);
		$GradientSettings = array("StartR"=>0,"StartG"=>191,"StartB"=>255,"Alpha"=>100,"Levels"=>50);
		$myPicture->drawGradientArea(0,0,700,450,DIRECTION_VERTICAL,$GradientSettings);

		switch ($period) {
			case "forever":
				$period = "All Time";
			break;
			case "last24hours":
				$period = "Last 24 Hours";
			break;
			case "last7days":
				$period = "Last 7 Days";
			break;
			case "last30days":
				$period = "Last 30 Days";
			break;

		}
		if ($direction == "in")
			$myPicture->drawText(350,45,"Breakdown of Income - " . $period,array("FontSize"=>20,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE));
		else if ($direction == "out")
			$myPicture->drawText(350,45,"Breakdown of Expenditure - " . $period,array("FontSize"=>20,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE));

		$pie = new \pPie($myPicture, $data);
		$pie->draw3DPie(350,250,array("WriteValues"=>PIE_VALUE_NATURAL,"Border"=>TRUE,"Radius"=>220,"ValuePosition"=>PIE_VALUE_OUTSIDE,"ValuePadding"=>45,"DataGapAngle"=>7,"DataGapRadius"=>6,"ValueSuffix"=>"c"));
		$pie->drawPieLegend(30,80,array("Alpha"=>20));

		$name = rand(100000,999999);
		$name .= ".png";

		if (file_exists(__DIR__ . "/../../../graphs/" . $name))
			unlink(__DIR__ . "/../../../graphs/" . $name);

		$myPicture->render(__DIR__ . "/../../../graphs/" . $name);

		return $name;

	}

	/**
	 * Adds a transaction to the DB
	 * 
	 * @param int $user user id
	 * @param string $type type of transaction, e.g. Ship Refuel, Market Sale, Job, BP Copy
	 * @param string $direction transfer direction, 'in' or 'out'
	 * @param int $amount transaction amount
	 * @param int $balance bank balance after transaction
	 * @param date YYYY-MM-DD HH MM SS $date date and time of transaction
	 * @param string $description description of transaction, according to oe, for future parsing features
	 * @param boolean $checkDuplicate if set to true, checks if transaction is duplicate before inserting
	 */
	public function addTransaction($user, $type, $direction, $amount, $balance, $date, $description, $checkDuplicate=true) {
		if ($checkDuplicate) {
			$q = new Query("SELECT");
			$q->where("date = ?", $date);
			$q->where("balance = ?", $balance);
			$q->where("user_id = ?", $user);

			$btbs = PersonalBankTransactionBean::select($q);
			if ($btbs[0])
				return false;
		}

		$btb = new PersonalBankTransactionBean;
		$btb->user_id = $user;
		$btb->type = $type;
		$btb->direction = $direction;
		$btb->amount = $amount;
		$btb->balance = $balance;
		$btb->description = $description;
		$btb->date = $date;

		$btb->insert();

		return true;
	}
}
?>