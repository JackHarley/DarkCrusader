<?php
if (!defined('STDIN'))
	die("This is a cron job which is run from the CLI, it cannot be accessed via the web");
error_reporting(E_ALL & ~E_NOTICE);
ini_set("display_errors", "On");

require_once(__DIR__ . '/../../hydrogen/hydrogen.inc.php');
require_once(__DIR__ . '/../darkcrusader.inc.php');

\darkcrusader\models\UserModel::getInstance()->checkValidityOfAllOnFileAccessKeys();
\darkcrusader\models\FactionResearchModel::getInstance()->updateDB();

?>