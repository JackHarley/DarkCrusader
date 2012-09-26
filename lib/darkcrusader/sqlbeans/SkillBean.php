<?php
namespace darkcrusader\sqlbeans;
use hydrogen\sqlbeans\SQLBean;
use darkcrusader\models\SkillsModel;

class SkillBean extends SQLBean {

	protected static $tableNoPrefix = 'skills';
	protected static $tableAlias = 'skills';
	protected static $primaryKey = 'id';
	protected static $primaryKeyIsAutoIncrement = true;
	protected static $fields = array(
		'id',
		'name',
		'description',
		'category_id'
	);
	
	protected static $beanMap = array(
		'category' => array(
			'joinType' => 'LEFT',
			'joinBean' => 'darkcrusader\sqlbeans\SkillCategoryBean',
			'foreignKey' => 'category_id'
		)
	);

	public function get_category() {
		return $this->getMapped('category');
	}

	public function get_prerequistes() {
		return SkillsModel::getInstance()->getPrerequisteSkillsForSkill($this->id);
	}

	public function get_unlocks() {
		return SkillsModel::getInstance()->getPrerequisteSkillsThatSkillUnlocks($this->id);
	}
}
?>