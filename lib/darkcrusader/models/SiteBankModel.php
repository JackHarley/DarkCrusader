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
use hydrogen\config\Config;
use hydrogen\database\Query;
use hydrogen\recache\RECacheManager;

use darkcrusader\models\UserModel;
use darkcrusader\models\OuterEmpiresModel;
use darkcrusader\sqlbeans\SiteBankTransferBean;
use darkcrusader\sqlbeans\LinkedCharacterBean;
use darkcrusader\sqlbeans\CharacterLinkRequestBean;

class SiteBankModel extends Model {
	
	protected static $modelID = "SiteBank";

	/**
	 * Updates the DB with any new transfers from OE
	 */
	public function updateDB() {
		
		// work out how many days we need to get to ensure we update the db fully
		$q = new Query("SELECT");
		$q->orderby("date", "DESC");
		$q->limit(1);

		$sbtbs = SiteBankTransferBean::select($q);

		$latestTransferKnown = $sbtbs[0];

		if (!$latestTransferKnown) {
			$daysToGet = 0; // fetch all (paul will be mad though)
		}
		else {
			$timeNow = time();
			$timeThen = strtotime($latestTransferKnown->date);

			$secondsSinceUpdate = $timeNow - $timeThen;

			$daysSinceUpdateApprox = round($secondsSinceUpdate / (3600 * 24));

			$daysToGet = $daysSinceUpdateApprox + 1; // add 1 for good luck
		}

		$siteBankAccessKey = Config::getRequiredVal("general", "site_bank_api_access_key");

		$transactions = OuterEmpiresModel::getInstance()->getPlayerBankTransactions(false, $daysToGet, true, $siteBankAccessKey);

		foreach($transactions as $transaction) {

			// we're only interested in transfers in
			if ($transaction->type != "Transfer")
				continue;
			if ($transaction->direction != "in")
				continue;

			// check if already in db
			$q = new Query("SELECT");
			$q->where("date = ?", $transaction->date);
			$q->where("character_name = ?", $transaction->characterName);
			$q->where("amount = ?", $transaction->amount);
			$sbtbs = SiteBankTransferBean::select($q);
			if ($sbtbs[0])
				continue;

			// insert
			$sbtb = new SiteBankTransferBean;
			$sbtb->character_name = $transaction->characterName;
			$sbtb->date = $transaction->date;
			$sbtb->amount = $transaction->amount;
			$sbtb->processed = 0;
			$sbtb->insert();
		}

	}

	/**
	 * Attempt to process any unprocessed transfers
	 * (1) check if there are any link requests matching any transfers, and approve them
	 * (2) update any user account balances
	 */
	public function processAnyUnprocessedTransfers() {
		$um = UserModel::getInstance();

		$q = new Query("SELECT");
		$q->where("processed = ?", 0);
		$sbtbs = SiteBankTransferBean::select($q);

		foreach($sbtbs as $sbtb) {
			// check for a link request
			$q = new Query("SELECT");
			$q->where("verification_amount = ?", $sbtb->amount);
			$q->where("character_name = ?", $sbtb->character_name);
			$clrbs = CharacterLinkRequestBean::select($q);
			$clrb = $clrbs[0];

			if ($clrb)
				$um->approveCharacterLinkRequest($clrb->id);

			// check for linked user and add funds
			$q = new Query("SELECT");
			$q->where("character_name = ?", $sbtb->character_name);
			$lcbs = LinkedCharacterBean::select($q, true);
			$lcb = $lcbs[0];

			if ($lcb) {
				$user = $lcb->getMapped("user");
				$user->balance += $sbtb->amount;
				$user->update();

				// now fully processed
				$sbtb->processed = 1;
				$sbtb->update();
			}

		}
	}


}
?>