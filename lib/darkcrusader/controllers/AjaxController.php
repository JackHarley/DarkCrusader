<?php
/**
 * Ajax Controller
 * Controls returning data via JSON for
 * AJAX requests
 *
 * Copyright (c) 2011, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\controllers;

use hydrogen\controller\Controller;
use hydrogen\view\View;

use darkcrusader\models\KoSModel;
use darkcrusader\models\ScanModel;
use darkcrusader\models\UserModel;
use darkcrusader\models\PlayerModel;
use darkcrusader\models\SystemModel;
use darkcrusader\classes\UserPermissionSet;

class AjaxController extends Controller {
	
	public function getplayerstats() {
		$UserModel = UserModel::getInstance();
		$user = $UserModel->getLoggedInUser();
		
		if (!$user->permissions->hasPermission(UserPermissionSet::VIEW_PLAYER_STATS)) {
			echo 0;
			return;
		}
		
		$name = $_POST["name"];
		
		$PlayerModel = PlayerModel::getInstance();
		$player = $PlayerModel->getPlayerCurrentStats($name, true);
		
		echo json_encode($player);
	}
	
	public function getsystemstats() {
		$UserModel = UserModel::getInstance();
		$user = $UserModel->getLoggedInUser();
		
		if (!$user->permissions->hasPermission(UserPermissionSet::VIEW_SYSTEM_STATS)) {
			echo 0;
			return;
		}
		
		$name = $_POST["name"];
		
		$SystemModel = SystemModel::getInstance();
		$system = $SystemModel->getSystemStats($name);
		
		echo json_encode($system);
	}
	
	public function getfactionstats() {
		$UserModel = UserModel::getInstance();
		$user = $UserModel->getLoggedInUser();
		
		if (!$user->permissions->hasPermission(UserPermissionSet::VIEW_SYSTEM_STATS)) {
			echo 0;
			return;
		}
		
		$name = $_POST["name"];
		
		$SystemModel = SystemModel::getInstance();
		$faction = $SystemModel->getFactionStats($name);
		
		echo json_encode($faction);
	}
	
	public function getlocalityinformation() {
		$SystemModel = SystemModel::getInstance();
		$locality = $SystemModel->getLocalityInformation($_POST["quadrant"], $_POST["sector"], $_POST["region"], $_POST["locality"]);
		echo json_encode($locality);
	}
		
	public function getscan() {
		$UserModel = UserModel::getInstance();
		$user = $UserModel->getLoggedInUser();
		
		if (!$user->permissions->hasPermission(UserPermissionSet::VIEW_SCANS)) {
			echo 0;
			return;
		}
			
		$id = $_POST["id"];
		
		$ScanModel = ScanModel::getInstance();
		$scan = $ScanModel->getScanByID($id);
		
		echo json_encode($scan);
	}
	
	public function addscan() {
		$UserModel = UserModel::getInstance();
		$user = $UserModel->getLoggedInUser();
		
		if (!$user->permissions->hasPermission(UserPermissionSet::SUBMIT_SCANS)) {
			echo 0;
			return;
		}
			
		$paste = $_POST["paste"];
		
		$ScanModel = ScanModel::getInstance();
		$result = $ScanModel->addScanPaste($paste);
		
		echo json_encode($result);
	}
	
	public function login() {
		$UserModel = UserModel::getInstance();
		
		if ((!$_POST["username"]) || (!$_POST["password"])) {
			echo json_encode(array("result" => 0));
			die();
		}
		
		if ($UserModel->login($_POST["username"], $_POST["password"])) {
			
			$UserModel->createAutologin($_POST["username"]);
			echo json_encode(array("result" => 1));
		}
		else
			echo json_encode(array("result" => 0));
	}
	
	public function register() {
		$UserModel = UserModel::getInstance();
		
		if ((!$_POST["username"]) || (!$_POST["password"])) {
			echo json_encode(array("result" => 0));
			die();
		}
		
		if ($UserModel->register($_POST["username"], $_POST["password"]))
			echo json_encode(array("result" => 1));
		else
			echo json_encode(array("result" => 0));
	}
	
	public function getpage() {
		$page = str_replace('#', '', $_POST["hash"]);
		
		if (!$page)
			$page = "home";
			
		$UserModel = UserModel::getInstance();
		$user = $UserModel->getLoggedInUser();
		
		switch ($page) {
			case "home":
				if ($user->permissions->hasPermission(UserPermissionSet::ACCESS_INDEX))
					View::load('ajax/' . $page);
				else
					View::load('ajax/permission_denied');
			break;
		
			case "info":
				if ($user->permissions->hasPermission(UserPermissionSet::ACCESS_INFO))
					View::load('ajax/' . $page);
				else
					View::load('ajax/permission_denied');
			break;
		
			case "kos":
				if ($user->permissions->hasPermission(UserPermissionSet::ACCESS_KOS_LIST)) {
					$KoSModel = KoSModel::getInstance();
					$KoSList = $KoSModel->getKoSList();
				
					View::load('ajax/' . $page, array(
						"KoSList" => $KoSList)
					);
				}
				else {
					View::load('ajax/permission_denied');
				}
			break;
		
			case "blog":
				if ($user->permissions->hasPermission(UserPermissionSet::ACCESS_BLOG))
					View::load('ajax/' . $page);
				else
					View::load('ajax/permission_denied');
			break;
		
			case "scans":
				if ($user->permissions->hasPermission(UserPermissionSet::ACCESS_SCANS)) {
					$ScanModel = ScanModel::getInstance();
					$scans = $ScanModel->getLatestScans(8);
					View::load('ajax/' . $page, array("latestScans" => $scans));
				}
				else {
					View::load('ajax/permission_denied');
				}
			break;
		
			case "submitscan":
				if ($user->permissions->hasPermission(UserPermissionSet::SUBMIT_SCANS))
					View::load('ajax/' . $page);
				else
					View::load('ajax/permission_denied');
			break;
		
			case "searchresource":
				if ($user->permissions->hasPermission(UserPermissionSet::SEARCH_SCANS))
					View::load('ajax/' . $page);
				else
					View::load('ajax/permission_denied');
			break;
		
			case "localityinformation":
				if ($user->permissions->hasPermission(UserPermissionSet::ACCESS_LOCALITY_INFORMATION))
					View::load('ajax/' . $page);
				else
					View::load('ajax/permission_denied');
			break;
		
			case "login":
				View::load('ajax/' . $page);
			break;
		
			case "register":
				View::load('ajax/' . $page);
			break;
		
			case "logout":
				$UserModel->logout();
				View::load('ajax/' . $page);
			break;
		
			case "playerstats":
				if ($user->permissions->hasPermission(UserPermissionSet::ACCESS_PLAYER_STATS))
					View::load('ajax/' . $page);
				else
					View::load('ajax/permission_denied');
				
			break;
		
			case "systems":
				if ($user->permissions->hasPermission(UserPermissionSet::ACCESS_SYSTEM_STATS))
					View::load('ajax/' . $page);
				else
					View::load('ajax/permission_denied');
				
			break;
		
			case "factions":
				if ($user->permissions->hasPermission(UserPermissionSet::ACCESS_SYSTEM_STATS))
					View::load('ajax/' . $page);
				else
					View::load('ajax/permission_denied');
			break;
		}
	}
	
}
?>