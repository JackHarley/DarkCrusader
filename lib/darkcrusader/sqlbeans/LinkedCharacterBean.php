<?php
namespace darkcrusader\sqlbeans;
use hydrogen\sqlbeans\SQLBean;
use darkcrusader\models\PermissionsModel;

class LinkedCharacterBean extends SQLBean {

	protected static $tableNoPrefix = 'linked_characters';
	protected static $tableAlias = 'linked_characters';
	protected static $primaryKey = 'id';
	protected static $primaryKeyIsAutoIncrement = true;
	protected static $fields = array(
		'id',
		'character_name',
		'user_id',
		'api_key',
		'is_default'
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