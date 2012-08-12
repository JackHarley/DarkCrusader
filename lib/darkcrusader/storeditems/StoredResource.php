<?php
/**
 * Stored Resource
 *
 * Copyright (c) 2012, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\storeditems;

class StoredResource {

	public $name; // name of resource
	public $locations; // array of locations (strings), e.g. ['AGal1', 'Aurlinfinn I Station']
	public $totalQuantity; // total quantity of resource available to user across galaxy

	public function __construct() {
		$this->locations = array();
		$this->totalQuantity = 0;
	}
}
?>