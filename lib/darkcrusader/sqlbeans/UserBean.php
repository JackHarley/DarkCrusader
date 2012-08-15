<?php
namespace darkcrusader\sqlbeans;
use hydrogen\sqlbeans\SQLBean;
use darkcrusader\models\PermissionsModel;
use darkcrusader\models\UserModel;

class UserBean extends SQLBean {

	protected static $tableNoPrefix = 'users';
	protected static $tableAlias = 'users';
	protected static $primaryKey = 'id';
	protected static $primaryKeyIsAutoIncrement = true;
	protected static $fields = array(
		'id',
		'username',
		'group_id',
		'passhash',
		'balance',
		'premium_until',
		'user_clearance_level'
	);
	
	protected static $beanMap = array(
		'group' => array(
			'joinType' => 'LEFT',
			'joinBean' => 'darkcrusader\sqlbeans\UserGroupBean',
			'foreignKey' => 'group_id'
		)
	);

	public function get_group() {
		return $this->getMapped("group");
	}

	protected $permissions;
	public function get_permissions() {
		if (!$this->permissions)
			$this->permissions = PermissionsModel::getInstance()->constructPermissionSet($this->group->getPermissions(), false);

		return $this->permissions;
	}

	protected $defaultCharacter;
	public function get_default_character() {
		if (!$this->defaultCharacter)
			$this->defaultCharacter = UserModel::getInstance()->getDefaultCharacter($this->id);

		return $this->defaultCharacter;
	}

	public $clearanceLevel;
	public function get_clearance_level() {
		if (!$this->clearanceLevel)
			$this->clearanceLevel =  UserModel::getInstance()->getClearanceLevel($this->id);

		return $this->clearanceLevel;
	}
}
?>