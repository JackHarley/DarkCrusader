<?php
namespace darkcrusader\sqlbeans;
use hydrogen\sqlbeans\SQLBean;
use darkcrusader\models\PermissionsModel;

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
		'balance'
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

}
?>