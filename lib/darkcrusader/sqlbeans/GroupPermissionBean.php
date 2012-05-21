<?php
/**
 * Project Dark Crusader
 * Copyright (c) 2012, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\sqlbeans;

use hydrogen\sqlbeans\SQLBean;
use hydrogen\database\Query;

class GroupPermissionBean extends SQLBean {

	protected static $tableNoPrefix = 'group_permissions';
	protected static $tableAlias = 'group_permissions';
	protected static $primaryKey = 'id';
	protected static $primaryKeyIsAutoIncrement = true;
	protected static $fields = array(
		'id',
		'group_id',
		'permission_id',
		'value'
	);
	
	protected static $beanMap = array(
		'group' => array(
			'joinType' => 'LEFT',
			'joinBean' => 'darkcrusader\sqlbeans\UserGroupBean',
			'foreignKey' => 'group_id'
		),
		'permission' => array(
			'joinType' => 'LEFT',
			'joinBean' => 'darkcrusader\sqlbeans\PermissionBean',
			'foreignKey' => 'permission_id'
		)
	);
}
?>