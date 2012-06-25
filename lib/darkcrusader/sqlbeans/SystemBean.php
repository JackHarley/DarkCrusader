<?php
/**
 * System SQLBean
 *
 * Copyright (c) 2012, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\sqlbeans;
use hydrogen\sqlbeans\SQLBean;

use darkcrusader\models\SystemModel;
use darkcrusader\models\ScanModel;

class SystemBean extends SQLBean {

	protected static $tableNoPrefix = 'systems';
	protected static $tableAlias = 'systems';
	protected static $primaryKey = 'id';
	protected static $primaryKeyIsAutoIncrement = true;
	protected static $fields = array(
		'id',
		'system_name',
		'oe_star_id',
		'quadrant',
		'sector',
		'region',
		'locality',
		'x',
		'y'
	);
	
	public $objects_scanned = 0;
	public $objects_scanned_by_user = 0;

	protected static $beanMap = array(
	);

	public $Stats;
	public function get_stats() {
		if (!$this->Stats)
			$this->Stats = SystemModel::getInstance()->getSystemStats($this->id);

		return $this->Stats;
	}

	public function get_location() {
		return $this->quadrant . ":" . $this->sector . ":" . $this->region . ":" . $this->locality;
	}

	public $Scans;
	public function get_scans() {
		if (!$this->Scans)
			$this->Scans = ScanModel::getInstance()->getScansForSystem($this->id);

		if (!is_array($this->Scans))
			$this->Scans = array();

		return $this->Scans;
	}
}
?>