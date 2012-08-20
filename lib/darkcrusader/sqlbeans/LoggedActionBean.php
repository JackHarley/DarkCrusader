<?php
namespace darkcrusader\sqlbeans;
use hydrogen\sqlbeans\SQLBean;

class LoggedActionBean extends SQLBean {

	protected static $tableNoPrefix = 'logged_actions';
	protected static $tableAlias = 'logged_actions';
	protected static $primaryKey = 'id';
	protected static $primaryKeyIsAutoIncrement = true;
	protected static $fields = array(
		'id',
		'user_id',
		'acted_upon_user_id',
		'type',
		'description',
		'date'
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