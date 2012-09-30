<?php
/**
 * Blueprints Model
 * Handles blueprints
 *
 * Copyright (c) 2012, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\models;

use hydrogen\model\Model;
use hydrogen\database\Query;

use darkcrusader\models\ColoniesModel;
use darkcrusader\models\OuterEmpiresModel;
use darkcrusader\models\StoredItemsModel;

use darkcrusader\sqlbeans\BlueprintResourceBean;

class BlueprintsModel extends Model {

	protected static $modelID = "bps";

	/**
	 * Gets the resources required for a blueprint (if known, otherwise BOOLEAN FALSE)
	 * 
	 * @param string $blueprintDescription blueprint description
	 * @return mixed array of BlueprintResourceBeans or boolean false if no resources known
	 */
	public function getBlueprintResources($blueprintDescription) {
		$q = new Query("SELECT");
		$q->where("blueprint_description = ?", $blueprintDescription);

		$brbs = BlueprintResourceBean::select($q);

		if (!$brbs[0])
			return false;

		return $brbs;
	}

	/**
	 * Adds a resource for a blueprint
	 *
	 * @param string $blueprintDescription blueprint description
	 * @param string $resourceName resource name
	 * @param int $resourceQuantity resource quantity
	 * @param int $submitter user id of the submitter of this info
	 */
	public function addBlueprintResource($blueprintDescription, $resourceName, $resourceQuantity, $submitter=false) {
		$brb = new BlueprintResourceBean;
		$brb->blueprint_description = $blueprintDescription;
		$brb->resource_name = $resourceName;
		$brb->resource_quantity = $resourceQuantity;

		if ($submitter)
			$brb->submitter_id = $submitter;

		$brb->insert();
	}
}