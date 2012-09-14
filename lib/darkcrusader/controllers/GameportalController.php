<?php
/**
 * Game Portal HUD
 *
 * Copyright (c) 2012, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\controllers;

use darkcrusader\controllers\Controller;
use darkcrusader\models\UserModel;
use hydrogen\config\Config;
use hydrogen\view\View;

class GameportalController extends Controller {
	
	public function index() {
		$this->checkAuth(array(
			"access_chat",
			"access_private_chat"
		));

		$user = UserModel::getInstance()->getActiveUser();
		View::setVar("nickname", str_replace(" ", "", $user->username));

		View::load('game_portal', array(
			"channelString" => Config::getRequiredVal("chat", "private_channel") . "%20" . Config::getRequiredVal("chat", "private_channel_key"),
			"connectString" => "irc.rizon.net and join #" . Config::getRequiredVal("chat", "private_channel") . " with the key " . Config::getRequiredVal("chat", "private_channel_key")
		));
	}
}
?>