<?php
namespace darkcrusader\sqlbeans;
use hydrogen\sqlbeans\SQLBean;

class IntelligenceBean extends SQLBean {

	protected static $tableNoPrefix = 'intelligence';
	protected static $tableAlias = 'intelligence';
	protected static $primaryKey = 'id';
	protected static $primaryKeyIsAutoIncrement = true;
	protected static $fields = array(
		'id',
		'system_id',
		'player_name',
		'info',
		'date_added',
		'submitter_id'
	);
	
	protected static $beanMap = array(
		'system' => array(
			'joinType' => 'LEFT',
			'joinBean' => 'darkcrusader\sqlbeans\SystemBean',
			'foreignKey' => 'system_id'
		),
		'submitter' => array(
			'joinType' => 'LEFT',
			'joinBean' => 'darkcrusader\sqlbeans\UserBean',
			'foreignKey' => 'submitter_id'
		),
		'player' => array(
			'joinType' => 'LEFT',
			'joinBean' => 'darkcrusader\sqlbeans\UserBean',
			'foreignKey' => 'submitter_id'
		),
	);
}
?>