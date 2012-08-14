<?php
/**
 * Stored Item
 *
 * Copyright (c) 2012, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\storeditems;

class StoredItem {

	public $description; // description of item
	public $type; // type: 'resource', 'scan', 'hull', 'blueprint', 'cannisters'
	public $quantity; // quantity of item at this location
	public $oeId; // oe item id, not sure if it has any use
	public $location; // location name (colony name: 'TitG10' or full station name: 'Aurlinfinn I Station')
}
?>