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
use darkcrusader\character\OECharacter;
use darkcrusader\oe\exceptions\APIQueryFailedException;

class OuterEmpiresModel extends Model {
	
	protected static $modelID = "OE";
	protected static $WSDL = "http://oeapi.outer-empires.com/OEAPI.asmx?WSDL";
	protected static $soapInstance = false;
	
	protected static $quickCache = array();
	
	/**
	 * Queries the OE API with the specified query and returns the result
	 * as an object
	 * 
	 * @param string $method method name
	 * @param array $parameters associative array of parameters to pass
	 * @param int $accessKey access key (user api key) to use
	 */
	protected function queryAPI($method, $parameters=array(), $accessKey) {
		$applicationKey = Config::getRequiredVal('general', 'oe_api_application_key');
		
		$query = array(
			"AccessKey" => $accessKey,
			"ApplicationKey" => $applicationKey,
		);
		
		foreach ($parameters as $key => $value)
			$query[$key] = $value;

		try {
			if (!static::$soapInstance)
				static::$soapInstance = new \SoapClient(static::$WSDL);

			$result = static::$soapInstance->{$method}($query);
		}
		catch (\Exception $e) {
			throw new APIQueryFailedException;
		}

		return $result;
	}

	/**
	 * Runs a simple transaction query using the access key supplied in order
	 * to verify that it is a working access key
	 * 
	 * @param string $key access key to test
	 * @return boolean true if key works, false if not
	 */
	public function testAccessKey($key) {
		$response = $this->queryAPI("GetTransactions", array("Days" => 1), $key);
		
		if ($response->GetTransactionsResult->valid == 1)
			return true;
		else
			return false;
	}

	/**
	 * Gets the latest player bank transactions for a user within the
	 * specified number of days
	 * 
	 * @param int $user user id
	 * @param string $days time period in days
	 * @param boolean $cache set to true to allow the quick cache to work (this script run only)
	 * @param mixed $accessKey leave as boolean false to use access key for default character of user
	 * specified, or optionally override the user and use the access key supplied
	 */
	public function getPlayerBankTransactions($user, $days, $cache=true, $accessKey=false) {
		
		if ($cache) {
			if (is_array(static::$quickCache[$user . "-" . $days]))
				return static::$quickCache[$user . "-" . $days];
		}

		if ($accessKey)
			$userAccessKey = $accessKey;
		else
			$userAccessKey = UserModel::getInstance()->getDefaultCharacter($user)->api_key;

		$working = $this->queryAPI("GetTransactions", array("Days" => $days), $userAccessKey);
		
		$transactions = $working->GetTransactionsResult->Transactions->Transaction;
		
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
				case "Ship Mod": // for some reason Jacky Jhonson had 0c income from ship mods, meh, add an overried
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

			// other stuff
			if ($type == "Transfer") {
				switch ($direction) {
					case "out":
						$working = explode(" credits to ", $description);
						$characterName = $working[1];
					break;
					case "in":
						$working = explode(" transferred ", $description);
						$characterName = $working[0];
					break;
				}
			}

			$bt = new PersonalBankTransaction;
			$bt->date = $datetime;
			$bt->type = $type;
			$bt->description = $description;
			$bt->amount = $amount;
			$bt->balance = $balance;
			$bt->direction = $direction;
			$bt->characterName = $characterName;
			
			$bts[] = $bt;
		}
		
		if ($cache)
			static::$quickCache[$user . "-" . $days] = $bts;
		
		return $bts;
	}

	/**
	 * Gets character info for a user's default character
	 * 
	 * @param int $user user id
	 * @param mixed $accessKey leave as boolean false to use access key for default character of user
	 * specified, or optionally override the user and use the access key supplied
	 * @return OECharacter character info
	 */
	public function getCharacterInfo($user, $accessKey=false) {

		if ($accessKey)
			$userAccessKey = $accessKey;
		else
			$userAccessKey = UserModel::getInstance()->getDefaultCharacter($user)->api_key;

		$response = $this->queryAPI("GetCharacterInfo", array(), $userAccessKey);
		$response = $response->GetCharacterInfoResult;

		$c = new OECharacter;
		$c->name = $response->FirstName . " " . $response->LastName;
		$c->rank = $response->Rank;
		$c->factionName = $response->FactionName;
		$c->factionTag = $response->FactionTag;
		$c->factionRank = $response->FactionTitle;

		return $c;
	}

}
?>