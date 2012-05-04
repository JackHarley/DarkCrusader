<?php
/**
 * PlayerStats SQLBean
 *
 * Copyright (c) 2011, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\sqlbeans;
use hydrogen\sqlbeans\SQLBean;

class PlayerBountyStatsBean extends SQLBean {

	protected static $tableNoPrefix = 'player_bounty_stats';
	protected static $tableAlias = 'player_bounty_stats';
	protected static $primaryKey = 'id';
	protected static $primaryKeyIsAutoIncrement = true;
	protected static $fields = array(
		'id',
		'leaderboard_position',
		'player_name',
		'bounty',
		'stats_set'
	);
	
	protected static $beanMap = array(
	);
}
?>