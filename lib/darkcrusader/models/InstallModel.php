<?php
/**
 * Project darkcrusader
 * Copyright (c) 2012, BroadcasTheNet
 * All Rights Reserved
 */
namespace darkcrusader\models;

use hydrogen\model\Model;
use hydrogen\database\DatabaseEngineFactory;
use hydrogen\database\Query;
use hydrogen\recache\RECacheManager;

use darkcrusader\models\UserModel;
use darkcrusader\models\UserGroupModel;
use darkcrusader\sqlbeans\UserGroupBean;
use darkcrusader\sqlbeans\UserBean;
use darkcrusader\permissions\PermissionSet;

class InstallModel extends Model {

	protected static $modelID = "install";
	const maxDbVersion = 1;

	/**
	 * Checks if the DB is installed
	 *
	 * @return boolean true or false depending on if DB is installed
	 */
	public function checkIfDatabaseIsInstalled() {
		$q = new Query("SELECT");
		$q->from("database_version");
		$q->field("database_version");
		$q->orderby("database_version", "DESC");
		$q->limit(1);

		$stmt = $q->prepare();

		if (!$stmt)
			return false;

		$stmt->execute();
		$row = $stmt->fetchObject();
		if (!$row->database_version)
			return false;

		return true;
	}

	/**
	 * Checks if the DB is installed
	 *
	 * @return boolean true or false depending on if DB is up to date
	 */
	public function checkIfDatabaseIsUpToDate() {
		$q = new Query("SELECT");
		$q->from("database_version");
		$q->field("database_version");
		$q->orderby("database_version", "DESC");
		$q->limit(1);

		$stmt = $q->prepare();

		if (!$stmt)
			return false;

		$stmt->execute();
		$row = $stmt->fetchObject();
		if ($row->database_version < self::maxDbVersion)
			return false;

		return true;
	}

