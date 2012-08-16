<?php
/**
 * Faction Research Model
 * Handles faction research
 *
 * Copyright (c) 2012, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\models;

use hydrogen\model\Model;
use hydrogen\database\Query;

use darkcrusader\models\UserModel;
use darkcrusader\models\StoredItemsModel;

use darkcrusader\sqlbeans\FactionBlueprintBean;

use darkcrusader\user\exceptions\UserDoesNotHaveAConfiguredCharacterException;

class FactionResearchModel extends Model {
	
	protected static $modelID = "FactionBank";

	/**
	 * Gets all faction members' researched BPs and checks if there are any new ones to add
	 * 
	 * @param int $user optionally, a user id to update, if none is supplied all will be updated
	 */
	public function updateDB($user=false) {
		
		$sim = StoredItemsModel::getInstance();
		$um = UserModel::getInstance();

		if ($user) {
			$members = array();
			$members[0] = new \stdClass;
			$members[0]->id = $user;
		}
		else {
			$members = $um->getUsersInUserGroups(array(1,5,6,7,8));
		}

		$failed = 0;

		foreach($members as $member) {
			try {
				$sim->updateDB($member->id);
				$blueprints = $sim->getResearchedBlueprints($member->id);
			}
			catch (UserDoesNotHaveAConfiguredCharacterException $e) {
				$failed++;
			}

			foreach($blueprints as $blueprint) {
				$q = new Query("SELECT");
				$q->where("description = ?", str_replace("BLUEPRINT : ", "", $blueprint->description));
				$fbbs = FactionBlueprintBean::select($q);
				if (!$fbbs[0]) {
					$working = explode("BLUEPRINT : ", $blueprint->description);
					$working = explode(".", $working[1]);
					$researcherName = $working[0];

					$working = explode("MK", $blueprint->description);
					$working = explode(" ", $working[1]);
					$researchMark = (int) $working[0];

					$blueprintDescription = str_replace("BLUEPRINT : ", "", $blueprint->description);

					$this->addBlueprint($member->id, $blueprintDescription, $researcherName, $researchMark);
				}
			}
		}
	}

	/**
	 * Adds a blueprint to the database
	 * 
	 * @param int $submitter submitter id
	 * @param string $description blueprint description (without the 'BLUEPRINT : ' part)
	 * @param string $researcherName researcher name without spaces
	 * @param int $researchMark research mark, e.g. MK6 would be 6
	 */
	public function addBlueprint($submitter, $description, $researcherName, $researchMark) {

		$fbb = new FactionBlueprintBean;
		$fbb->submitter_id = $submitter;
		$fbb->description = $description;
		$fbb->researcher_player_name = $researcherName;
		$fbb->research_mark = $researchMark;
		$fbb->set("date_added", "NOW()", true);
		$fbb->insert();
	}

	/**
	 * Gets the latest blueprints added to the database
	 * 
	 * @param int $limit number of blueprints to get
	 * @return array array of FactionBlueprintBeans
	 */
	public function getLatestBlueprints($limit=10) {

		$q = new Query("SELECT");
		$q->orderby("date_added", "DESC");
		$q->limit($limit);

		return FactionBlueprintBean::select($q, true);
	}

	/**
	 * Gets all the researcher names we have in the database
	 * 
	 * @return array array of researcher names
	 */
	public function getResearcherNames() {

		$fbbs = FactionBlueprintBean::select();

		$researchers = array();
		foreach($fbbs as $fbb) {
			if (!in_array($fbb->researcher_player_name, $researchers))
				$researchers[] = $fbb->researcher_player_name;
		}

		sort($researchers);
		return $researchers;
	}

	/**
	 * Gets blueprints researched by a particular player
	 * 
	 * @param string $researcher researcher name, no spaces
	 * @return array array of FactionBlueprintBeans
	 */
	public function getBlueprintsResearchedBy($researcher) {
		$q = new Query("SELECT");
		$q->where("researcher_player_name = ?", $researcher);

		return FactionBlueprintBean::select($q, true);
	}
}
?>