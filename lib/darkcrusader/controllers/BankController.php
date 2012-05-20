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
use darkcrusader\models\BankModel;
use hydrogen\view\View;

use darkcrusader\bank\exceptions\IncorrectTransactionLogPasteException;

class BankController extends Controller {
	
	public function index() {
		$this->checkAuth("access_bank");

		if ($this->checkAuth("administrate_bank", false))
			View::setVar("isBankAdmin", true);

		$bm = BankModel::getInstance();
		$bm->generateDonorsGraph();
		
		View::load('bank/index', array(
			"bankBalance" => $bm->getCurrentBankBalance(),
			"latestTransactions" => $bm->getLatestTransactions(10)
		));
	}

	public function pastetransactionlog() {
		$this->checkAuth(array(
			"access_bank",
			"administrate_bank"
		));

		if (!$this->checkFormInput("paste")) {
			View::load('bank/paste_transaction_log');
			return;
		}

		try {
			$transactionsAdded = BankModel::getInstance()->parseTransactionLogPaste($_POST["paste"]);
		}
		catch (IncorrectTransactionLogPasteException $e) {
			$this->alert("error", "Incorrect transaction log pasted, ensure you're pasting the faction transaction log, not your own");
			View::load('bank/paste_transaction_log');
			return;
		}

		$this->alert("success", $transactionsAdded . " transactions added to database successfully!");
		$this->index();
	}
}
?>