	/**
	 * Installs the tables into the database
	 *
	 * @param string $user Username for first admin
	 * @param string $pass Password for first admin
	 * @param string $email Email for first admin
	 * @param boolean $overwriteExisting Set to true to wipe database before install
	 * @return boolean true on success
	 */
	public function installDatabase($user, $pass, $overwriteExisting=false) {

		// Bye bye session
		session_start();
		session_destroy();

		// Destroy all session data in existence
		$files = glob(ini_get('session.save_path') . '/sess_*');
		if (is_array($files)) {
			foreach($files as $file) {
				unlink($file);
			}
		}

		// Get a copy of the PDO engine
		$pdo = DatabaseEngineFactory::getEngine();

		// Drop existing tables
		if ($overwriteExisting) {
			$tables = array(
				'users', 'user_groups', 'permissions', 'group_permissions', 'database_version'
			);
			$tablestr = '`' . implode('`, `', $tables) . '`';

			$pdo->pdo->query("DROP TABLE IF EXISTS $tablestr");
		}

		// we always need the database version table
		$pdo->pdo->query("
			CREATE TABLE IF NOT EXISTS `database_version` (
				`database_version` bigint(20) unsigned NOT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=latin1;"
		);
		// throw a 0 value in in case theres no values
		$pdo->pdo->query("
			INSERT INTO `database_version` (`database_version`) VALUES (0);"
		);

		$pdo = DatabaseEngineFactory::getEngine();
		$this->migrateDatabase(self::maxDbVersion, $user, $pass, $email);
		if ($overwriteExisting)
			$this->preloadData($pdo, $user, $pass, $email);

		// Clear cache
		$rec = RECacheManager::getInstance();
		$rec->clearAll();
	}

	/**
	 * Migrate the database to the specified version
	 *
	 * @param int $toVersion Version to migrate the database to
	 * @param string $user username of initial user
	 * @param string $pass password of initial user
	 * @param string $email email of initial user
	 *
	 * @return boolean true on success
	 */
	public function migrateDatabase($toVersion, $user, $pass, $email) {
		$pdo = DatabaseEngineFactory::getEngine();

		$currentVersion = $pdo->pdo->query("SELECT * FROM `database_version`")->fetchColumn();
		$currentVersion++;

		while ($currentVersion <= $toVersion) {
			$migrationName = '_runMigrationToVersion' . ($currentVersion);

			if (!$this->$migrationName($pdo, $user, $pass)) {
				return $currentVersion;
			}

			$pdo->pdo->query("UPDATE database_version SET database_version='$currentVersion'");
			$currentVersion++;
		}
		return $currentVersion;
	}

	/**
	 * Migrate the database to version 1
	 *
	 * @param PDOEngine $pdo Copy of the PDO engine returned by the DatabaseEngineFactory
	 * @param string $user username of initial user
	 * @param string $pass password of initial user
	 *
	 * @return boolean true on success
	 */
	protected function _runMigrationToVersion1($pdo, $user, $pass) {
		$pdo->pdo->query("
			CREATE TABLE IF NOT EXISTS `users` (
				`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				`username` varchar(32) NOT NULL,
				`group_id` bigint(20) unsigned NOT NULL,
				`passhash` varchar(60) NOT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;"
		);

		$pdo->pdo->query("
			CREATE TABLE IF NOT EXISTS `user_groups` (
				`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				`group_name` varchar(32) NOT NULL,
				`description` varchar(128) NOT NULL,
				`colour` varchar(6) NOT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;"
		);

		$pdo->pdo->query("
			CREATE TABLE IF NOT EXISTS `permissions` (
				`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				`name` varchar(32) NOT NULL,
				`type` varchar(16) NOT NULL,
				`description` varchar(128) NOT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;"
		);

		$pdo->pdo->query("
			CREATE TABLE IF NOT EXISTS `group_permissions` (
				`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				`group_id` bigint(20) unsigned NOT NULL,
				`permission_id` bigint(20) unsigned NOT NULL,
				`value` tinyint(1) NOT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;"
		);

		$pdo->pdo->query("
			CREATE TABLE IF NOT EXISTS `autologin` (
				`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				`user_id` bigint(20) unsigned NOT NULL,
				`public_key` varchar(32) NOT NULL,
				`private_key` varchar(32) NOT NULL,
				`created_on` datetime NOT NULL,
				`last_used_on` datetime NOT NULL,
				`last_used_ip` bigint(20) unsigned NOT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;"
		);

		// Get the UM, PM and UGM
		$um = UserModel::getInstance();
		$pm = PermissionsModel::getInstance();
		$ugm = UserGroupModel::getInstance();

		// Create permissions
		$pm->createPermission("site", "access_site", "Access the main site");
		$pm->createPermission("scans", "access_scans", "Access scans");
		$pm->createPermission("scans", "submit_scans", "Submit scans");
		$pm->createPermission("player", "access_player_stats", "Access player stats");
		$pm->createPermission("system", "access_system_stats", "Access system stats");
		$pm->createPermission("faction", "access_faction_stats", "Access faction stats");
		$pm->createPermission("kos", "access_kos", "Access the KoS list");
		$pm->createPermission("admin", "access_admin_panel", "Access the admin panel");

		return true;
	}

	/**
	 * Preload data
	 *
	 * @param PDOEngine $pdo Copy of the PDO engine returned by the DatabaseEngineFactory
	 * @param string $user username of initial user
	 * @param string $pass password of initial user
	 *
	 * @return boolean true on success
	 */
	protected function preloadData($pdo, $user, $pass) {

		$um = UserModel::getInstance();
		$ugm = UserGroupModel::getInstance();
		$pm = PermissionsModel::getInstance();

		// Create the Root Admin user group
		$pbs = $pm->getAllPermissions();
		$ugm->addUserGroup("root_admin", "Root Admin", false, $pbs);

		// Create the Member user group
		$pbs = $pm->getPermissions(array(
			"access_site",
			"access_scans",
			"access_player_stats",
			"access_system_stats",
			"access_faction_stats",
			"access_kos")
		);
		$ugm->addUserGroup("user", "User", false, $pbs);

		// Create the Guest user group
		$pbs = $pm->getPermissions(array(
			"access_site",
			"access_player_stats",
			"access_system_stats",
			"access_faction_stats",
			"access_kos")
		);
		$ugm->addUserGroup("guest", "Guest", false, $pbs);

		// Create the Banned user group
		$pbs = array();
		$ugm->addUserGroup("banned", "Banned", false, $pbs);

		// Create the admin user
		$query = new Query("SELECT");
		$query->where("group_name = ?", "root_admin");
		$query->limit(1);
		$ugbs = UserGroupBean::select($query);
		$ugb = $ugbs[0];

		$um->addUser($user, $pass, $ugb->id);
		$q = new Query("SELECT");
		$q->from("users");
		$q->field("id");
		$q->where("username = ?", $user);
		$stmt = $q->prepare();
		$stmt->execute();
		$u = $stmt->fetchObject();
	}
}
