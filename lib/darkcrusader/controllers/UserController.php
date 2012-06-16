<?php
/**
 * Project Dark Crusader
 * Copyright (c) 2012, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\controllers;

use hydrogen\view\View;
use hydrogen\config\Config;

use darkcrusader\controllers\Controller;
use darkcrusader\models\UserModel;

use darkcrusader\user\exceptions\UsernameAlreadyRegisteredException;
use darkcrusader\user\exceptions\EmailAlreadyRegisteredException;
use darkcrusader\user\exceptions\InviteKeyInvalidException;
use darkcrusader\user\exceptions\NoSuchUserException;
use darkcrusader\user\exceptions\PasswordIncorrectException;
use darkcrusader\user\exceptions\CannotSetCharacterAsDefaultWithoutAPIKeyException;
use darkcrusader\user\exceptions\CharacterIsAlreadyLinkedException;
use darkcrusader\user\exceptions\UserDoesNotHaveSufficientFundsException;

use darkcrusader\exceptions\FormIncorrectlyFilledOutException;

use darkcrusader\oe\exceptions\APIKeyInvalidException;

class UserController extends Controller {

	/**
	 * User Index
	 */
	public function index() {
		$um = UserModel::getInstance();

		$user = $um->getActiveUser();

		// if we have a submitted form, they've just purchased premium
		if ($_POST["submit"]) {
			try {
				$um->subscribeUserToPremium($user->id, $_POST["duration"]);
			}
			catch (UserDoesNotHaveSufficientFundsException $e) {
				$this->alert('error', "Sorry, you do not have sufficient credits in your site bank account to do this. Please see the FAQ for information on adding credits to your site bank account.");
			}
			catch (FormIncorrectlyFilledOutException $e) {
				$this->alert("error", "You did not choose a valid option, please try again");
			}
		}

		$this->initializeViewVariables();

		View::load("user/index", array(
			"linkedCharacters" => $um->getLinkedCharacters($user->id)
		));
	}

	/**
	 * OE Character Integration
	 */
	public function characters() {
		$um = UserModel::getInstance();
		$user = $um->getActiveUser();

		if (isset($_POST["submit"])) {
			try {
				$um->requestCharacterLink($user->id, $_POST["character_name"], $_POST["api_key"]);
			}
			catch (CharacterIsAlreadyLinkedException $e) {
				$this->alert("error", "That character has already been added to an account");
			}
			catch (APIKeyInvalidException $e) {
				$this->alert("error", "The API key you provided was invalid or is not for the character you specified. Check you spelled the character's name correctly and that the API key is correct and then try again");
			}
		}

		switch ($_GET["act"]) {
			case "default":
				try {
					$um->setDefaultCharacter($_GET["id"]);
				}
				catch (CannotSetCharacterAsDefaultWithoutAPIKeyException $e) {
					$this->alert("error", "You cannot set a character as default if it does not have an API key associated with it. Please delete the character and then add it again, this time with your API key");
				}
			break;
			case "deletecharacter":
				$um->deleteLinkedCharacter($_GET["id"]);
			break;
			case "deleterequest":
				$um->deleteCharacterLinkRequest($_GET["id"]);
			break;
		}

		if (($_GET["act"]) || ($_GET["id"]))
			$this->redirect("/index.php/user/characters");

		$characters = $um->getLinkedCharacters($user->id);
		$requests = $um->getCharacterLinkRequests($user->id);

		View::load("user/characters", array(
			"linkedCharacters" => $characters,
			"linkRequests" => $requests
		));
	}

	/**
	 * Register
	 */
	public function register() {
		if (!isset($_POST["submit"])) {
			if (UserModel::getInstance()->userIsLoggedIn()) {
				$this->redirect();
			}
			View::load("user/register_form");
		}
		else {
			try {
				UserModel::getInstance()->register($_POST["username"], $_POST["password"]);
			}
			catch (UsernameAlreadyRegisteredException $b) {
				$this->alert("error", "Username already registered, please use a different username");
				View::load("user/register_form");
				return;
			}

			View::load("user/register_success");
		}
	}

	/**
	 * Login
	 */
	public function login() {
		if (!$_POST["submit"]) {
			if (UserModel::getInstance()->userIsLoggedIn())
				$this->redirect();
			
			View::load("user/login_form");
		}
		else {

			$um = UserModel::getInstance();
			
			try {
				$um->login($_POST["username"], $_POST["password"]);
			}
			catch (NoSuchUserException $e) {
				$this->alert("error", "No such username, ensure you are spelling it correctly");
				View::load("user/login_form");
				return;
			}
			catch (PasswordIncorrectException $e) {
				$this->alert("error", "Password incorrect, please try again");
				View::load("user/login_form");
				return;
			}

			if (isset($_POST["remember_me"]))
				$um->createAutologin();

			$this->redirect();
		}
	}

	/**
	 * Logout
	 */
	public function logout() {
		UserModel::getInstance()->logout();
		$this->redirect();
	}

}
?>