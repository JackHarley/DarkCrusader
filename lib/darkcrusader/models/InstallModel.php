<?php
/**
 * Project Dark Crusader
 * Copyright (c) 2012, Jack Harley
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
	const maxDbVersion = 2;

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
				'users', 'user_groups', 'permissions', 'group_permissions', 'database_version', 'faction_bank_transactions',
				'intelligence', 'kill_on_sight_list', 'personal_bank_transactions', 'scans', 'scan_results', 'systems', 'system_stats',
				'system_stats_sets'
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
				`oe_api_access_key` varchar(60) NOT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;"
		);

		$pdo->pdo->query("
			CREATE TABLE IF NOT EXISTS `user_groups` (
				`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				`group_name` varchar(32) NOT NULL,
				`description` varchar(128) NOT NULL,
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
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;"
		);

		$pdo->pdo->query("
			CREATE TABLE IF NOT EXISTS `personal_bank_transactions` (
				`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				`user_id` bigint(20) unsigned NOT NULL,
				`type` varchar(16) NOT NULL,
				`direction` varchar(4) NOT NULL,
				`amount` bigint(20) unsigned NOT NULL,
				`balance` bigint(20) unsigned NOT NULL,
				`description` varchar(255) NOT NULL,
				`date` datetime NOT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;"
		);

		$pdo->pdo->query("
			CREATE TABLE IF NOT EXISTS `faction_bank_transactions` (
				`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				`player_name` varchar(32) NOT NULL,
				`type` varchar(16) NOT NULL,
				`direction` varchar(4) NOT NULL,
				`amount` bigint(20) unsigned NOT NULL,
				`balance` bigint(20) unsigned NOT NULL,
				`date` datetime NOT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;"
		);

		$pdo->pdo->query("
			CREATE TABLE IF NOT EXISTS `scans` (
				`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				`system_id` bigint(20) unsigned NOT NULL,
				`planet_number` varchar(4) NOT NULL,
				`moon_number` tinyint NOT NULL,
				`date_submitted` datetime NOT NULL,
				`submitter_id` bigint(20) unsigned NOT NULL,
				`scanner_level` bigint(20) unsigned NOT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;"
		);

		$pdo->pdo->query("
			CREATE TABLE IF NOT EXISTS `scan_results` (
				`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				`scan_id` bigint(20) unsigned NOT NULL,
				`resource_name` varchar(16) NOT NULL,
				`resource_quality` varchar(16) NOT NULL,
				`resource_extraction_rate` bigint(20) unsigned NOT NULL,
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
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;"
		);

		$pdo->pdo->query("
			CREATE TABLE IF NOT EXISTS `systems` (
				`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				`system_name` varchar(32) NOT NULL,
				`quadrant` tinyint unsigned NOT NULL,
				`sector` tinyint unsigned NOT NULL,
				`region` tinyint unsigned NOT NULL,
				`locality` tinyint unsigned NOT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;"
		);

		$pdo->pdo->query("
			CREATE TABLE IF NOT EXISTS `system_stats` (
				`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				`stats_set` bigint(20) unsigned NOT NULL NOT NULL,
				`has_station` tinyint(1) unsigned NOT NULL,
				`faction` varchar(32) NOT NULL,
				`system_id` bigint(20) unsigned NOT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;"
		);

		$pdo->pdo->query("
			CREATE TABLE IF NOT EXISTS `system_stats_sets` (
				`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				`time` datetime NOT NULL,
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
		$pm->createPermission("bank", "access_personal_bank", "Access personal bank");
		$pm->createPermission("bank", "access_faction_bank", "Access faction bank");
		$pm->createPermission("bank", "administrate_faction_bank", "Admin faction bank");

		return true;
	}

	/**
	 * Migrate the database to version 2
	 *
	 * @param PDOEngine $pdo Copy of the PDO engine returned by the DatabaseEngineFactory
	 * @param string $user username of initial user
	 * @param string $pass password of initial user
	 *
	 * @return boolean true on success
	 */
	protected function _runMigrationToVersion2($pdo, $user, $pass) {
		$pdo->pdo->query("ALTER TABLE scans MODIFY `scanner_level` float(1) NOT NULL");

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
