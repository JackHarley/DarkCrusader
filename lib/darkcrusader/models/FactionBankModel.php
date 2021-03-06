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
use darkcrusader\models\SystemModel;
use darkcrusader\sqlbeans\FactionBankTransactionBean;

use darkcrusader\bank\exceptions\IncorrectTransactionLogPasteException;
use darkcrusader\bank\exceptions\NoFactionBankTransactionsAddedException;

class FactionBankModel extends Model {
	
	protected static $modelID = "FactionBank";

	/**
	 * Gets the current bank balance
	 * 
	 * @return int number of credits in bank
	 */
	public function getCurrentBankBalance() {
		$q = new Query("SELECT");
		$q->orderby("date", "DESC");
		$q->limit(1);

		$btbs = FactionBankTransactionBean::select($q);

		if (!$btbs[0])
			throw new NoFactionBankTransactionsAddedException;

		return $btbs[0]->balance;
	}

	/**
	 * Gets the latest x transactions and returns them
	 * 
	 * @param int $limit number of transactions to get
	 */
	public function getLatestTransactions($limit=10) {
		$q = new Query("SELECT");
		$q->orderby("date", "DESC");
		$q->limit($limit);

		$transactions = FactionBankTransactionBean::select($q, true);

		if (!$transactions[0])
			throw new NoFactionBankTransactionsAddedException;

		return $transactions;
	}

	/**
	 * Gets an associative array of players and the amount they have in total donated
	 * to the faction bank
	 * 
	 * @param int $limit maximum number of players to get, set to true for all
	 * @return array Player Name => Total Donation
	 */
	public function getPlayerTotalDonations($limit=true) {
		$q = new Query("SELECT");
		$q->where("type = ?", "transfer");
		$q->where("direction = ?", "in");

		$btbs = FactionBankTransactionBean::select($q);
		if (!$btbs[0])
			throw new NoFactionBankTransactionsAddedException;

		$players = array();

		foreach($btbs as $btb) {
			if (!$btb->player_name)
				continue;

			if (!$players[$btb->player_name])
				$players[$btb->player_name] = 0;

			$players[$btb->player_name] += $btb->amount;
		}

		// sort players by amount of credits donated
		$orderedPlayers = array();
		while (count($players) > 0) {
			$best = array("player" => null, "credits" => 0);

			foreach($players as $player => $creditsDonated) {
				if ($creditsDonated > $best["credits"]) {
					$best["player"] = $player;
					$best["credits"] = $creditsDonated;
				}

			}

			$orderedPlayers[] = $best;
			unset($players[$best["player"]]);
		}

		if ($limit !== true) {
			for($i=$limit;$orderedPlayers[$i];$i++) {
				unset($orderedPlayers[$i]);
			}
		}

		return $orderedPlayers;

	}

