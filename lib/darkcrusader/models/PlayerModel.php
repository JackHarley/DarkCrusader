<?php
/**
 * Player Model
 * Handles data requests regarding the
 * player database
 *
 * Copyright (c) 2012, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\models;

use hydrogen\model\Model;

use hydrogen\database\Query;
use darkcrusader\sqlbeans\PlayerBean;
use darkcrusader\sqlbeans\MilitaryStatusBean;

use darkcrusader\models\UserModel;

use darkcrusader\players\exceptions\NoSuchPlayerException;
use darkcrusader\players\exceptions\PlayerAlreadyExistsException;

class PlayerModel extends Model {
	
	protected static $modelID = "player";
	
	/**
	 * Gets a player
	 * 
	 * @param string $playerName player name to get
	 * @return PlayerBean player
	 * @throws NoSuchPlayerException if no player by that name exists in our db
	 */
	public function getPlayer($playerName) {
		$q = new Query("SELECT");
		$q->where("player_name LIKE ?", "%" . $playerName . "%");

		$pbs = PlayerBean::select($q, true);

		if ($pbs[0])
			return $pbs[0];
		else
			throw new NoSuchPlayerException;

	}

	/**
	 * Adds a player to our database (basically initialise them so that data can be associated)
	 * Spelling and capitalization MUST be correct so as not to cause annoyances later on
	 * 
	 * @param string $playerName player name to add
	 * @throws PlayerAlreadyExistsException if player already exists
	 */
	public function addPlayer($playerName) {
		$q = new Query("SELECT");
		$q->where("player_name LIKE ?", $playerName);
		$pbs = PlayerBean::select($q);

		if ($pbs[0])
			throw new PlayerAlreadyExistsException;

		$q = new Query("SELECT");
		$q->where("name = ?", "Neutral");
		$msbs = MilitaryStatusBean::select($q);
		$neutral = $msbs[0];

		$player = new PlayerBean;
		$player->player_name = $playerName;
		$player->faction = "Unknown";
		$player->rank = "Unknown";
		$player->official_status_id = $neutral->id;
		$player->insert();
	}

	/**
	 * Updates player info in our database
	 * 
	 * @param string $playerName player name
	 * @param string $rank rank
	 * @param string $faction faction name
	 * @param int $officialStatus official status id
	 * @param int $user user id that's doing the updating
	 */
	public function updatePlayer($playerName, $rank, $faction, $officialStatus, $user) {
		$q = new Query("SELECT");
		$q->where("player_name = ?", $playerName);
		$pbs = PlayerBean::select($q, true);

		$player = $pbs[0];

		if ($rank)
			$player->rank = $rank;

		if ($faction)
			$player->faction = $faction;

		if ($officialStatus) {
			$user = UserModel::getInstance()->getUser($user);

			if ($user->permissions->hasPermission("edit_official_military_statuses"))
				$player->official_status_id = $officialStatus;
		}

		$player->update();
	}

	/**
	 * Gets the number of players we have on file
	 * 
	 * @return int number of players we have on file
	 */
	public function getNumberOfPlayersOnFile() {
		$pbs = PlayerBean::select($q);
		return count($pbs);
	}

	/**
	 * Gets an array of valid in game player ranks
	 * 
	 * @return array rank strings
	 */
	public function getRanks() {
		return array(
			"Academy Cadet",
			"Light Class A",
			"Light Class B",
			"Light Class C",
			"Medium Class A",
			"Medium Class B",
			"Medium Class C",
			"Heavy Class A",
			"Heavy Class B",
			"Heavy Class C",
			"Heavy Class D",
			"Heavy Class E",
			"Elite"
		);
	}

	/**
	 * Gets an array of valid military statuses
	 * 
	 * @return array military status strings
	 */
	public function getMilitaryStatuses__3600_militarystatuses() {
		return MilitaryStatusBean::select();
	}
}
?>