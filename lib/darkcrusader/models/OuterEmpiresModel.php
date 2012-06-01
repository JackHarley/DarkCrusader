<?php
/**
 * OE Model
 * Handles communications with the OE api/server
 *
 * Copyright (c) 2012, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\models;

use hydrogen\model\Model;
use hydrogen\config\Config;

use darkcrusader\models\UserModel;
use darkcrusader\bank\PersonalBankTransaction;

class OuterEmpiresModel extends Model {
	
	protected static $modelID = "OE";
	protected static $APIURL = "http://oeapi.outer-empires.com/OEAPI.asmx";
	
	protected static $quickCache = array();
	
	/**
	 * Gets the latest player bank transactions for a user within the
	 * specified number of days
	 * 
	 * @param int $user user id
	 * @param string $days time period in days
	 */
	public function getPlayerBankTransactions($user, $days, $cache=true) {
		
		if (is_array(static::$quickCache[$user . "-" . $days]))
			return static::$quickCache[$user . "-" . $days];
		
		$accessKey = UserModel::getInstance()->getUser($user)->oe_api_access_key;
		$applicationKey = Config::getVal('general', 'oe_api_application_key');
		
		$query = array(
			"callback" => "lol",
			"AccessKey" => '"' . $accessKey . '"',
			"ApplicationKey" => '"' . $applicationKey . '"',
			"Days" => $days
		);
		
		$url = static::$APIURL . "/GetTransactions?";
		
		$runs = 0;
		foreach($query as $key => $value) {
			if ($runs != 0)
				$url .= "&";
			
			$url .= $key . "=" . $value;
			
			$runs++;
		}
		
		$data = file_get_contents($url);
		
		// strip the callback off it
		$data = str_replace(array("lol(", ");"), "", $data);
		
		$working = json_decode($data)->d;
		
		$transactions = $working->Transactions;
		
		$bts = array();
		foreach($transactions as $transaction) {
			
			// conversions so that we can make a nice object for each transaction
			$datetime = $transaction->DTofTransaction;
			$type = $transaction->TypeofTrans;
			$description = $transaction->Detail;
			$amount = $transaction->Amount;
			$balance = $transaction->TotalBank;
	                        
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
			
			$bt = new PersonalBankTransaction;
			$bt->date = $datetime;
			$bt->type = $type;
			$bt->description = $description;
			$bt->amount = $amount;
			$bt->balance = $balance;
			$bt->direction = $direction;
			
			$bts[] = $bt;
		}
		
		$bts = array_reverse($bts);
		
		if ($cache)
			static::$quickCache[$user . "-" . $days] = $bts;
		
		return $bts;
	}
}
?>