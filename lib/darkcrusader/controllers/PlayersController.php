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
use darkcrusader\models\IntelligenceModel;
use darkcrusader\models\FactionModel;
use darkcrusader\models\UserModel;

class PlayersController extends Controller {
	
	public function index() {
		$this->redirect("/index.php/stats");
	}
	
	public function player() {
		$this->checkAuth("access_player_statistics");

		$playerName = $_GET["name"];
		$user = UserModel::getInstance()->getActiveUser();

		$im = IntelligenceModel::getInstance();

		try {
			$player = PlayerModel::getInstance()->getPlayer($playerName);
		}
		catch (NoSuchPlayerException $e) {
			if ($this->checkAuth("add_players", false)) {
				$this->alert("info", "The player " . $playerName . " does not exist in our database. You can add them below");
				$this->redirect("/index.php/players/add?name=" . $playerName);
			}
			else {
				$this->alert("info", "The player " . $playerName . " does not exist in our database, please contact a SWAT/FIRE member to get them added");
				$this->redirect("/index.php/stats");
			}
		}

		if ($_POST["submit"]) {
			$im->addPlayerComment($user->id, $player->player_name, $_POST["classification"], $_POST["comment"]);
			$this->alert("success", "Comment added successfully");
		}

		if ($this->checkAuth("edit_players", false))
			View::setVar("canEditPlayers", "yes");

		View::load("players/player", array(
			"player" => $player,
			"comments" => $im->getPlayerComments($player->player_name, $user->clearance_level)
		));
	}

	public function add() {
		$this->checkAuth(array(
			"access_player_statistics",
			"add_players"
		));

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
				$this->alert("warning", "Player already exists, you can see their record below");
				$this->redirect("/index.php/players/player?name=" . $_POST["name"]);
			}

			$this->alert("success", "Player added successfully");
			$this->redirect("/index.php/players/player?name=" . $_POST["name"]);
		}
	}

	public function edit() {
		$this->checkAuth(array(
			"access_player_statistics",
			"edit_players"
		));

		if (!$_POST["submit"]) {
			try {
				$player = PlayerModel::getInstance()->getPlayer($_GET["name"]);
			}
			catch (NoSuchPlayerException $e) {
				$this->alert("info", "The player " . $playerName . " does not exist in our database. You can add them below");
				$this->redirect("/index.php/players/add?name=" . $_GET["name"]);
			}

			if ($this->checkAuth("edit_official_military_statuses", false))
				View::setVar("canEditOfficialMilitaryStatuses", "yes");

			View::load("players/edit", array(
				"player" => $player,
				"factions" => FactionModel::getInstance()->getFactionsCached(),
				"ranks" => PlayerModel::getInstance()->getRanks(),
				"statuses" => PlayerModel::getInstance()->getMilitaryStatusesCached()
			));
		}
		else {
			$user = UserModel::getInstance()->getActiveUser();

			PlayerModel::getInstance()->updatePlayer($_GET["name"], $_POST["rank"], $_POST["faction"], $_POST["official_status_id"], $user->id);

			$this->alert("success", "User updated successfully");
			$this->redirect("/index.php/players/player?name=" . $_GET["name"]);
		}
	}
}
?>