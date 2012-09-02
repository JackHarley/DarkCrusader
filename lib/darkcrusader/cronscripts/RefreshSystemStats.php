<?php
if (!defined('STDIN'))
	die("This is a cron job which is run from the CLI, it cannot be accessed via the web");
ini_set("display_errors", "Off");
date_default_timezone_set("Europe/Dublin");
error_reporting(0);

require_once(__DIR__ . '/../../hydrogen/hydrogen.inc.php');
require_once(__DIR__ . '/../darkcrusader.inc.php');

use darkcrusader\sqlbeans\SystemStatsBean;
use darkcrusader\sqlbeans\SystemStatsSetBean;
use darkcrusader\sqlbeans\SystemBean;
use darkcrusader\models\SystemModel;
use hydrogen\recache\RECacheManager;
use hydrogen\database\Query;

$q = new Query("SELECT");
$systems = SystemBean::select($q);
if ((count($systems) < 20000) || (!$systems[0]->oe_star_id)) {
	echo "Doing first time scrape...";
	SystemModel::getInstance()->scrapeAllLocalities();
	echo "done!\n";
}

if ((!$systems[0]->x) || (!$systems[0]->y)) {
	echo "Getting all system locations...";
	SystemModel::getInstance()->scrapeSystemLocationsFromTalon();
	echo "done!\n";
}

$statsSet = new SystemStatsSetBean;
$statsSet->set("time", "NOW()", true);
$statsSet->insert();

$query = new Query("SELECT");
$query->orderby("id", "DESC");
$query->limit(1);

$statsSet = SystemStatsSetBean::select($query);
$statsSet = $statsSet[0];

echo "Scraping System Stats...\n\n";

sleep(1);

for($q=1;$q<5;$q++) {
	for($s=1;$s<5;$s++) {
		for($r=1;$r<5;$r++) {
			for($l=1;$l<5;$l++) {
				$html = file_get_contents('http://gameview.outer-empires.com/GalaxyViewer/Gview.asp?VS=1&SystemID=0&Q=' . $q . '&S=' . $s . '&R=' . $r . '&L=' . $l);
				echo "Fetching " . $q . ":" . $s . ":" . $r . ":" . $l . "...\n";

				$working = explode('<div id="GView" class="GView">', $html);
				$working = explode('<div id="dpad"', $working[1]);
				$working = explode('<center>', $working[0]);
				unset($working[0]);
				$systemsHTMLArray = $working;
				
				foreach($systemsHTMLArray as $systemHTML) {
					$workingFaction = explode('<span title="', $systemHTML);
					if (strpos($workingFaction[1], "tiny_station") !== false) {
						$workingFaction = explode('"><img src="', $workingFaction[1]);
						$hasStation = 1;
					}
					else {
						$workingFaction = explode('"><img src="', $workingFaction[1]);
						$hasStation = 0;
					}
					
					$faction = $workingFaction[0];
					$workingSystemName = explode('" face="Verdana" size="1">', $systemHTML);
					$workingSystemName = explode('</font></span></center></div>', $workingSystemName[1]);
					$systemName = $workingSystemName[0];
					
					$workingColour = explode('<font color="', $systemHTML);
					$workingColour = explode('" face=', $workingColour[1]);
					$colour = $workingColour[0];

					$query = new Query("SELECT");
					$query->where("system_name = ?", $systemName);
					$systemBeans = SystemBean::select($query);
					$systemBean = $systemBeans[0];
					
					if (($faction != "None") || ($hasStation != 0)) {
						$systemStatsBean = new SystemStatsBean;
						$systemStatsBean->system_id = $systemBean->id;
						$systemStatsBean->stats_set = $statsSet->id;
						$systemStatsBean->faction = $faction;
						$systemStatsBean->has_station = $hasStation;
						$systemStatsBean->hex_colour = $colour;
						$systemStatsBean->insert();
					}
				}

				echo "done!\n";
			}
		}
	}
}

// clear cache
echo "\nClearing cache...";
RECacheManager::getInstance()->clearAll();
echo "done!\n";

echo "\nAll stats scraped successfully!\n";
?>