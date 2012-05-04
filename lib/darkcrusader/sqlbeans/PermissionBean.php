<?php
/**
 * Project darkcrusader
 * Copyright (c) 2012, BroadcasTheNet
 * All Rights Reserved
 */
namespace darkcrusader\sqlbeans;

use hydrogen\sqlbeans\SQLBean;
use hydrogen\database\Query;

class PermissionBean extends SQLBean {

	protected static $tableNoPrefix = 'permissions';
	protected static $tableAlias = 'permissions';
	protected static $primaryKey = 'id';
	protected static $primaryKeyIsAutoIncrement = true;
	protected static $fields = array(
		'id',
		'type',
		'name',
		'description'
	);
	
	protected static $beanMap = array(
	);
}
?>