<?php
/**
 * Colony
 *
 * Copyright (c) 2012, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\colonies;

class Colony {

	public $name;
	public $location;
	public $population;
	public $maxPopulation;
	public $morale;
	public $power;
	public $freePower;
	public $size;
	public $freeSize;
	public $maxSize;
	public $dateEstablished; // this appears to be bugged, maybe it's dateLastVisited?
	public $storageCapacity;
	public $displayedSize;
}
?>