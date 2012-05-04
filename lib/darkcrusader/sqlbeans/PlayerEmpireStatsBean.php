<?php
/**
 * PlayerStats SQLBean
 *
 * Copyright (c) 2011, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\sqlbeans;
use hydrogen\sqlbeans\SQLBean;

class PlayerEmpireStatsBean extends SQLBean {

	protected static $tableNoPrefix = 'player_empire_stats';
	protected static $tableAlias = 'player_empire_stats';
	protected static $primaryKey = 'id';
	protected static $primaryKeyIsAutoIncrement = true;
	protected static $fields = array(
		'id',
		'leaderboard_position',
		'player_name',
		'colonies',
		'population',
		'stats_set'
	);
	
	protected static $beanMap = array(
	);
}
?>