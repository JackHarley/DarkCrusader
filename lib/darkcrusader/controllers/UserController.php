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

class UserController extends Controller {

	/**
	 * User Index
	 */
	public function index() {
		View::load("user/index");
	}

	/**
	 * Register
	 */
	public function register() {
		if (!isset($_POST["submit"])) {
			if (UserModel::getInstance()->userIsLoggedIn()) {
				$this->redirect();
			}
			
			View::load("user/register_form", array(
				"invite" => $_GET["invite"])
			);
		}
		else {
			try {
				UserModel::getInstance()->register($_POST["username"], $_POST["password"]);
			}
			catch (UsernameAlreadyRegisteredException $b) {
				View::load("user/register_form", array(
					"error" => "Error! Username already registered!")
				);
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
				View::load("user/login_form", array(
					"error" => "User does not exist"
				));
				return;
			}
			catch (PasswordIncorrectException $e) {
				View::load("user/login_form", array(
					"error" => "Password incorrect"
				));
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