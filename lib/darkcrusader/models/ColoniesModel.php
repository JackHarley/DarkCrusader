<?php
/**
 * Colonies Model
 * Handles colonies
 *
 * Copyright (c) 2012, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\models;

use hydrogen\model\Model;
use hydrogen\config\Config;
use hydrogen\database\Query;

use darkcrusader\models\UserModel;
use darkcrusader\models\OuterEmpiresModel;
use darkcrusader\models\StoredItemsModel;

use darkcrusader\sqlbeans\ColonyBean;

class ColoniesModel extends Model {

	protected static $modelID = "colonies";

	/**
	 * Updates the database with a user's colonies
	 * 
	 * @param int $user user id
	 * @return array array of Colony's
	 */
	public function updateDB($user) {

		$colonies = OuterEmpiresModel::getInstance()->getColonies($user);

		foreach($colonies as $colony) {

			// add to/update db
			$this->addOrUpdateColony($user, $colony);
		}
	}

	/**
	 * Gets the colonies for a user and returns them
	 * 
	 * @param int $user user id
	 * @param string $primaryActivity colony primary activity or false to return all
	 * if you pass a blank string ("") it will return colonies that have not had their primary activity
	 * clasified
	 * @return array array of ColonyBeans
	 */
	public function getColonies($user, $primaryActivity=false) {
		$q = new Query("SELECT");
		$q->where("user_id = ?", $user);

		if ($primaryActivity !== false)
			$q->where("primary_activity = ?", $primaryActivity);

		$q->orderby("name", "ASC");

		return ColonyBean::select($q, true);
	}

	/**
	 * Classify a colony with a primary activity
	 * 
	 * @param int $user user id
	 * @param int $colony colony id
	 * @param string $primaryActivity primary activity to classify with
	 */
	public function classifyColony($user, $colony, $primaryActivity) {
		$cb = $this->getColony($colony);

		if ($cb->user_id != $user)
			die("Go away hacker");

		$cb->primary_activity = $primaryActivity;
		$cb->update();
	}

	/**
	 * Get a colony
	 * 
	 * @param int $id colony id
	 * @return ColonyBean colony
	 */
	public function getColony($id=false, $name=false) {
		$q = new Query("SELECT");

		if ($id)
			$q->where("colonies.id = ?", $id);

		if ($name)
			$q->where("name = ?", $name);

		$q->limit(1);

		$cbs = ColonyBean::select($q, true);
		return $cbs[0];
	}

	/**
	 * Gets a colony's status (idle, active, unknown)
	 * 
	 * @param int $id colony id
	 * @return string status, either idle, active or unknown
	 */
	public function getColonyStatus($id) {
		$storedItems = StoredItemsModel::getInstance()->getStoredItemsInColony($id);
		$colony = $this->getColony($id);

		switch ($colony->primary_activity) {
			case "manufacturing":
				foreach($storedItems as $storedItem) {
					if ($storedItem->description == "Item Production Resources")
						return "active";
				}
				return "idle";
			break;
			case "research":
				foreach($storedItems as $storedItem) {
					if ($storedItem->description == "Item Production Resources")
						return "active";
				}
				return "idle";
			break;
		}

		return "unknown";
	}

	/**
	 * Gets the free hangar capacity for a colony
	 * 
	 * @param int $id colony id
	 * @return int amount of space left in hangar
	 */
	public function getFreeCapacityInColony($id) {
		$storedItems = StoredItemsModel::getInstance()->getStoredItemsInColony($id);
		$colony = $this->getColony($id);

		$totalUsed = 0;
		foreach($storedItems as $storedItem) {
			$totalUsed += $storedItem->quantity;
		}

		return $colony->storage_capacity - $totalUsed;
	}

	/**
	 * Adds a colony to the database, or updates the existing entry if the colony is already
	 * in the database
	 * 
	 * @param int $user user id
	 * @param darkcrusader\colonies\Colony $colony colony
	 */
	public function addOrUpdateColony($user, $colony) {

		$planet = explode(" ", $colony->location);
		foreach($planet as $key => $val)
			if (!$val)
				unset($planet[$key]);

		// we're going to knock off the last term each time until we have only the system name left, and
		// the planet numeral, and if applicable moon number, stored
		$numberOfWords = count($planet);

		// check if last word has an M (this is a moon if it does)
		$lastWord = $planet[($numberOfWords - 1)];
		if (strpos($lastWord, "M") !== false) {
			$moonNumber = intval(str_replace("M", "", $lastWord));
			unset($planet[($numberOfWords - 1)]);
			$numberOfWords--;
		}

		// last word must now be the planet numeral
		$lastWord = $planet[($numberOfWords - 1)];
		$planetNumeral = $lastWord;
		unset($planet[($numberOfWords - 1)]);
		$numberOfWords--;

		// we're left with the system name
		$system = "";
		foreach($planet as $word) {
			$system .= $word . " ";
		}
		$system = trim($system);
		
		if (!$moonNumber)
			$moonNumber = 0;

		$q = new Query("SELECT");
		$q->where("name = ?", $colony->name);
		$q->where("user_id = ?", $user);
		$q->where("system_id = ?", SystemModel::getInstance()->getSystem(false, $system)->id);
		$q->where("planet_numeral = ?", $planetNumeral);
		$q->where("moon_number = ?", $moonNumber);

		$cbs = ColonyBean::select($q, true);

		$cb = ($cbs[0]) ? $cbs[0] : new ColonyBean;

		$cb->user_id = $user;
		$cb->name = $colony->name;
		$cb->system_id = SystemModel::getInstance()->getSystem(false, $system)->id;
		$cb->planet_numeral = $planetNumeral;
		$cb->moon_number = $moonNumber;
		$cb->population = $colony->population;
		$cb->max_population = $colony->maxPopulation;
		$cb->morale = $colony->morale;
		$cb->power = $colony->power;
		$cb->free_power = $colony->freePower;
		$cb->size = $colony->size;
		$cb->free_size = $colony->freeSize;
		$cb->max_size = $colony->maxSize;
		$cb->storage_capacity = $colony->storageCapacity;
		$cb->displayed_size = $colony->displayedSize;

		if ($cbs[0])
			$cb->update();
		else
			$cb->insert();
	}
}
?>