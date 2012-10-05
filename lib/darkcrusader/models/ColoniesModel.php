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
use darkcrusader\models\BlueprintsModel;

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
	 * @param string $name colony name
	 * @param int $user user id colony is owned by
	 * @param boolean $joins set to true to get all joins
	 * @return ColonyBean colony
	 */
	public function getColony($id=false, $name=false, $user=false, $joins=true) {
		$q = new Query("SELECT");

		if ($id)
			$q->where("colonies.id = ?", $id);

		if ($name)
			$q->where("name = ?", $name);

		if ($user)
			$q->where("user_id = ?", $user);

		$q->limit(1);

		$cbs = ColonyBean::select($q, $joins);
		return $cbs[0];
	}

	/**
	 * Gets a colony's status (idle, active, unknown)
	 * 
	 * @param int $id colony id
	 * @return string status, either idle, active or unknown
	 */
	public function getColonyStatus($id) {
		$colony = $this->getColony($id);
		$storedItems = StoredItemsModel::getInstance()->getStoredItemsInColony($id, $colony->user_id);

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
		$colony = $this->getColony($id);
		$storedItems = StoredItemsModel::getInstance()->getStoredItemsInColony($id, $colony->user_id);

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

	/**
	 * Calculates an optimal manufacturing route based on the input given
	 * 
	 * @param 
	 * @return array array of step by step instructions
	 */
	public function calculateOptimalManufacturingRoute($blueprintDescription, $coloniesToPickUpFrom, 
		$fuelCapacity, $fuelPerLightyear=2, $shipCargoCapacity, $startSystem, $manufacturingColonyName, $user) {

		// oh god this is going to be difficult
		$sim = StoredItemsModel::getInstance();

		// let's start by deciding how many we're going to make of the blueprint

		// first, find out how many we COULD make with the resources at our disposal
		$blueprintResources = BlueprintsModel::getInstance()->getBlueprintResources($blueprintDescription);

		// go through each resource and see how many we could make if it was only that resource which was required
		$amountWeCanMakeWithEachResource = array(); // resource name => amount we can make
		foreach($blueprintResources as $blueprintResource) {

			// find how much of the resource we have at our disposal
			$resourceOccurences = $sim->getOccurencesOfResource($user, $blueprintResource->resource_name);

			// we can only pick up from a colony/count the resource if it is in $coloniesToPickUpFrom or
			// is the manufacturing colony
			$totalResourcesAtDisposal = 0;
			foreach($resourceOccurences as $resourceOccurence) {
				if (($coloniesToPickUpFrom[$resourceOccurence->colony->name] == "yes") || ($resourceOccurence->colony->name == $manufacturingColonyName)) {
					$totalResourcesAtDisposal += $resourceOccurence->quantity;
				}
			}

			$amountWeCanMakeWithEachResource[$blueprintResource->resource_name] = (floor($totalResourcesAtDisposal / $blueprintResource->resource_quantity)); // divide amount we have by the amount needed for one item
		}

		// find out what resource limited us
		$amountWeCanMakeWithEachResourceFlipped = array_keys($amountWeCanMakeWithEachResource);
		ksort($amountWeCanMakeWithEachResourceFlipped);
		$handycapResource = reset($amountWeCanMakeWithEachResourceFlipped);

		// the lowest number is the maximum we can create (if we had unlimited storage space)
		sort($amountWeCanMakeWithEachResource);
		$maximumWeCanCreateWithResourcesAvailable = $amountWeCanMakeWithEachResource[0];

		// ok, now we have to bring storage into the equation, namely the storage of our manufacturing colony
		
		// first work out the storage required for one item
		$storageRequiredForOneItem = 0;
		foreach($blueprintResources as $blueprintResource) {
			$storageRequiredForOneItem += $blueprintResource->resource_quantity;
		}

		// now find out how much storage we have in our manufacturing facility
		$manufacturingColony = $this->getColony(false, $manufacturingColonyName, $user);
		$freeCapacity = $manufacturingColony->free_capacity;

		// we're now going to find out if any of our required resources are already at the facility,
		// if so, we can add the total amount back to our free storage
		$blueprintResourcesStrings = array(); // resources strings so we can use in_array()
		foreach($blueprintResources as $blueprintResource)
			$blueprintResourcesStrings[] = $blueprintResource->resource_name;

		$resourcesAtManufacturingColony = $manufacturingColony->resources;
		foreach($resourcesAtManufacturingColony as $resourceAtManufacturingColony) {
			if (in_array($resourceAtManufacturingColony->description, $blueprintResourcesStrings))
				$freeCapacity += $resourceAtManufacturingColony->quantity;
		}

		// now work out how many we can fit in our colony at one time
		$maximumWeCanCreateWithManufacturingColonyStorage = floor($freeCapacity / $storageRequiredForOneItem);

		// finally we need to bring the final limiting factor into the equation, ship storage

		// a lot of the work was done for us in the manufacturing colony storage check

		// we're going to find out if any of our required resources are already at the facility,
		// if so, we can add the total amount to our ship capacity since those resources don't need to be
		// moved
		foreach($resourcesAtManufacturingColony as $resourceAtManufacturingColony) {
			if (in_array($resourceAtManufacturingColony->description, $blueprintResourcesStrings))
				$shipCargoCapacity += $resourceAtManufacturingColony->quantity;
		}

		// now work out how many we can fit in our ship at one time
		$maximumWeCanCreateWithShipStorage = floor($shipCargoCapacity / $storageRequiredForOneItem);

		// our limit is whichever is lower (manufacturing colony storage, ship storage or resources available)
		$limits = array(
			"resource" => $maximumWeCanCreateWithResourcesAvailable,
			"shipStorage" => $maximumWeCanCreateWithShipStorage,
			"manufacturingColonyStorage" => $maximumWeCanCreateWithManufacturingColonyStorage
		);
		asort($limits);

		$itemsWeCanCreate = reset($limits);

		// find our handycap
		$limitsFlipped = array_keys($limits);
		$handycap = reset($limitsFlipped);

		// ok, now work out how much of each resource we need, then subtract the amount already at the manu
		// colony to find how much we need to collect
		$resourcesToCollect = array(); // resource name => amount to collect
		foreach($blueprintResources as $blueprintResource) {
			$resourcesToCollect[$blueprintResource->resource_name] = ($blueprintResource->resource_quantity * $itemsWeCanCreate);
		}

		foreach($resourcesAtManufacturingColony as $resourceAtManufacturingColony) {
			if ($resourcesToCollect[$resourceAtManufacturingColony->description] > 0)
				$resourcesToCollect[$resourceAtManufacturingColony->description] -= $resourceAtManufacturingColony->quantity;

			if ($resourcesToCollect[$resourceAtManufacturingColony->description] < 0)
				unset($resourcesToCollect[$resourceAtManufacturingColony->description]);
		}

		// work out all the possible places we could pick up from, for starters, limit it to only find
		// occurences with all the resources we need
		$resourceOccurencesOfWhatWeNeed = array(); // [key] => StoredItemBean
		foreach($resourcesToCollect as $resource => $quantity) {
			$resourceOccurences = $sim->getOccurencesOfResource($user, $resource, $quantity);

			// quickly check if any of the occurences are in our manu colony, if so, eliminate
			foreach($resourceOccurences as $id => $resourceOccurence) {
				if ($resourceOccurence->colony->name == $manufacturingColonyName)
					unset($resourceOccurences[$id]);
			}

			$resourceOccurencesOfWhatWeNeed = array_merge($resourceOccurencesOfWhatWeNeed, $resourceOccurences);
		}

		// if one of the resources is missing, run it again, this time without a limit for any resources
		// in question
		$resourcesChecklist = $resourcesToCollect;
		foreach($resourcesChecklist as $resource => $quantity) {
			foreach($resourceOccurencesOfWhatWeNeed as $resourceOccurenceOfWhatWeNeed) {
				if ($resourceOccurenceOfWhatWeNeed->description == $resource)
					unset($resourcesChecklist[$resource]);
			}
		}

		foreach($resourcesChecklist as $resource => $quantity) {
			$resourceOccurences = $sim->getOccurencesOfResource($user, $resource);

			// quickly check if any of the occurences are in our manu colony, if so, eliminate
			foreach($resourceOccurences as $id => $resourceOccurence) {
				if ($resourceOccurence->colony->name == $manufacturingColonyName)
					unset($resourceOccurences[$id]);
			}

			$resourceOccurencesOfWhatWeNeed = array_merge($resourceOccurencesOfWhatWeNeed, $resourceOccurences);
		}

		// now, the tricky part, we have to devise a route and instructions
		$instructions = array(); // [key] => instruction (friendly)

		// how to do it?
		// go to each colony we know where resources are until we have the amount required of each resource
		// then go to manu colony and drop off
		// of course the tricky bit is optimising fuel stops, which is going to eb a pain in the neck
		$sm = SystemModel::getInstance();

		$fuelCapacityInLightyears = floor($fuelCapacity / $fuelPerLightyear);
		$currentFuelInLightyears = $fuelCapacityInLightyears;

		$currentLocation = $sm->getSystem(false, $startSystem);

		// loop-edy-loop until we have everything done, at which point we'll break;
		// all the places we (might) need to visit are in $resourceOccurencesOfWhatWeNeed
		// and the total of each resource we need to collect is in $resourcesToCollect
		// our current location is always in $currentLocation
		// our current fuel in lightyears is always in $currentFuelInLightyears
		// our fuel capacity in lightyears is in $fuelCapacityInLightyears
		// our drop off point is $manufacturingColony
		// let's go!

		while(true) {

			// find the closest colony to our current location
			$resourceOccurencesSortedByDistance = array(); // distance in ly to current loc => resource occurence
			foreach($resourceOccurencesOfWhatWeNeed as $id => $resourceOccurence) {
				$distance = $sm->getDistanceBetweenSystems($currentLocation->id, $resourceOccurence->colony->system->id);

				$resourceOccurencesSortedByDistance[$distance] = $resourceOccurence;
				$resourceOccurencesSortedByDistance[$distance]->resourceId = $id;
			}
			ksort($resourceOccurencesSortedByDistance);

			$nextResourceOccurence = current($resourceOccurencesSortedByDistance);
			
			if ($nextResourceOccurence->id) {
				$nextSystem = $nextResourceOccurence->colony->system;

				if ($nextResourceOccurence->quantity >= $resourcesToCollect[$nextResourceOccurence->description])
					$amountToCollect = $resourcesToCollect[$nextResourceOccurence->description];
				else
					$amountToCollect = $nextResourceOccurence->quantity;

				// check if we can make it there and then back to a station
				$fuelInLightyearsAfterJump = $currentFuelInLightyears - $sm->getDistanceBetweenSystems($currentLocation->id, $nextSystem->id);

				$nearestStationSystemToNextSystem = $sm->getNearestStationSystemToSystem($nextSystem->id);
				$fuelNecessaryToGetToClosestStation = $sm->getDistanceBetweenSystems($nextSystem->id, $nearestStationSystemToNextSystem->id);

				if ($fuelInLightyearsAfterJump > $fuelNecessaryToGetToClosestStation) {
					$currentLocation = $nextSystem;
					$currentFuelInLightyears = $fuelInLightyearsAfterJump;
					$instructions[] = "Jump to " . $nextSystem->system_name . " and dock at " . $nextResourceOccurence->colony->location_string . ". Collect x" . $amountToCollect . " " . $nextResourceOccurence->description;
					unset($resourceOccurencesOfWhatWeNeed[$nextResourceOccurence->resourceId]);
				}
				else {
					// damn it! we can't make it, gotta find the nearest refuel spot to that system that we can reach
					$refuelSystem = $sm->getNearestStationSystemToSystemThatIsLessThanDistanceToSystem($nextSystem->id, $currentLocation->id, $currentFuelInLightyears);
					$currentLocation = $refuelSystem;
					$currentFuelInLightyears = $fuelCapacityInLightyears;
					$instructions[] = "Refuel at " . $refuelSystem->system_name;
				}
			}
			else {
				$nextSystem = $manufacturingColony->system;

				// check if we can make it there and then back to a station
				$fuelInLightyearsAfterJump = $currentFuelInLightyears - $sm->getDistanceBetweenSystems($currentLocation->id, $nextSystem->id);

				$nearestStationSystemToNextSystem = $sm->getNearestStationSystemToSystem($nextSystem->id);
				$fuelNecessaryToGetToClosestStation = $sm->getDistanceBetweenSystems($nextSystem->id, $nearestStationSystemToNextSystem->id, false, false, false, false, true);

				if ($fuelInLightyearsAfterJump > $fuelNecessaryToGetToClosestStation) {
					$currentLocation = $nextSystem;
					$currentFuelInLightyears = $fuelInLightyearsAfterJump;
					$instructions[] = "Jump to " . $nextSystem->system_name . " and dock at " . $manufacturingColony->location_string . ". Drop off all your resources and begin production";
					$instructions[] = "Jump back to the station system " . $nearestStationSystemToNextSystem->system_name;
					break;
				}
				else {
					// damn it! we can't make it, gotta find the nearest refuel spot to that system that we can reach
					$refuelSystem = $sm->getNearestStationSystemToSystemThatIsLessThanDistanceToSystem($nextSystem->id, $currentLocation->id, $currentFuelInLightyears);
					$currentLocation = $refuelSystem;
					$currentFuelInLightyears = $fuelCapacityInLightyears;
					$instructions[] = "Refuel at " . $refuelSystem->system_name;
				}
			}
		}

		$return = new \stdClass;
		$return->instructions = $instructions;
		$return->items = $itemsWeCanCreate;
		$return->blueprintDescription = $blueprintDescription;
		$return->handycap = $handycap;

		if ($handycap == "resource")
			$return->handycapResource = $handycapResource;

		return $return;
	}
}
?>