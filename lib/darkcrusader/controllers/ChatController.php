<?php
/**
 * IRC Chat Controller
 *
 * Copyright (c) 2012, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\controllers;

use darkcrusader\controllers\Controller;
use hydrogen\view\View;

use hydrogen\config\Config;

use darkcrusader\models\UserModel;

class ChatController extends Controller {

	public function index() {
		$this->checkAuth("access_chat");

		$user = UserModel::getInstance()->getActiveUser();
		if ($user->username)
			View::setVar("nickname", str_replace(" ", "", $user->username));

		if ($this->checkAuth("access_private_chat", false)) {
			View::load('chat', array(
				"channelString" => Config::getRequiredVal("chat", "private_channel") . "%2C" . Config::getRequiredVal("chat", "public_channel") . "%20" . Config::getRequiredVal("chat", "private_channel_key"),
				"connectString" => "irc.rizon.net and join #" . Config::getRequiredVal("chat", "public_channel") . " for public chat and #" . Config::getRequiredVal("chat", "private_channel") . " with the key " . Config::getRequiredVal("chat", "private_channel_key") . " for private SWAT/FIRE chat"
			));
			return;
		}
		
		View::load('chat', array(
			"channelString" => Config::getRequiredVal("chat", "public_channel"),
			"connectString" => "irc.rizon.net and join #" . Config::getRequiredVal("chat", "public_channel")
		));
	}
}
?>