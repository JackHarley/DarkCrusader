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
use darkcrusader\models\OuterEmpiresModel;
use darkcrusader\sqlbeans\PersonalBankTransactionBean;

use darkcrusader\bank\exceptions\IncorrectTransactionLogPasteException;

class PersonalBankModel extends Model {
	
	protected static $modelID = "PersonalBank";

	/**
	 * Gets the current bank balance
	 *
	 * @return int number of credits in bank
	 */
	public function getCurrentBankBalance($user) {
		$bts = $this->getLatestTransactions($user, 30, 1);
		return $bts[0]->balance;
	}

	/**
	 * Gets a user's richest moment (highest balance)
	 * NEEDS TO BE REWRITTEN FOR NEW SYSTEM
	 *
	 * @param int $user user id
	 * @return PersonalTransactionBean transaction at which balance was highest
	 */
	/*public function getRichestMoment($user) {
		$q = new Query("SELECT");
		$q->orderby("balance", "DESC");
		$q->where("user_id = ?", $user);
		$q->limit(1);

		$btbs = PersonalBankTransactionBean::select($q);
		return $btbs[0];
	}*/

	/**
	 * Gets the latest x transactions and returns them
	 * 
	 * @param int $user user id
	 * @param string $days time period in days
	 * @param int $limit number of transactions to get, set to false to get all
	 */
	public function getLatestTransactions($user, $days, $limit=false) {
		$bts = OuterEmpiresModel::getInstance()->getPlayerBankTransactions($user, $days);
		
		if ($limit) {
			for($i=1;$bts[$i];$i++) {
				if ($i > $limit)
					unset($bts[$i]);
			}
		}
		
		return $bts;
	}

	/**
	 * Gets an associative array of types of transaction and the amount of credits each
	 * type generated for the time period
	 * 
	 * @param int $user user id
	 * @param string $days time period in days
	 * @param string $direction 'in' for income, 'out' for expenditure
	 * @return array Type => Credits 
	 */
	public function getTransactionTypes($user, $days, $direction) {

		$bts = $this->getLatestTransactions($user, $days);
		
		foreach($bts as $key => $bt) {
			if ($bt->direction != $direction)
				unset($bts[$key]);
		}
		
		$types = array();

		foreach($bts as $bt) {
			if (!$types[$bt->type])
				$types[$bt->type] = 0;

			$types[$bt->type] += $bt->amount;
		}

		// sort types by amount of credits earned
		$orderedTypes = array();
		while (count($types) > 0) {
			$best = array("type" => null, "credits" => -1);

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
	 * @param string $days time period in days
	 * @param string $direction 'in' for income, 'out' for expenditure
	 * @return string name of image in /graphs/
	 */
	public function generateTransactionTypesGraph($user, $days, $direction) {
		$bestTypes = $this->getTransactionTypes($user, $days, $direction);

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

		if ($direction == "in")
			$myPicture->drawText(350,45,"Breakdown of Income - Last " . $days . " Days",array("FontSize"=>20,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE));
		else if ($direction == "out")
			$myPicture->drawText(350,45,"Breakdown of Expenditure - Last " . $days . " Days",array("FontSize"=>20,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE));

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
}
?>