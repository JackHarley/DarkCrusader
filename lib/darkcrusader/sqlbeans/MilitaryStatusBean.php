<?php
namespace darkcrusader\sqlbeans;
use hydrogen\sqlbeans\SQLBean;

class MilitaryStatusBean extends SQLBean {

	protected static $tableNoPrefix = 'military_statuses';
	protected static $tableAlias = 'military_statuses';
	protected static $primaryKey = 'id';
	protected static $primaryKeyIsAutoIncrement = true;
	protected static $fields = array(
		'id',
		'name',
		'hex_colour'
	);
	
	protected static $beanMap = array(
	);
}
?>