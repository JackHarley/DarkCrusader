<?php
/**
 * Bank Controller
 * Controls the bank section
 *
 * Copyright (c) 2012, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\controllers;

use darkcrusader\controllers\Controller;
use darkcrusader\models\FactionBankModel;
use hydrogen\view\View;

use darkcrusader\bank\exceptions\IncorrectTransactionLogPasteException;
use darkcrusader\bank\exceptions\NoFactionBankTransactionsAddedException;

class FactionbankController extends Controller {
	
	public function index() {
		$this->checkAuth("access_faction_bank");

		if ($this->checkAuth("administrate_faction_bank", false))
			View::setVar("isBankAdmin", true);

		$bm = FactionBankModel::getInstance();
		try {
			$bm->generateDonorsGraph();
			$balance = $bm->getCurrentBankBalance();
			$latestTransactions = $bm->getLatestTransactions(10);
		}
		catch (NoFactionBankTransactionsAddedException $e) {
			$this->pastetransactionlog();
			return;
		}

		View::load('faction_bank/index', array(
			"bankBalance" => $balance,
			"latestTransactions" => $latestTransactions
		));
	}

	public function pastetransactionlog() {
		$this->checkAuth(array(
			"access_faction_bank",
			"administrate_faction_bank"
		));

		if (!$this->checkFormInput("paste")) {
			View::load('faction_bank/paste_transaction_log');
			return;
		}

		try {
			$transactionsAdded = FactionBankModel::getInstance()->parseTransactionLogPaste($_POST["paste"]);
		}
		catch (IncorrectTransactionLogPasteException $e) {
			$this->alert("error", "Incorrect transaction log pasted, ensure you're pasting the faction transaction log, not your own");
			View::load('faction_bank/paste_transaction_log');
			return;
		}

		$this->alert("success", $transactionsAdded . " transactions added to database successfully!");
		$this->index();
	}
}
?>