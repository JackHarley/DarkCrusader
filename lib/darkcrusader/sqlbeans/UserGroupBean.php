<?php
/**
 * Project darkcrusader
 * Copyright (c) 2012, BroadcasTheNet
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

	public function getPermissions() {
		$q = new Query("SELECT");
		$q->where("group_id = ?", $this->id);
		$gpbs = GroupPermissionBean::select($q, true);

		return $gpbs;
	}

	public function getUsers() {
		$q = new Query("SELECT");
		$q->where("group_id = ?", $this->id);

		$ubs = UserBean::select($q, true);
		return $ubs;
	}
}
?>