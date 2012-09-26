<?php
if (!defined('STDIN'))
	die("This is a cron job which is run from the CLI, it cannot be accessed via the web");
error_reporting(0);
ini_set("display_errors", "Off");
date_default_timezone_set("Europe/Dublin");

require_once(__DIR__ . '/../../hydrogen/hydrogen.inc.php');
require_once(__DIR__ . '/../darkcrusader.inc.php');

\darkcrusader\models\SkillsModel::getInstance()->updateDB();

?>