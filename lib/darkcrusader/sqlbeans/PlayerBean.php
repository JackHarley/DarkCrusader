<?php
/**
 * Player SQLBean
 *
 * Copyright (c) 2012, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\sqlbeans;
use hydrogen\sqlbeans\SQLBean;

class PlayerBean extends SQLBean {

	protected static $tableNoPrefix = 'players';
	protected static $tableAlias = 'players';
	protected static $primaryKey = 'id';
	protected static $primaryKeyIsAutoIncrement = true;
	protected static $fields = array(
		'id',
		'player_name',
		'official_status_id',
		'rank', // last known rank in format 'Heavy Class A', 'Medium Class B' (no number at end)
		'faction' // current faction full name or 'None'
	);

	protected static $beanMap = array(
		'official_status' => array(
			'joinType' => 'LEFT',
			'joinBean' => 'darkcrusader\sqlbeans\MilitaryStatusBean',
			'foreignKey' => 'official_status_id'
		)
	);

	public function get_official_status() {
		return $this->getMapped('official_status');
	}
}
?>