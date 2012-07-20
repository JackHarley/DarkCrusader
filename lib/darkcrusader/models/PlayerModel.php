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

		$pbs = PlayerBean::select($q);

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

		$player = new PlayerBean;
		$player->player_name = $playerName;
		$player->faction = "Unknown";
		$player->rank = "Unknown";
		$player->official_status = "Neutral";
		$player->insert();
	}

	/**
	 * Updates player info in our database
	 * 
	 * @param string $playerName player name
	 * @param string $rank rank
	 * @param string $faction faction name
	 * @param string $officialStatus official status
	 * @param int $user user id that's doing the updating
	 */
	public function updatePlayer($playerName, $rank, $faction, $officialStatus, $user) {
		$q = new Query("SELECT");
		$q->where("player_name = ?", $playerName);
		$pbs = PlayerBean::select($q);

		$player = $pbs[0];

		if ($rank)
			$player->rank = $rank;

		if ($faction)
			$player->faction = $faction;

		if ($officialStatus)
			$player->official_status = $officialStatus;

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

	public function getMilitaryStatuses() {
		return array(
			"Allied",
			"Neutral",
			"Kill Player on Sight",
			"Kill Player + Colonies on Sight",
			"Negotiated War",
			"All Out War"
		);
	}
}
?>