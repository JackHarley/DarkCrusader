<?php
namespace darkcrusader\sqlbeans;
use hydrogen\sqlbeans\SQLBean;

class AutologinBean extends SQLBean {

	protected static $tableNoPrefix = 'autologin';
	protected static $tableAlias = 'autologin';
	protected static $primaryKey = 'id';
	protected static $primaryKeyIsAutoIncrement = true;
	protected static $fields = array(
		'id',
		'user_id',
		'public_key',
		'private_key',
		'created_on'
	);
	
	protected static $beanMap = array(
		'user' => array(
			'joinType' => 'LEFT',
			'joinBean' => 'darkcrusader\sqlbeans\UserBean',
			'foreignKey' => 'user_id'
		)
	);
}
?>