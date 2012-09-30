<?php
namespace darkcrusader\sqlbeans;
use hydrogen\sqlbeans\SQLBean;
use hydrogen\config\Config;

class StoredItemBean extends SQLBean {

	protected static $tableNoPrefix = 'stored_items';
	protected static $tableAlias = 'stored_items';
	protected static $primaryKey = 'id';
	protected static $primaryKeyIsAutoIncrement = true;
	protected static $fields = array(
		'id',
		'user_id',

		'location_type', // 'colony', 'station' or 'ship'
		/* Colony ID */
		'colony_id',
		/* OR System ID and Station Numeral */
		'system_id',
		'planet_numeral',
		/* OR Ship Name */
		'ship_name',

		'description',
		'type',
		'quantity'
	);
	
	protected static $beanMap = array(
		'user' => array(
			'joinType' => 'LEFT',
			'joinBean' => 'darkcrusader\sqlbeans\UserBean',
			'foreignKey' => 'user_id'
		),
		'system' => array(
			'joinType' => 'LEFT',
			'joinBean' => 'darkcrusader\sqlbeans\SystemBean',
			'foreignKey' => 'system_id'
		),
		'colony' => array(
			'joinType' => 'LEFT',
			'joinBean' => 'darkcrusader\sqlbeans\ColonyBean',
			'foreignKey' => 'colony_id'
		),
	);

	public function get_blueprint_description() {
		return str_replace("BLUEPRINT : ", "", $this->description);
	}
}
?>