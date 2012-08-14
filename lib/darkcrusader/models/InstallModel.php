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
	const maxDbVersion = 17;

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
				'system_stats_sets', 'site_bank_transfers', 'character_link_requests', 'linked_characters', 'players'
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
	 * Migrate the database to version 3
	 *
	 * @param PDOEngine $pdo Copy of the PDO engine returned by the DatabaseEngineFactory
	 * @param string $user username of initial user
	 * @param string $pass password of initial user
	 *
	 * @return boolean true on success
	 */
	protected function _runMigrationToVersion3($pdo, $user, $pass) {
		$pm = PermissionsModel::getInstance();
		$pm->createPermission("locality", "access_locality_stats", "Access locality stats");
		$pm->createPermission("beta", "test_beta_features", "Test beta features");

		return true;
	}

	/**
	 * Migrate the database to version 4
	 *
	 * @param PDOEngine $pdo Copy of the PDO engine returned by the DatabaseEngineFactory
	 * @param string $user username of initial user
	 * @param string $pass password of initial user
	 *
	 * @return boolean true on success
	 */
	protected function _runMigrationToVersion4($pdo, $user, $pass) {
		$pdo->pdo->query("
			CREATE TABLE IF NOT EXISTS `linked_characters` (
				`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				`user_id` bigint(20) unsigned NOT NULL,
				`character_name` varchar(32) NOT NULL,
				`api_key` varchar(32) NOT NULL,
				`is_default` tinyint(1) unsigned NOT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;"
		);

		$pdo->pdo->query("
			CREATE TABLE IF NOT EXISTS `site_bank_transfers` (
				`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				`character_name` varchar(32) NOT NULL,
				`amount` bigint(20) unsigned NOT NULL,
				`date` datetime NOT NULL,
				`processed` tinyint(1) unsigned NOT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;"
		);

		$pdo->pdo->query("
			CREATE TABLE IF NOT EXISTS `character_link_requests` (
				`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				`character_name` varchar(32) NOT NULL,
				`user_id` bigint(20) unsigned NOT NULL,
				`api_key` varchar(32) NOT NULL,
				`verification_amount` bigint(20) unsigned NOT NULL,
				`date_requested` datetime NOT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;"
		);

		$pdo->pdo->query("ALTER TABLE users ADD `balance` bigint(20) unsigned NOT NULL");

		return true;
	}

	/**
	 * Migrate the database to version 5
	 *
	 * @param PDOEngine $pdo Copy of the PDO engine returned by the DatabaseEngineFactory
	 * @param string $user username of initial user
	 * @param string $pass password of initial user
	 *
	 * @return boolean true on success
	 */
	protected function _runMigrationToVersion5($pdo, $user, $pass) {
		$pdo->pdo->query("ALTER TABLE users ADD `premium_until` datetime NOT NULL");
		$pdo->pdo->query("ALTER TABLE user_groups ADD `premium` tinyint(1) NOT NULL");

		return true;
	}

	/**
	 * Migrate the database to version 6
	 *
	 * @param PDOEngine $pdo Copy of the PDO engine returned by the DatabaseEngineFactory
	 * @param string $user username of initial user
	 * @param string $pass password of initial user
	 *
	 * @return boolean true on success
	 */
	protected function _runMigrationToVersion6($pdo, $user, $pass) {
		$pm = PermissionsModel::getInstance();
		$pm->createPermission("market", "access_market_seller_overview", "Access market seller overview");
		$pm->createPermission("market", "access_market", "Access market features");

		return true;
	}

	/**
	 * Migrate the database to version 7
	 *
	 * @param PDOEngine $pdo Copy of the PDO engine returned by the DatabaseEngineFactory
	 * @param string $user username of initial user
	 * @param string $pass password of initial user
	 *
	 * @return boolean true on success
	 */
	protected function _runMigrationToVersion7($pdo, $user, $pass) {
		$pdo->pdo->query("ALTER TABLE systems ADD `oe_star_id` int(11) unsigned NOT NULL");
		$pdo->pdo->query("ALTER TABLE systems ADD `x` int(10) unsigned NOT NULL");
		$pdo->pdo->query("ALTER TABLE systems ADD `y` int(10) unsigned NOT NULL");
		$pdo->pdo->query("ALTER TABLE system_stats ADD `hex_colour` varchar(7) NOT NULL");

		return true;
	}

	/**
	 * Migrate the database to version 8
	 *
	 * @param PDOEngine $pdo Copy of the PDO engine returned by the DatabaseEngineFactory
	 * @param string $user username of initial user
	 * @param string $pass password of initial user
	 *
	 * @return boolean true on success
	 */
	protected function _runMigrationToVersion8($pdo, $user, $pass) {
		$pdo->pdo->query("
			CREATE TABLE IF NOT EXISTS `players` (
				`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				`player_name` varchar(32) NOT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;"
		);

		return true;
	}

	/**
	 * Migrate the database to version 9
	 *
	 * @param PDOEngine $pdo Copy of the PDO engine returned by the DatabaseEngineFactory
	 * @param string $user username of initial user
	 * @param string $pass password of initial user
	 *
	 * @return boolean true on success
	 */
	protected function _runMigrationToVersion9($pdo, $user, $pass) {
		$pdo->pdo->query("ALTER TABLE players ADD `rank` varchar(16) NOT NULL");
		$pdo->pdo->query("ALTER TABLE players ADD `faction` varchar(48) NOT NULL");
		$pdo->pdo->query("ALTER TABLE players ADD `official_status` varchar(32) NOT NULL");

		$pm = PermissionsModel::getInstance();
		$pm->createPermission("players", "access_player_statistics", "Access player information and statistics");
		$pm->createPermission("players", "add_players", "Add players to database");
		$pm->createPermission("players", "edit_players", "Edit player information");
		$pm->createPermission("players", "edit_official_military_statuses", "Edit military status towards a player or faction (e.g. KoS, War, Neutral, etc)");

		return true;
	}

	/**
	 * Migrate the database to version 10
	 *
	 * @param PDOEngine $pdo Copy of the PDO engine returned by the DatabaseEngineFactory
	 * @param string $user username of initial user
	 * @param string $pass password of initial user
	 *
	 * @return boolean true on success
	 */
	protected function _runMigrationToVersion10($pdo, $user, $pass) {
		$pdo->pdo->query("
			CREATE TABLE IF NOT EXISTS `military_statuses` (
				`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				`name` varchar(32) NOT NULL,
				`hex_colour` varchar(7) NOT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;"
		);

		$q = new Query("INSERT");
		$q->intoTable("military_statuses");
		$q->intoField("name");
		$q->intoField("hex_colour");
		$q->values("(?,?)", array("Allied", "#00FF26"));
		$q->values("(?,?)", array("Peaceful", "#00D0FF"));
		$q->values("(?,?)", array("Neutral", "#FFFF00"));
		$q->values("(?,?)", array("Kill Player on Sight", "#FF0000"));
		$q->values("(?,?)", array("Kill Player/Colonies on Sight", "#FF0000"));
		$q->values("(?,?)", array("Negotiated War", "#FF0000"));
		$q->values("(?,?)", array("All Out War", "#FF0000"));
		$q->prepare()->execute();

		return true;
	}

	/**
	 * Migrate the database to version 11
	 *
	 * @param PDOEngine $pdo Copy of the PDO engine returned by the DatabaseEngineFactory
	 * @param string $user username of initial user
	 * @param string $pass password of initial user
	 *
	 * @return boolean true on success
	 */
	protected function _runMigrationToVersion11($pdo, $user, $pass) {
		$pdo->pdo->query("ALTER TABLE players DROP COLUMN`official_status`");
		$pdo->pdo->query("ALTER TABLE players ADD `official_status_id` bigint(20) unsigned NOT NULL");

		return true;
	}

	/**
	 * Migrate the database to version 12
	 *
	 * @param PDOEngine $pdo Copy of the PDO engine returned by the DatabaseEngineFactory
	 * @param string $user username of initial user
	 * @param string $pass password of initial user
	 *
	 * @return boolean true on success
	 */
	protected function _runMigrationToVersion12($pdo, $user, $pass) {
		$pm = PermissionsModel::getInstance();
		$pm->createPermission("chat", "access_chat", "Access public chat");
		$pm->createPermission("chat", "access_private_chat", "Access private chat");

		return true;
	}

	/**
	 * Migrate the database to version 13
	 *
	 * @param PDOEngine $pdo Copy of the PDO engine returned by the DatabaseEngineFactory
	 * @param string $user username of initial user
	 * @param string $pass password of initial user
	 *
	 * @return boolean true on success
	 */
	protected function _runMigrationToVersion13($pdo, $user, $pass) {
		$pdo->pdo->query("ALTER TABLE faction_bank_transactions ADD `system_id` bigint(20) unsigned NOT NULL");
		$pdo->pdo->query("ALTER TABLE faction_bank_transactions ADD `planet_numeral` varchar(4) NOT NULL");

		return true;
	}

	/**
	 * Migrate the database to version 14
	 *
	 * @param PDOEngine $pdo Copy of the PDO engine returned by the DatabaseEngineFactory
	 * @param string $user username of initial user
	 * @param string $pass password of initial user
	 *
	 * @return boolean true on success
	 */
	protected function _runMigrationToVersion14($pdo, $user, $pass) {
		$pdo->pdo->query("ALTER TABLE faction_bank_transactions ADD `description` varchar(128) NOT NULL");

		return true;
	}

	/**
	 * Migrate the database to version 15
	 *
	 * @param PDOEngine $pdo Copy of the PDO engine returned by the DatabaseEngineFactory
	 * @param string $user username of initial user
	 * @param string $pass password of initial user
	 *
	 * @return boolean true on success
	 */
	protected function _runMigrationToVersion15($pdo, $user, $pass) {
		$pm = PermissionsModel::getInstance();
		$pm->deletePermission("access_market_seller_overview");
		$pm->deletePermission("access_market");
		$pm->deletePermission("access_kos");
		$pm->createPermission("empire", "access_empire", "Access empire management");

		return true;
	}

	/**
	 * Migrate the database to version 16
	 *
	 * @param PDOEngine $pdo Copy of the PDO engine returned by the DatabaseEngineFactory
	 * @param string $user username of initial user
	 * @param string $pass password of initial user
	 *
	 * @return boolean true on success
	 */
	protected function _runMigrationToVersion16($pdo, $user, $pass) {
		$pdo->pdo->query("
			CREATE TABLE IF NOT EXISTS `colonies` (
				`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				`name` varchar(16) NOT NULL,
				`user_id` bigint(20) unsigned NOT NULL,
				`system_id` bigint(20) unsigned NOT NULL,
				`planet_numeral` varchar(4) NOT NULL,
				`moon_number` tinyint unsigned NOT NULL,
				`population` smallint unsigned NOT NULL,
				`max_population` smallint unsigned NOT NULL,
				`morale` smallint unsigned NOT NULL,
				`power` smallint unsigned NOT NULL,
				`free_power` smallint unsigned NOT NULL,
				`size` tinyint unsigned NOT NULL,
				`free_size` tinyint unsigned NOT NULL,
				`max_size` tinyint unsigned NOT NULL,
				`storage_capacity` mediumint unsigned NOT NULL,
				`displayed_size` tinyint unsigned NOT NULL,
				`primary_activity` varchar(32) NOT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;"
		);

		return true;
	}

	/**
	 * Migrate the database to version 17
	 *
	 * @param PDOEngine $pdo Copy of the PDO engine returned by the DatabaseEngineFactory
	 * @param string $user username of initial user
	 * @param string $pass password of initial user
	 *
	 * @return boolean true on success
	 */
	protected function _runMigrationToVersion17($pdo, $user, $pass) {
		$pdo->pdo->query("
			CREATE TABLE IF NOT EXISTS `stored_items` (
				`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				`user_id` bigint(20) unsigned NOT NULL,
				`location_type` varchar(8) NOT NULL,
				`colony_id` bigint(20) unsigned NOT NULL,
				`system_id` bigint(20) unsigned NOT NULL,
				`planet_numeral` varchar(4) NOT NULL,
				`ship_name` varchar(16) NOT NULL,
				`description` varchar(160) NOT NULL,
				`quantity` mediumint unsigned NOT NULL,
				`type` varchar(16) NOT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;"
		);

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
		$ugm->addUserGroup("root_admin", "Root Admin", "yes", false, $pbs);

		// Create the User user group
		$pbs = $pm->getPermissions(array(
			"access_site",
			"access_player_stats",
			"access_system_stats",
			"access_faction_stats",
			"access_locality_stats",
			"access_personal_bank",
			"access_empire")
		);
		$ugm->addUserGroup("user", "User", "no", false, $pbs);

		// Create the Member user group
		$pbs = $pm->getPermissions(array(
			"access_site",
			"access_scans",
			"submit_scans",
			"access_personal_bank",
			"access_faction_bank",
			"access_player_stats",
			"access_system_stats",
			"access_faction_stats",
			"access_locality_stats",
			"access_empire")
		);
		$ugm->addUserGroup("member", "Member", "no", false, $pbs);

		// Create the Guest user group
		$pbs = $pm->getPermissions(array(
			"access_site",
			"access_player_stats",
			"access_system_stats",
			"access_faction_stats",
			"access_locality_stats")
		);
		$ugm->addUserGroup("guest", "Guest", "no", false, $pbs);

		// Create the Banned user group
		$pbs = array();
		$ugm->addUserGroup("banned", "Banned", "no", false, $pbs);

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
