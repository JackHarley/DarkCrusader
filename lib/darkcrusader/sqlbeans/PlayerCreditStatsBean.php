<?php
/**
 * PlayerStats SQLBean
 *
 * Copyright (c) 2012, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\sqlbeans;
use hydrogen\sqlbeans\SQLBean;

class PlayerCreditStatsBean extends SQLBean {

	protected static $tableNoPrefix = 'player_credit_stats';
	protected static $tableAlias = 'player_credit_stats';
	protected static $primaryKey = 'id';
	protected static $primaryKeyIsAutoIncrement = true;
	protected static $fields = array(
		'id',
		'leaderboard_position',
		'player_name',
		'credits',
		'stats_set'
	);
	
	protected static $beanMap = array(
	);
}
?>