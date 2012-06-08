<?php
namespace darkcrusader\sqlbeans;
use hydrogen\sqlbeans\SQLBean;

class CharacterLinkRequestBean extends SQLBean {

	protected static $tableNoPrefix = 'character_link_requests';
	protected static $tableAlias = 'character_link_requests';
	protected static $primaryKey = 'id';
	protected static $primaryKeyIsAutoIncrement = true;
	protected static $fields = array(
		'id',
		'character_name',
		'user_id',
		'api_key',
		'verification_amount',
		'date_requested'
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