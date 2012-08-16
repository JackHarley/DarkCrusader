<?php
/**
 * Stored Items Model
 * Handles stored items (inventory)
 *
 * Copyright (c) 2012, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\models;

use hydrogen\model\Model;
use hydrogen\config\Config;
use hydrogen\database\Query;
use hydrogen\recache\RECacheManager;

use darkcrusader\models\UserModel;
use darkcrusader\models\OuterEmpiresModel;
use darkcrusader\models\ColoniesModel;
use darkcrusader\models\SystemModel;

use darkcrusader\user\exceptions\UserDoesNotHaveAConfiguredCharacterException;

use darkcrusader\storeditems\StoredResource;

use darkcrusader\sqlbeans\StoredItemBean;

class StoredItemsModel extends Model {

	protected static $modelID = "storeditems";

	/**
	 * Gets the stored resources for a user and returns an array of
	 * StoredResources
	 * 
	 * @param int $user user id
	 * @return array array of StoredResources
	 */
	public function getStoredResources($user) {
		$storedItems = OuterEmpiresModel::getInstance()->getStoredItems($user);

		$resources = array();

		foreach($storedItems as $storedItem) {

			// not a resource? we don't want it!
			if ($storedItem->type != "resource")
				continue;

			// check if we have a resource entry already, if not create one
			if (!$resources[$storedItem->description]) {
				$resources[$storedItem->description] = new StoredResource;
				$resources[$storedItem->description]->name = $storedItem->description;
			}
			
			// add the location and increment the quantity
			$resources[$storedItem->description]->locations[] = $storedItem->location;
			$resources[$storedItem->description]->totalQuantity += $storedItem->quantity;

		}

		// sort the resources alphabetically and return
		ksort($resources);
		return $resources;
	}

	/**
	 * Updates the database with a user's stored items
	 * 
	 * @param int $user user id
	 */
	public function updateDB($user) {

		$defaultCharacter = UserModel::getInstance()->getDefaultCharacter($user);
		
		if (!$defaultCharacter->api_key)
			throw new UserDoesNotHaveAConfiguredCharacterException;

		$storedItems = OuterEmpiresModel::getInstance()->getStoredItems(false, $defaultCharacter->api_key);

		// clear db
		$this->clearStoredItems($user);

		foreach($storedItems as $storedItem) 
			$this->addStoredItem($user, $storedItem);
	}

	/**
	 * Clears the stored items for a user
	 * 
	 * @param int $user user id
	 */
	public function clearStoredItems($user) {
		$q = new Query("DELETE");
		$q->from("stored_items");
		$q->where("user_id = ?", $user);
		$q->prepare()->execute();
	}

	/**
	 * Gets the stored items in a colony
	 * 
	 * @param int $colony colony id
	 * @return array array of StoredItemBeans
	 */
	public function getStoredItemsInColony($colony) {
		$q = new Query("SELECT");
		$q->where("colony_id = ?", $colony);
		$q->where("location_type = ?", "colony");

		$sibs = StoredItemBean::select($q, true);
		return $sibs;
	}

	/**
	 * Gets the stored resources in a colony
	 * 
	 * @param int $colony colony id
	 * @param bool $includeFoodAndWater set to false to not include Food and Water
	 * @param bool $includeWorkers set to false to not include Workers
	 * @return array array of StoredItemBeans
	 */
	public function getStoredResourcesInColony($colony, $includeFoodAndWater=true, $includeWorkers=true) {
		$q = new Query("SELECT");
		$q->where("colony_id = ?", $colony);
		$q->where("location_type = ?", "colony");
		$q->where("type = ?", "resource");
		if (!$includeFoodAndWater) {
			$q->where("stored_items.description != ?", "Food");
			$q->where("stored_items.description != ?", "Water");
		}
		if (!$includeWorkers)
			$q->where("stored_items.description != ?", "Workers");

		$sibs = StoredItemBean::select($q, true);
		return $sibs;
	}

	/**
	 * Adds the record of a stored item
	 * 
	 * @param int $user user id
	 * @param StoredItem stored item
	 */
	public function addStoredItem($user, $storedItem) {

		$sib = new StoredItemBean;

		$sib->description = $storedItem->description;
		$sib->type = $storedItem->type;
		$sib->quantity = $storedItem->quantity;
		$sib->user_id = $user;

		if (strpos($storedItem->location, "Station") !== false) {
			$sib->location_type = "station";

			$planet = str_replace(" Station", "", $storedItem->location);
			$planet = explode(" ", $planet);

			// we're going to knock off the last term each time until we have only the system name left, and
			// the planet numeral, and if applicable moon number, stored
			$numberOfWords = count($planet);

			// last word must be the planet numeral
			$lastWord = $planet[($numberOfWords - 1)];
			$planetid = $lastWord;
			unset($planet[($numberOfWords - 1)]);
			$numberOfWords--;

			// we're left with the system name
			$system = "";
			foreach($planet as $word) {
				$system .= $word . " ";
			}
			$system = trim($system);

			$sib->system_id = SystemModel::getInstance()->getSystem(false, $system)->id;
			$sib->planet_numeral = $planetid;
		}
		else {
			$colony = ColoniesModel::getInstance()->getColony(false, $storedItem->location);

			if ($colony->id) {
				$sib->location_type = "colony";
				$sib->colony_id = $colony->id;
			}
			else {
				$sib->location_type = "ship";
				$sib->ship_name = $storedItem->location;
			}

		}

		$sib->insert();

	}

	/**
	 * Gets all researched blueprints which were researched by the user in question and stored
	 * in their facilities
	 * 
	 * @param int $user user id
	 * @return array array of StoredItemBeans
	 */
	public function getResearchedBlueprints($user) {
		
		$defaultCharacter = UserModel::getInstance()->getDefaultCharacter($user);
		
		if (!$defaultCharacter->api_key)
			throw new UserDoesNotHaveAConfiguredCharacterException;
		
		$characterNoSpaces = str_replace(" ", "", $defaultCharacter->character_name);

		$q = new Query("SELECT");
		$q->where("type = ?", "blueprint");
		$q->where("stored_items.description LIKE ?", "%" . $characterNoSpaces . "%");
		$q->where("stored_items.user_id = ?", $user);

		$sibs = StoredItemBean::select($q, true);
		return $sibs;

	}
}
?>