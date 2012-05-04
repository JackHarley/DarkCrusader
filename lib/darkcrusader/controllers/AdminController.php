<?php
/**
 * Admin Controller
 * Controls the administration area of Dark Crusader
 *
 * Copyright (c) 2011, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\controllers;

use darkcrusader\controllers\Controller;
use hydrogen\view\View;

use darkcrusader\models\UserModel;
use darkcrusader\models\KoSModel;

use darkcrusader\classes\UserPermissionSet;

class AdminController extends Controller {
	
	public function index() {
		$UserModel = UserModel::getInstance();
		$user = $UserModel->getLoggedInUser();
		
		if (!$user->permissions->hasPermission(UserPermissionSet::SEARCH_SCANS)) {
			View::load('admin/permission_denied');
			return;
		}
		
		View::load('admin/index');
	}
	
	public function kos($act=false) {
		$UserModel = UserModel::getInstance();
		$user = $UserModel->getLoggedInUser();
		
		if (!$user->permissions->hasPermission(UserPermissionSet::SEARCH_SCANS)) {
			View::load('admin/permission_denied');
			return;
		}
		
		if (!$user->permissions->hasPermission(UserPermissionSet::ADMIN_KOS_LIST)) {
			View::load('admin/permission_denied');
			return;
		}
		
		if (!$act) {
			$KoSModel = KoSModel::getInstance();
			$KoSList = $KoSModel->getKoSList();
			
			View::load('admin/kos/list', array(
				"KoSList" => $KoSList)
			);
			return;
		}
		
		switch ($act) {
			case "add":
				if (!$_POST["submit"]) {
					View::load('admin/kos/add');
					return;
				}
				else {
					$KoSModel = KoSModel::getInstance();
					$KoSModel->addKoSEntry($_POST["player"], $_POST["reason"]);
					$KoSList = $KoSModel->getKoSList();
		
					View::load('admin/kos/list', array(
						"KoSList" => $KoSList)
					);
					return;
				}
			break;
			
			case "del":
				if (!$_GET["id"]) {
					$KoSModel = KoSModel::getInstance();
					$KoSList = $KoSModel->getKoSList();
			
					View::load('admin/kos/list', array(
						"KoSList" => $KoSList)
					);
					return;
				}
				
				$KoSModel = KoSModel::getInstance();
				$KoSModel->deleteKoSEntry($_GET["id"]);
				$KoSList = $KoSModel->getKoSList();
		
				View::load('admin/kos/list', array(
					"KoSList" => $KoSList)
				);
				return;
			break;
		}
	}	
}
?>