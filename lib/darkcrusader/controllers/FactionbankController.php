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

class FactionbankController extends Controller {
	
	public function index() {
		$this->checkAuth("access_bank");

		if ($this->checkAuth("administrate_bank", false))
			View::setVar("isBankAdmin", true);

		$bm = FactionBankModel::getInstance();
		$bm->generateDonorsGraph();

		View::load('bank/index', array(
			"bankBalance" => $bm->getCurrentBankBalanceCached(),
			"latestTransactions" => $bm->getLatestTransactionsCached(10)
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
			$transactionsAdded = FactionBankModel::getInstance()->parseTransactionLogPaste($_POST["paste"]);
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