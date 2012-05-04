<?php
/**
 * Project Nebula
 * Copyright (c) 2012, BroadcasTheNet
 * All Rights Reserved
 */
namespace darkcrusader\permissions;

class PermissionSet {
	
	public $permissionBeans = array();
	
	/**
	 * Construction
	 *
	 * @param int permission bean array
	 */
	function __construct($permissions=false) {
		if ($permissions)
			$this->permissionBeans = $permissions;
	}
	
	/**
	 * Checks if this set holds a permission
	 *
	 * @param string permission to check for
	 * @return boolean true if permission is allowed or false if not
	 */
	function hasPermission($permission) {
		foreach($this->permissionBeans as $bean) {
			if ($bean->name == $permission)
				return true;
		}
		return false;
	}
}
?>