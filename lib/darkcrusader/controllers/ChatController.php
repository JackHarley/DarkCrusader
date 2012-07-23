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
		$this->redirect("/index.php/chat/secret");
	}

	public function secret() {
		$this->checkAuth("access_private_chat");

		$user = UserModel::getInstance()->getActiveUser();
		
		View::load('chat', array(
			"nickname" => str_replace(" ", "", $user->username),
			"channel" => Config::getRequiredVal("chat", "private_channel"),
			"key" => Config::getRequiredVal("chat", "private_channel_key")
		));
	}
}
?>