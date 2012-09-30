<?php
namespace darkcrusader\sqlbeans;
use hydrogen\sqlbeans\SQLBean;

class BlueprintResourceBean extends SQLBean {

	protected static $tableNoPrefix = 'blueprint_resources';
	protected static $tableAlias = 'blueprint_resources';
	protected static $primaryKey = 'id';
	protected static $primaryKeyIsAutoIncrement = true;
	protected static $fields = array(
		'id',
		'submitter_id',
		'blueprint_description',
		'resource_name',
		'resource_quantity'
	);
	
	protected static $beanMap = array(
	);
}
?>