	/**
	 * Generates the donors bar chart and stores it in /graphs/bankdonors.png
	 */
	public function generateDonorsGraph() {
		$bestDonors = $this->getPlayerTotalDonations(5);

		$creditsDonated = array();
		$donors = array();
		foreach($bestDonors as $donor) {
			$creditsDonated[] = $donor["credits"];
			$donors[] = $donor["player"];
		}

		$data = new \pData;
		$data->addPoints($creditsDonated, "Credits");
		$data->setAxisName(0, "Credits");
		$data->addPoints($donors, "Players");
		$data->setSerieDescription("Players", "Players");
		$data->setAbscissa("Players");
		$serieSettings = array("R"=>0,"G"=>0,"B"=>0,"Alpha"=>80);
		$data->setPalette("Credits",$serieSettings);

		$myPicture = new \pImage(750,500,$data);
		$GradientSettings = array("StartR"=>0,"StartG"=>191,"StartB"=>255,"Alpha"=>100,"Levels"=>50);
		$myPicture->drawGradientArea(0,0,750,500,DIRECTION_VERTICAL,$GradientSettings);
		$myPicture->setGraphArea(100,70,700,450);
		$myPicture->drawText(375,45,"Total Credits Donated by Top 7 Faction Members",array("FontSize"=>20,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE));
		$myPicture->drawScale(array("CycleBackground"=>TRUE,"DrawSubTicks"=>TRUE,"GridR"=>0,"GridG"=>0,"GridB"=>0,"GridAlpha"=>10)); 
		$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));
		$myPicture->drawBarChart(array("DisplayPos"=>LABEL_POS_OUTSIDE,"DisplayValues"=>TRUE,"Rounded"=>TRUE,"Surrounding"=>30));
		$myPicture->render(__DIR__ . "/../../../graphs/bankdonors.png"); 

	} 

	/**
	 * Adds a transaction to the DB
	 * 
	 * @param string $type type of transaction, 'transfer' or 'join_fee'
	 * @param string $direction transfer direction, 'in' or 'out'
	 * @param string $description transaction description according to OE
	 * @param int $amount transaction amount
	 * @param int $balance bank balance after transaction
	 * @param date YYYY-MM-DD HH MM SS $date date and time of transaction
	 * @param string $player player name, if applicable
	 * @param string $system system id, if applicable
	 * @param string $planetNumeral planet numeral, e.g. III, if applicable
	 * @param boolean $checkDuplicate if set to true, checks if transaction is duplicate before inserting
	 */
	public function addTransaction($type, $direction, $description, $amount, $balance, $date, $player=false, 
									$system=false, $planetNumeral=false, $checkDuplicate=true) {
		if ($checkDuplicate) {
			$q = new Query("SELECT");
			$q->where("date = ?", $date);
			$q->where("amount = ?", $amount);
			$q->where("balance = ?", $balance);
			$q->where("direction = ?", $direction);

			$btbs = FactionBankTransactionBean::select($q);
			if ($btbs[0])
				return false;
		}

		$btb = new FactionBankTransactionBean;
		$btb->type = $type;
		$btb->direction = $direction;
		$btb->description = $description;
		$btb->amount = $amount;
		$btb->balance = $balance;
		$btb->date = $date;
		if ($player) 
			$btb->player_name = $player;
		if ($system)
			$btb->system_id = $system;
		if ($planetNumeral)
			$btb->planet_numeral = $planetNumeral;

		$btb->insert();

		RECacheManager::getInstance()->clearGroup("bank");

		return true;
	}

	/**
	 * Parses a transaction log paste and adds applicable transactions to the
	 * database
	 * 
	 * @param string $paste transaction log paste
	 * @return int number of transactions added successfully
	 */
	public function parseTransactionLogPaste($paste) {

		// split it into each transaction (line)
		$transactions = explode("\n", $paste);

		// parse each transaction
		$transactionsAdded = 0;
		foreach($transactions as $transaction) {

			// split into each field:
			//
			// datetime, 
			// type according to OE (always Faction),
			// description of transaction,
			// amount of credits,
			// balance after transaction

			$transaction = str_replace("\t", "  ", $transaction);

			$fields = explode("  ", $transaction);

			$datetime = $fields[0];
			$oeType = $fields[1];

			// if oe type is not 'faction' or 'station', then the user probably pasted their own accidentally,
			// or else something else went horribly wrong with parsing
			if (($oeType != "Faction") && ($oeType != "Station"))
				throw new IncorrectTransactionLogPasteException;

			$description = $fields[2];
			$amount = $fields[3];
			$balance = $fields[4];

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

			// work out what kind of transaction we're dealing with, the direction and the player
			$words = explode(" ", $description);
			if ($words[0] == "Membership") {
				$type = "join_fee";
				$direction = "out";
				$player = $words[3] . " " . $words[4];
				$system = false;
				$planetNumeral = false;
			}
			else if (($words[1] == "transferred") && ($words[2] == "to")) {
				$type = "transfer";
				$direction = "in";
				$player = $words[6] . " " . $words[7];
				$system = false;
				$planetNumeral = false;
			}
			else if (($words[1] == "transferred") && ($words[2] == "from")) {
				$type = "transfer";
				$direction = "out";
				$player = $words[6] . " " . $words[7];
				$system = false;
				$planetNumeral = false;
			}
			else if ($words[3] == "fuel") {
				$type = "fuel";
				$direction = "in";
				$player = false;
				$system = $words[6];
				$planetNumeral = $words[7];
			}
			else if ($words[0] == "Market") {
				$type = "market";
				$direction = "in";
				$player = false;
				$system = $words[4];
				$planetNumeral = $words[5];
			}

			// if system, convert system name to a db id
			if ($system) {
				$system = SystemModel::getInstance()->getSystem(false, $system);
				$systemId = $system->id;
			}
			else
				$systemId = 0;

			$result = $this->addTransaction(
				$type,
				$direction,
				$description,
				$amount,
				$balance,
				$datetime,
				$player,
				$systemId,
				$planetNumeral
			);

			if ($result === true)
				$transactionsAdded++;
		}

		return $transactionsAdded;
	}
}
?>