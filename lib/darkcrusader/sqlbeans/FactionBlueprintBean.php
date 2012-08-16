<?php
namespace darkcrusader\sqlbeans;
use hydrogen\sqlbeans\SQLBean;
use hydrogen\config\Config;

class FactionBlueprintBean extends SQLBean {

	protected static $tableNoPrefix = 'faction_member_blueprints';
	protected static $tableAlias = 'faction_member_blueprints';
	protected static $primaryKey = 'id';
	protected static $primaryKeyIsAutoIncrement = true;
	protected static $fields = array(
		'id',
		'submitter_id',
		'description',
		'researcher_player_name',
		'research_mark',
		'date_added'
	);
	
	protected static $beanMap = array(
		'submitter' => array(
			'joinType' => 'LEFT',
			'joinBean' => 'darkcrusader\sqlbeans\UserBean',
			'foreignKey' => 'submitter_id'
		)
	);

	public function get_submitter() {
		return $this->getMapped("submitter");
	}
}
?>