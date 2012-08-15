<?php
/**
 * Intel Model
 * Handles intelligence
 *
 * Copyright (c) 2012, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\models;

use hydrogen\model\Model;
use hydrogen\config\Config;
use hydrogen\database\Query;

use darkcrusader\models\UserModel;

use darkcrusader\sqlbeans\IntelligenceBean;

class IntelligenceModel extends Model {

	protected static $modelID = "intel";

	/**
	 * Add a comment on a player
	 * 
	 * @param int $submitter id of user who is submitting this comment
	 * @param string $playerName player name this intel is about
	 * @param int $classification classification clearance level
	 * @param string $comment the comment
	 */
	public function addPlayerComment($submitter, $playerName, $classification=1, $comment) {

		$ib = new IntelligenceBean;
		$ib->type = "player_comment";
		$ib->player_name = $playerName;
		$ib->classification_level = $classification;
		$ib->comment = $comment;
		$ib->submitter_id = $submitter;
		$ib->set("date_added", "NOW()", true);
		$ib->insert();
	}

	/**
	 * Gets the comments on file for a player
	 * 
	 * @param string $playerName player name
	 * @param mixed $clearanceLevel the maximum clearance level info to retrive, or false to retrieve all
	 * @return array array of IntelligenceBeans
	 */
	public function getPlayerComments($playerName, $clearanceLevel=false) {
		$q = new Query("SELECT");
		$q->where("player_name = ?", $playerName);
		$q->where("type = ?", "player_comment");
		
		if ($clearanceLevel !== false)
			$q->where("classification_level <= ?", $clearanceLevel);

		$q->orderby("date_added", "DESC");

		return IntelligenceBean::select($q, true);
	}
}
?>