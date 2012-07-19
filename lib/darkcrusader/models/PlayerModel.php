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
		$q->where("player_name LIKE ?", $playerName);

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
		$player->insert();
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
}
?>