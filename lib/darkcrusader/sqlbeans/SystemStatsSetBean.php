<?php
/**
 * SystemStatsSet SQLBean
 *
 * Copyright (c) 2011, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\sqlbeans;
use hydrogen\sqlbeans\SQLBean;

class SystemStatsSetBean extends SQLBean {

	protected static $tableNoPrefix = 'system_stats_sets';
	protected static $tableAlias = 'system_stats_sets';
	protected static $primaryKey = 'id';
	protected static $primaryKeyIsAutoIncrement = true;
	protected static $fields = array(
		'id',
		'time',
	);
	
	protected static $beanMap = array(
	);
}
?>