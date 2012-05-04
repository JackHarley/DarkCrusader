<?php
/**
 * PlayerStats SQLBean
 *
 * Copyright (c) 2011, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\sqlbeans;
use hydrogen\sqlbeans\SQLBean;

class PlayerXPStatsBean extends SQLBean {

	protected static $tableNoPrefix = 'player_xp_stats';
	protected static $tableAlias = 'player_xp_stats';
	protected static $primaryKey = 'id';
	protected static $primaryKeyIsAutoIncrement = true;
	protected static $fields = array(
		'id',
		'leaderboard_position',
		'player_name',
		'rank',
		'total_xp',
		'stats_set'
	);
	
	protected static $beanMap = array(
		'set' => array(
			'joinType' => 'LEFT',
			'joinBean' => 'darkcrusader\sqlbeans\PlayerStatsSetBean',
			'foreignKey' => 'stats_set'
		)
	);
}
?>