<?php
/**
 * Project Dark Crusader
 * Copyright (c) 2012, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\models;

use hydrogen\model\Model;
use hydrogen\database\Query;

use darkcrusader\permissions\PermissionSet;

use darkcrusader\sqlbeans\PermissionBean;
use darkcrusader\sqlbeans\GroupPermissionBean;

class PermissionsModel extends Model {

	protected static $modelID = "perms";

	/**
	 * Constructs a PermissionSet from the given GroupPermissionBeans
	 * and UserPermissionBeans
	 * IMPORTANT: The GPBs and UPBs must have been queried WITH mappings
	 * else this will fail
	 *
	 * @param array array of GroupPermissionBeans
	 * @param array array of UserPermissionBeans
	 * @return darkcrusader\permissions\PermissionSet Permission set
	 */
	public function constructPermissionSet($gpbs=false, $upbs=false) {

		$pbs = array();

		if ($gpbs) {
			foreach ($gpbs as $gpb) {
				if ($gpb->value == 1)
					$pbs[] = $gpb->getMapped("permission");
			}
		}

		if ($upbs) {
			foreach($upbs as $upb) {
				foreach ($pbs as $id => $pb) {
					if ($pb->name == $upb->name)
						unset($pbs[$id]);
				}
				if ($gpb->value == 1)
					$pbs[] = $upb->getMspped("permission");
			}
		}

		$ps = new PermissionSet($pbs);
		return $ps;
	}

	/**
	 * Constructs a PermissionSet from the given GroupPermissionBeans
	 * and UserPermissionBeans, with the permissions NOT granted
	 * IMPORTANT: The GPBs and UPBs must have been queried WITH mappings
	 * else this will fail
	 *
	 * @param array array of GroupPermissionBeans
	 * @param array array of UserPermissionBeans
	 * @return darkcrusader\permissions\PermissionSet Permission set
	 */
	public function constructNonGrantedPermissionSet($gpbs=false, $upbs=false) {

		$grantedSet = $this->constructPermissionSet($gpbs, $upbs);
		$allPerms = $this->getAllPermissions();

		$nonGrantedPerms = $allPerms;
		foreach($allPerms as $id => $perm) {
			foreach($grantedSet->permissionBeans as $grantedPerm) {
				if ($perm->id == $grantedPerm->id)
					unset($nonGrantedPerms[$id]);
			}
		}

		$nonGrantedSet = new PermissionSet($nonGrantedPerms);
		return $nonGrantedSet;
	}

	/**
	 * Creates a permission and inserts it
	 *
	 * @param string permission type (site, forums, admin, etc.)
	 * @param string permission name (access_site)
	 * @param string description (Access the main site)
	 */
	public function createPermission($type, $name, $description) {

		$pb = new PermissionBean;
		$pb->type = $type;
		$pb->name = $name;
		$pb->description = $description;
		$pb->insert();
	}

	/**
	 * Deletes a permission
	 *
	 * @param string permission name (access_site)
	 */
	public function deletePermission($name) {

		$q = new Query("SELECT");
		$q->where("name = ?", $name);
		$pbs = PermissionBean::select($q);
		$pb = $pbs[0];

		if (!$pb->id)
			return;
		
		$q = new Query("DELETE");
		$q->from("group_permissions");
		$q->where("permission_id = ?", $pb->id);
		$q->prepare()->execute();

		$pb->delete();
	}

	/**
	 * Gets all permissions and returns as an array of PermissionBeans
	 *
	 * @return array array of PermissionBeans
	 */
	public function getAllPermissions() {

		$pbs = PermissionBean::select();
		return $pbs;
	}

	/**
	 * Gets the permission beans for the permission names given
	 *
	 * @param array array of permission names
	 * @return array array of PermissionBeans
	 */
	public function getPermissions($permissions) {

		$q = new Query("SELECT");

		$q->whereOpenGroup("OR");
		foreach ($permissions as $perm)
			$q->where("name = ?", $perm, "OR");
		$q->whereCloseGroup();

		$pbs = PermissionBean::select($q);
		return $pbs;
	}

}
?>