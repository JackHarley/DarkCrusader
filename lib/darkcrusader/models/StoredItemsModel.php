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

use darkcrusader\storeditems\StoredResource;

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
}
?>