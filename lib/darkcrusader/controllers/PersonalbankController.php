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
use darkcrusader\models\PersonalBankModel;
use darkcrusader\models\UserModel;
use hydrogen\view\View;

use darkcrusader\bank\exceptions\IncorrectTransactionLogPasteException;

class PersonalbankController extends Controller {
	
	public function index() {
		$this->checkAuth("access_personal_bank");
		$this->alert("info", "All transaction logs were cleared on 23 MAY 15:00 BST due to a bug with parsing, apologies, please repaste your logs");

		$bm = PersonalBankModel::getInstance();
		$user = UserModel::getInstance()->getActiveUser();

		if (!$bm->checkIfUserHasAtLeastOneTransaction($user->id)) {
			View::load('personal_bank/first_time');
			return;
		}

		View::load('personal_bank/index', array(
			"bankBalance" => $bm->getCurrentBankBalance($user->id),
			"latestTransactions" => $bm->getLatestTransactions($user->id, 10),
			"incomeGraph" => $bm->generateTransactionTypesGraph($user->id, "forever", "in"),
			"expenditureGraph" => $bm->generateTransactionTypesGraph($user->id, "forever", "out"),
			"richestMoment" => $bm->getRichestMoment($user->id)
		));
	}

	public function transactions() {
		$this->checkAuth("access_personal_bank");

		$user = UserModel::getInstance()->getActiveUser();

		View::load('personal_bank/transactions', array(
			"transactions" => PersonalBankModel::getInstance()->getLatestTransactions($user->id, 300)
		));
	}

	public function pastetransactionlog() {
		$this->checkAuth("access_personal_bank");

		if (!$this->checkFormInput("paste")) {
			View::load('personal_bank/paste_transaction_log');
			return;
		}

		try {
			$transactionsAdded = PersonalBankModel::getInstance()->parseTransactionLogPaste(UserModel::getInstance()->getActiveUser()->id, $_POST["paste"]);
		}
		catch (IncorrectTransactionLogPasteException $e) {
			$this->alert("error", "Transaction log parsing failed, ensure you're pasting it properly");
			View::load('personal_bank/paste_transaction_log');
			return;
		}

		$this->alert("success", $transactionsAdded . " transactions added to database successfully!");
		$this->index();
	}
}
?>