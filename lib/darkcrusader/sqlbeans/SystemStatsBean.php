<?php
/**
 * SystemStats SQLBean
 *
 * Copyright (c) 2012, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\sqlbeans;
use hydrogen\sqlbeans\SQLBean;
use darkcrusader\models\SystemModel;

class SystemStatsBean extends SQLBean {

	protected static $tableNoPrefix = 'system_stats';
	protected static $tableAlias = 'system_stats';
	protected static $primaryKey = 'id';
	protected static $primaryKeyIsAutoIncrement = true;
	protected static $fields = array(
		'id',
		'faction',
		'has_station',
		'system_id',
		'stats_set',
		'hex_colour'
	);
	
	protected static $beanMap = array(
		'set' => array(
			'joinType' => 'LEFT',
			'joinBean' => 'darkcrusader\sqlbeans\SystemStatsSetBean',
			'foreignKey' => 'stats_set'
		),
		'system' => array(
			'joinType' => 'LEFT',
			'joinBean' => 'darkcrusader\sqlbeans\SystemBean',
			'foreignKey' => 'system_id'
		)
	);

	public $StoredSystem;
	public function get_system() {
		if (!$this->StoredSystem)
			return $this->getMapped('system');
		else
			return $this->StoredSystem;
	}

	public function set_system($val, $func=false) {
		$this->System = $val;
	}

	public function get_set() {
		return $this->getMapped('set');
	}
}
?>