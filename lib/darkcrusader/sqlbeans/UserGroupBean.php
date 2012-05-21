<?php
/**
 * Project Dark Crusader
 * Copyright (c) 2012, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\sqlbeans;

use hydrogen\sqlbeans\SQLBean;
use hydrogen\database\Query;
use darkcrusader\sqlbeans\GroupPermissionBean;
use darkcrusader\sqlbeans\UserBean;

class UserGroupBean extends SQLBean {

	protected static $tableNoPrefix = 'user_groups';
	protected static $tableAlias = 'user_groups';
	protected static $primaryKey = 'id';
	protected static $primaryKeyIsAutoIncrement = true;
	protected static $fields = array(
		'id',
		'group_name',
		'description',
	);

	protected $permissions = false;
	public function getPermissions() {
		if (!$this->permissions) {
			$q = new Query("SELECT");
			$q->where("group_id = ?", $this->id);
			$this->permissions = GroupPermissionBean::select($q, true);
		}

		return $this->permissions;
	}

	protected $users = false;
	public function getUsers() {
		if (!$this->users) {
			$q = new Query("SELECT");
			$q->where("group_id = ?", $this->id);
			$this->users = UserBean::select($q, true);
		}

		return $this->users;
	}
}
?>