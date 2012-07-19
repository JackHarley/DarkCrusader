<?php
/**
 * Players Controller
 * Controls the player stats
 *
 * Copyright (c) 2012, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\controllers;

use darkcrusader\controllers\Controller;
use hydrogen\view\View;

use darkcrusader\players\exceptions\NoSuchPlayerException;
use darkcrusader\players\exceptions\PlayerAlreadyExistsException;

use darkcrusader\models\PlayerModel;

class PlayersController extends Controller {
	
	public function index() {
		$this->redirect("/index.php/stats");
	}
	
	public function player() {
		$this->checkAuth("access_site");

		$playerName = $_GET["name"];

		try {
			$player = PlayerModel::getInstance()->getPlayer($playerName);
		}
		catch (NoSuchPlayerException $e) {
			$this->redirect("/index.php/players/new?name=" . $playerName);
		}

		View::load("players/player", array(
			"player" => $player
		));
	}

	public function add() {
		if (!$_POST["submit"]) {
			if ($_GET["name"])
				View::setVar("playerName", $_GET["name"]);

			View::load("players/add");
		}
		else {

			try {
				PlayerModel::getInstance()->addPlayer($_POST["name"]);
			}
			catch (PlayerAlreadyExistsException $e) {
				$this->alert("error", "Player already exists!");
				$this->redirect("/index.php/stats");
			}

			$this->alert("success", "Player added successfully, you can now look them up");
			$this->redirect("/index.php/stats");
		}
	}
}
?>