<?php
/**
 * Bank Model
 * Handles the faction bank stuff
 *
 * Copyright (c) 2012, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\models;

use hydrogen\model\Model;
use hydrogen\database\Query;
use hydrogen\recache\RECacheManager;

use darkcrusader\models\UserModel;
use darkcrusader\sqlbeans\PersonalBankTransactionBean;

use darkcrusader\bank\exceptions\IncorrectTransactionLogPasteException;

class PersonalBankModel extends Model {
	
	protected static $modelID = "PersonalBank";

	/**
	 * Checks if a user has at least one transaction added to the database
	 * 
	 * @param int $user user id
	 * @return boolean true if user has at least one transaction, otherwise false
	 */
	public function checkIfUserHasAtLeastOneTransaction($user) {
		$q = new Query("SELECT");
		$q->where("user_id = ?", $user);
		$q->limit(1);

		$btbs = PersonalBankTransactionBean::select($q);
		if ($btbs[0])
			return true;
		else
			return false;
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
	 * Generates an income graph for the specified user and returns the name of the file in
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

		$myPicture = new \pImage(800,450,$data);
		$GradientSettings = array("StartR"=>0,"StartG"=>191,"StartB"=>255,"Alpha"=>100,"Levels"=>50);
		$myPicture->drawGradientArea(0,0,800,450,DIRECTION_VERTICAL,$GradientSettings);
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
			$myPicture->drawText(400,45,"Breakdown of Income - " . $period,array("FontSize"=>20,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE));
		else if ($direction == "out")
			$myPicture->drawText(400,45,"Breakdown of Expenditure - " . $period,array("FontSize"=>20,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE));

		$pie = new \pPie($myPicture, $data);
		$pie->draw3DPie(400,250,array("WriteValues"=>PIE_VALUE_NATURAL,"Border"=>TRUE,"Radius"=>220,"ValuePosition"=>PIE_VALUE_OUTSIDE,"ValuePadding"=>45,"DataGapAngle"=>7,"DataGapRadius"=>6,"ValueSuffix"=>"c"));
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
			$q->where("amount = ?", $amount);
			$q->where("balance = ?", $balance);
			$q->where("direction = ?", $direction);
			$q->where("user_id = ?", $user);
			$q->where("type = ?", $type);
			$q->where("description = ?", $description);

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

	/**
	 * Parses a transaction log paste and adds applicable transactions to the
	 * database
	 * 
	 * @param int $user user id
	 * @param string $paste transaction log paste
	 * @return int number of transactions added successfully
	 */
	public function parseTransactionLogPaste($user, $paste) {

		// split it into each transaction (line)
		$transactions = explode("\n", $paste);

		// parse each transaction
		$transactionsAdded = 0;
		foreach($transactions as $transaction) {

			// split into each field:
			//
			// datetime, 
			// type e.g. Job, Ship Refuel, etc
			// description of transaction,
			// amount of credits,
			// balance after transaction

			$transaction = str_replace("\t", "  ", $transaction);

			$fields = explode("  ", $transaction);

			$datetime = $fields[0];
			$type = $fields[1];
			$description = $fields[2];
			$amount = $fields[3];
			$balance = $fields[4];

			if ((!$datetime) || (!$type) || (!$description) || (!$amount) || (!$balance))
				continue;

			// direction
			if (strpos($amount, "-") !== false)
				$direction = "out";
			else if (strpos($amount, "-") === false)
				$direction = "in";

			// direction overrides to patch silly oe bugs (like clones appearing as positive amounts)
			switch ($type) {
				case "Clone": // simple transaction log bug, there's no - before the amount
					$direction = "out";
				break;
				case "Refuel": // not sure why this happens, definitely a bug
					$direction = "out";
				break;
				case "Colony": // appears to be a once off thing when colony payments initiated on the 13th
							   // included here for backwards compatibility
					$direction = "out";
				break;
			}

			// if amount is negative, change it to positive, we don't deal with signed ints
			$amount = str_replace("-", "", $amount);

			// convert date to mysql friendly YYYY-MM-DD HH MM SS
			$dateAndTime = explode(" ", $datetime);
			
			$date = $dateAndTime[0];
			$dayMonthAndYear = explode("/", $date);
			$day = $dayMonthAndYear[0];
			$month = $dayMonthAndYear[1];
			$year = $dayMonthAndYear[2];

			$time = $dateAndTime[1];
			$hourMinuteAndSecond = explode(":", $time);
			$hour = $hourMinuteAndSecond[0];
			$minute = $hourMinuteAndSecond[1];
			$second = $hourMinuteAndSecond[2];

			$datetime = $year . "-" . $month . "-" . $day . " " . $hour . ":" . $minute . ":" . $second;

			$result = $this->addTransaction(
				$user,
				$type,
				$direction,
				$amount,
				$balance,
				$datetime,
				$description
			);

			if ($result === true)
				$transactionsAdded++;
		}

		return $transactionsAdded;
	}
}
?>