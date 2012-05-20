<?php
/**
 * KoS SQLBean
 *
 * Copyright (c) 2012, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\sqlbeans;
use hydrogen\sqlbeans\SQLBean;

class KoSBean extends SQLBean {

	protected static $tableNoPrefix = 'kill_on_sight_list';
	protected static $tableAlias = 'kill_on_sight_list';
	protected static $primaryKey = 'id';
	protected static $primaryKeyIsAutoIncrement = true;
	protected static $fields = array(
		'id',
		'player_name',
		'reason',
		'creation_time',
		'creator_id'
	);
	
	protected static $beanMap = array(
	);
}
?>