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
		$this->checkAuth("test_beta_features");
		$this->checkForValidCharacterAndAPIKey();

		$bm = PersonalBankModel::getInstance();
		$um = UserModel::getInstance();
		$user = $um->getActiveUser();

		View::load('personal_bank/index', array(
			"character" => $um->getDefaultCharacter($user->id)->character_name,
			"bankBalance" => $bm->getCurrentBankBalance($user->id),
			"latestTransactions" => $bm->getLatestTransactions($user->id, 30, 10),
			"incomeGraph" => $bm->generateTransactionTypesGraph($user->id, 30, "in"),
			"expenditureGraph" => $bm->generateTransactionTypesGraph($user->id, 30, "out"),
			//"richestMoment" => $bm->getRichestMoment($user->id)
		));
	}

	public function transactions() {
		$this->checkAuth("access_personal_bank");
		$this->checkAuth("test_beta_features");
		$this->checkForValidCharacterAndAPIKey();
		
		$user = UserModel::getInstance()->getActiveUser();

		View::load('personal_bank/transactions', array(
			"transactions" => PersonalBankModel::getInstance()->getLatestTransactions($user->id, 7, 300)
		));
	}
}
?>