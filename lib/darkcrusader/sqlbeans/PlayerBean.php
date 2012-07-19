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
		'player_name'
	);

	protected static $beanMap = array(
	);
}
?>