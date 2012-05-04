<?php
/**
 * System SQLBean
 *
 * Copyright (c) 2011, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\sqlbeans;
use hydrogen\sqlbeans\SQLBean;

use darkcrusader\models\SystemModel;

class SystemBean extends SQLBean {

	protected static $tableNoPrefix = 'systems';
	protected static $tableAlias = 'systems';
	protected static $primaryKey = 'id';
	protected static $primaryKeyIsAutoIncrement = true;
	protected static $fields = array(
		'id',
		'system_name',
		'quadrant',
		'sector',
		'region',
		'locality'
	);
	
	protected static $beanMap = array(
	);

	protected $stats;
	public function get_stats() {
		if (!$this->stats)
			$this->stats = SystemModel::getInstance()->getSystemStats($this->id);

		return $this->stats;
	}

	public function get_location() {
		return $this->quadrant . ":" . $this->sector . ":" . $this->region . ":" . $this->locality;
	}
}
?>