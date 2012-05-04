<?php
/**
 * KoS Model (Kill on Sight Model)
 * Handles data requests regarding the kill
 * on sight list
 *
 * Copyright (c) 2011, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\models;

use hydrogen\model\Model;

use hydrogen\database\Query;
use darkcrusader\sqlbeans\KoSBean;

use darkcrusader\models\UserModel;

class KoSModel extends Model{
	
	protected static $modelID = "KoS";
	
	public function getKoSList() {
		$query = new Query("SELECT");
		$KoSEntries = KoSBean::select($query);
		return $KoSEntries;
	}
	
	public function addKoSEntry($player, $reason) {
		$UserModel = UserModel::getInstance();
		$user = $UserModel->getLoggedInUser();
		
		$newEntry = new KoSBean;
		$newEntry->player_name = $player;
		$newEntry->reason = $reason;
		$newEntry->set("creation_time", "NOW()", true);
		$newEntry->creator_id = $user->id;
		$newEntry->insert();
		
		return true;
	}
	
	public function deleteKoSEntry($id) {
		$query = new Query("SELECT");
		$query->where("id = ?", $id);
		$entries = KosBean::select($query);
		$entries[0]->delete();
	}
}
?>