<?php
namespace darkcrusader\sqlbeans;
use hydrogen\sqlbeans\SQLBean;

class SkillCategoryBean extends SQLBean {

	protected static $tableNoPrefix = 'skill_categories';
	protected static $tableAlias = 'skill_categories';
	protected static $primaryKey = 'id';
	protected static $primaryKeyIsAutoIncrement = true;
	protected static $fields = array(
		'id',
		'name'
	);
	
	protected static $beanMap = array(
	);
}
?>