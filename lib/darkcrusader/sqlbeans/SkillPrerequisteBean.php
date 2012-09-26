<?php
namespace darkcrusader\sqlbeans;
use hydrogen\sqlbeans\SQLBean;
use darkcrusader\models\SkillsModel;

class SkillPrerequisteBean extends SQLBean {

	protected static $tableNoPrefix = 'skill_prerequistes';
	protected static $tableAlias = 'skill_prerequistes';
	protected static $primaryKey = 'id';
	protected static $primaryKeyIsAutoIncrement = true;
	protected static $fields = array(
		'id',
		'skill_id',
		'prerequiste_skill_id',
		'prerequiste_skill_level'
	);
	
	protected static $beanMap = array(
		'prerequiste_skill' => array(
			'joinType' => 'LEFT',
			'joinBean' => 'darkcrusader\sqlbeans\SkillBean',
			'foreignKey' => 'prerequiste_skill_id'
		)
	);

	public function get_prerequiste_skill() {
		return $this->getMapped('prerequiste_skill');
	}

	public function get_skill() {
		return SkillsModel::getInstance()->getSkill($this->skill_id);
	}
}
?>