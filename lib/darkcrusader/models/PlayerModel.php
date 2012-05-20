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
use darkcrusader\sqlbeans\PlayerStatsSetBean;
use darkcrusader\sqlbeans\PlayerXPStatsBean;
use darkcrusader\sqlbeans\PlayerCreditStatsBean;
use darkcrusader\sqlbeans\PlayerBountyStatsBean;
use darkcrusader\sqlbeans\PlayerEmpireStatsBean;

use darkcrusader\models\UserModel;

class PlayerModel extends Model {
	
	protected static $modelID = "player";
	
	public function getPlayerCurrentStats($playerName, $like=false) {
		$query = new Query("SELECT");
		$query->orderby("time", "DESC");
		$query->limit(1);
		
		$statsSet = PlayerStatsSetBean::select($query);
		
		if ($like)
			$playerName = '%' . $playerName . '%';
			
		$query = new Query("SELECT");
		$query->where("stats_set = ?", $statsSet[0]->id);
		if ($like)
			$query->where("player_name LIKE ?", $playerName);
		else
			$query->where("player_name = ?", $playerName);
		$playerXP = PlayerXPStatsBean::select($query);
		
		
		$query = new Query("SELECT");
		$query->where("stats_set = ?", $statsSet[0]->id);
		if ($like)
			$query->where("player_name LIKE ?", $playerName);
		else
			$query->where("player_name = ?", $playerName);
		$playerCredit = PlayerCreditStatsBean::select($query);
		
		$query = new Query("SELECT");
		$query->where("stats_set = ?", $statsSet[0]->id);
		if ($like)
			$query->where("player_name LIKE ?", $playerName);
		else
			$query->where("player_name = ?", $playerName);
		$playerEmpire = PlayerEmpireStatsBean::select($query);
		
		$query = new Query("SELECT");
		$query->where("stats_set = ?", $statsSet[0]->id);
		if ($like)
			$query->where("player_name LIKE ?", $playerName);
		else
			$query->where("player_name = ?", $playerName);
		$playerBounty = PlayerBountyStatsBean::select($query);
		
		if ($playerXP[0]->total_xp) {
			$query = new Query("SELECT");
			if ($like)
				$query->where("player_name LIKE ?", $playerName);
			else
				$query->where("player_name = ?", $playerName);
			$query->where("total_xp != ?", $playerXP[0]->total_xp);
			$query->orderby("stats_set", "DESC");
			$query->limit(1);
			$latestXPChange = PlayerXPStatsBean::select($query, true);
			if ($latestXPChange)
				$latestXPChangeStatsSet = $latestXPChange[0]->getMapped("set");
		}
		
		$return = array(
			"player_name" => $playerXP[0]->player_name,
			"current_rank" => $playerXP[0]->rank,
			"total_xp" => number_format($playerXP[0]->total_xp),
			"leaderboard_position_xp" => $playerXP[0]->leaderboard_position,
			"latest_xp_change" => $latestXPChangeStatsSet->time,
			
			"leaderboard_position_credit" => $playerCredit[0]->leaderboard_position,
			"credits" => number_format($playerCredit[0]->credits),
			
			"leaderboard_position_empire" => $playerEmpire[0]->leaderboard_position,
			"colonies" => $playerEmpire[0]->colonies,
			"population" => $playerEmpire[0]->population,
			
			"leaderboard_position_bounty" => $playerBounty[0]->leaderboard_position,
			"bounty" => $playerBounty[0]->bounty);
		
		return $return;
	}
}