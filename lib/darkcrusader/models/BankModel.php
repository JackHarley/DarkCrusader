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
use darkcrusader\sqlbeans\BankTransactionBean;

use darkcrusader\bank\exceptions\IncorrectTransactionLogPasteException;

class BankModel extends Model {
	
	protected static $modelID = "Bank";

	/**
	 * Gets the current bank balance
	 * 
	 * @return int number of credits in bank
	 */
	public function getCurrentBankBalance__5200_bank() {
		$q = new Query("SELECT");
		$q->orderby("date", "DESC");
		$q->limit(1);

		$btbs = BankTransactionBean::select($q);
		return $btbs[0]->balance;
	}

	/**
	 * Gets the latest x transactions and returns them
	 * 
	 * @param int $limit number of transactions to get
	 */
	public function getLatestTransactions__5200_bank($limit=10) {
		$q = new Query("SELECT");
		$q->orderby("date", "DESC");
		$q->limit($limit);

		return BankTransactionBean::select($q, true);
	}

	/**
	 * Gets an associative array of players and the amount they have in total donated
	 * to the faction bank
	 * 
	 * @param int $limit maximum number of players to get, set to true for all
	 * @return array Player Name => Total Donation
	 */
	public function getPlayerTotalDonations__5200_bank($limit=true) {
		$q = new Query("SELECT");
		$q->where("type = ?", "transfer");
		$q->where("direction = ?", "in");

		$btbs = BankTransactionBean::select($q);

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
		$bestDonors = $this->getPlayerTotalDonationsCached(8);

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

		$myPicture = new \pImage(900,500,$data);
		$GradientSettings = array("StartR"=>0,"StartG"=>191,"StartB"=>255,"Alpha"=>100,"Levels"=>50);
		$myPicture->drawGradientArea(0,0,1000,500,DIRECTION_VERTICAL,$GradientSettings);
		$myPicture->setGraphArea(100,70,870,450);
		$myPicture->drawText(450,45,"Total Credits Donated by Top 8 Faction Members",array("FontSize"=>20,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE));
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
	 * @param int $amount transaction amount
	 * @param int $balance bank balance after transaction
	 * @param date YYYY-MM-DD HH MM SS $date date and time of transaction
	 * @param string $player player name, if applicable
	 * @param boolean $checkDuplicate if set to true, checks if transaction is duplicate before inserting
	 */
	public function addTransaction($type, $direction, $amount, $balance, $date, $player=false, $checkDuplicate=true) {
		if ($checkDuplicate) {
			$q = new Query("SELECT");
			$q->where("date = ?", $date);
			$q->where("amount = ?", $amount);
			$q->where("balance = ?", $balance);
			$q->where("direction = ?", $direction);

			$btbs = BankTransactionBean::select($q);
			if ($btbs[0])
				return false;
		}

		$btb = new BankTransactionBean;
		$btb->type = $type;
		$btb->direction = $direction;
		$btb->amount = $amount;
		$btb->balance = $balance;
		$btb->date = $date;
		if (($player) && (is_string($player)))
			$btb->player_name = $player;

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

			// if oe type is not 'faction', then the user probably pasted their own accidentally,
			// or else something else went horribly wrong with parsing
			if ($oeType != "Faction")
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
			}
			else if ($words[2] == "to") {
				$type = "transfer";
				$direction = "in";
				$player = $words[6] . " " . $words[7];
			}
			else if ($words[2] == "from") {
				$type = "transfer";
				$direction = "out";
				$player = $words[6] . " " . $words[7];
			}

			$result = $this->addTransaction(
				$type,
				$direction,
				$amount,
				$balance,
				$datetime,
				$player
			);

			if ($result === true)
				$transactionsAdded++;
		}

		return $transactionsAdded;
	}
}
?>