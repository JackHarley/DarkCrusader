<?php
if (!defined('STDIN'))
	die("This is a cron job which is run from the CLI, it cannot be accessed via the web");
error_reporting(E_ALL & ~E_NOTICE);
ini_set("display_errors", "On");

require_once(__DIR__ . '/../../hydrogen/hydrogen.inc.php');
require_once(__DIR__ . '/../darkcrusader.inc.php');

use darkcrusader\sqlbeans\PlayerXPStatsBean;
use darkcrusader\sqlbeans\PlayerCreditStatsBean;
use darkcrusader\sqlbeans\PlayerEmpireStatsBean;
use darkcrusader\sqlbeans\PlayerBountyStatsBean;
use darkcrusader\sqlbeans\PlayerStatsSetBean;
use hydrogen\database\Query;

$statsSet = new PlayerStatsSetBean;
$statsSet->set("time", "NOW()", true);
$statsSet->insert();

$query = new Query("SELECT");
$query->orderby("id", "DESC");
$query->limit(1);

$statsSet = PlayerStatsSetBean::select($query);
$statsSet = $statsSet[0];

echo "Scraping XP and Rank Stats...";
for($i=5;$i<1000;$i+=10) {
	$html = file_get_contents("http://gameview.outer-empires.com/Ladders/LadderRank.asp?c=0&F=0&s=" . $i);
	
	$working = explode('<table class="ladders"', $html);
	$working = explode('</table>', $working[1]);
	
	$rows = explode('<tr>', $working[0]);
	unset($rows[0]);
	unset($rows[1]);
	unset($rows[12]);
	
	foreach($rows as $id => $row) {
		$rows[$id] = str_replace(array("\n", "\r", "</tr>", "\t", "</td>"), "", $rows[$id]);
		$rows[$id] = trim($rows[$id]);
	}
	
	foreach($rows as $id => $row) {
		$rows[$id] = explode("<td >", $rows[$id]);
		$rows[$id][3] = explode("<br>", $rows[$id][3]);
	}
	
	foreach($rows as $row) {
		
		$bean = new PlayerXPStatsBean;
		$bean->player_name = trim($row[2]);
		$bean->rank = trim($row[3][0]);
		$bean->stats_set = $statsSet->id;
		$bean->leaderboard_position = trim($row[1]);
		
		if (strpos($row[3][0], "Elite A") !== false) {
			$rank = str_replace("Elite A", "", $row[3][0]);
			
			switch($rank) {
				case "1":
					$totalXP = 21832625;
				break;
				case "2":
					$totalXP = 23832625;
				break;
				case "3":
					$totalXP = 26632625;
				break;
				case "4":
					$totalXP = 30232625;
				break;
				case "5":
					$totalXP = 34632625;
				break;
				case "6":
					$totalXP = 39832625;
				break;
				case "7":
					$totalXP = 45832625;
				break;
			}
		}
		if (strpos($row[3][0], "Heavy Class E") !== false) {
			$rank = str_replace("Heavy Class E", "", $row[3][0]);
			
			switch($rank) {
				case "1":
					$totalXP = 12082625;
				break;
				case "2":
					$totalXP = 12832625;
				break;
				case "3":
					$totalXP = 13632625;
				break;
				case "4":
					$totalXP = 14482625;
				break;
				case "5":
					$totalXP = 15382625;
				break;
				case "6":
					$totalXP = 16332625;
				break;
				case "7":
					$totalXP = 17332625;
				break;
				case "8":
					$totalXP = 18382625;
				break;
				case "9":
					$totalXP = 19482625;
				break;
				case "10":
					$totalXP = 20632625;
				break;
			}
		}
		if (strpos($row[3][0], "Heavy Class D") !== false) {
			$rank = str_replace("Heavy Class D", "", $row[3][0]);
			
			switch($rank) {
				case "1":
					$totalXP = 6332625;
				break;
				case "2":
					$totalXP = 6772625;
				break;
				case "3":
					$totalXP = 7242625;
				break;
				case "4":
					$totalXP = 7742625;
				break;
				case "5":
					$totalXP = 8272625;
				break;
				case "6":
					$totalXP = 8832625;
				break;
				case "7":
					$totalXP = 9422625;
				break;
				case "8":
					$totalXP = 10042625;
				break;
				case "9":
					$totalXP = 10692625;
				break;
				case "10":
					$totalXP = 11372625;
				break;
			}
		}
		if (strpos($row[3][0], "Heavy Class C") !== false) {
			$rank = str_replace("Heavy Class C", "", $row[3][0]);
			
			switch($rank) {
				case "1":
					$totalXP = 3132625;
				break;
				case "2":
					$totalXP = 3362625;
				break;
				case "3":
					$totalXP = 3612625;
				break;
				case "4":
					$totalXP = 3882625;
				break;
				case "5":
					$totalXP = 4172625;
				break;
				case "6":
					$totalXP = 4482625;
				break;
				case "7":
					$totalXP = 4812625;
				break;
				case "8":
					$totalXP = 5162625;
				break;
				case "9":
					$totalXP = 5532625;
				break;
				case "10":
					$totalXP = 5922625;
				break;
			}
		}
		if (strpos($row[3][0], "Heavy Class B") !== false) {
			$rank = str_replace("Heavy Class B", "", $row[3][0]);
			
			switch($rank) {
				case "1":
					$totalXP = 1482625;
				break;
				case "2":
					$totalXP = 1602625;
				break;
				case "3":
					$totalXP = 1732625;
				break;
				case "4":
					$totalXP = 1872625;
				break;
				case "5":
					$totalXP = 2022625;
				break;
				case "6":
					$totalXP = 2182625;
				break;
				case "7":
					$totalXP = 2352625;
				break;
				case "8":
					$totalXP = 2532625;
				break;
				case "9":
					$totalXP = 2722625;
				break;
				case "10":
					$totalXP = 2922625;
				break;
			}
		}
		if (strpos($row[3][0], "Heavy Class A") !== false) {
			$rank = str_replace("Heavy Class A", "", $row[3][0]);
			
			switch($rank) {
				case "1":
					$totalXP = 607625;
				break;
				case "2":
					$totalXP = 672625;
				break;
				case "3":
					$totalXP = 742625;
				break;
				case "4":
					$totalXP = 817625;
				break;
				case "5":
					$totalXP = 897625;
				break;
				case "6":
					$totalXP = 982625;
				break;
				case "7":
					$totalXP = 1072625;
				break;
				case "8":
					$totalXP = 1167625;
				break;
				case "9":
					$totalXP = 1267625;
				break;
				case "10":
					$totalXP = 1372625;
				break;
			}
		}
		if (strpos($row[3][0], "Medium Class C") !== false) {
			$rank = str_replace("Medium Class C", "", $row[3][0]);
			
			switch($rank) {
				case "1":
					$totalXP = 347625;
				break;
				case "2":
					$totalXP = 391625;
				break;
				case "3":
					$totalXP = 439625;
				break;
				case "4":
					$totalXP = 491625;
				break;
				case "5":
					$totalXP = 547625;
				break;
			}
		}
		if (strpos($row[3][0], "Medium Class B") !== false) {
			$rank = str_replace("Medium Class B", "", $row[3][0]);
			
			switch($rank) {
				case "1":
					$totalXP = 177625;
				break;
				case "2":
					$totalXP = 205625;
				break;
				case "3":
					$totalXP = 236625;
				break;
				case "4":
					$totalXP = 270625;
				break;
				case "5":
					$totalXP = 307625;
				break;
			}
		}
		if (strpos($row[3][0], "Medium Class A") !== false) {
			$rank = str_replace("Medium Class A", "", $row[3][0]);
			
			switch($rank) {
				case "1":
					$totalXP = 72625;
				break;
				case "2":
					$totalXP = 89625;
				break;
				case "3":
					$totalXP = 108625;
				break;
				case "4":
					$totalXP = 129625;
				break;
				case "5":
					$totalXP = 152625;
				break;
			}
		}
		if (strpos($row[3][0], "Light Class C") !== false) {
			$rank = str_replace("Light Class C", "", $row[3][0]);
			
			switch($rank) {
				case "1":
					$totalXP = 15350;
				break;
				case "2":
					$totalXP = 21425;
				break;
				case "3":
					$totalXP = 30625;
				break;
				case "4":
					$totalXP = 43625;
				break;
				case "5":
					$totalXP = 57625;
				break;
			}
		}
		if (strpos($row[3][0], "Light Class B") !== false) {
			$rank = str_replace("Light Class B", "", $row[3][0]);
			
			switch($rank) {
				case "1":
					$totalXP = 3200;
				break;
				case "2":
					$totalXP = 4700;
				break;
				case "3":
					$totalXP = 6500;
				break;
				case "4":
					$totalXP = 8600;
				break;
				case "5":
					$totalXP = 11300;
				break;
			}
		}
		$totalXP = $totalXP + $row[3][1];			
			
		$bean->total_xp = $totalXP;
		$bean->insert();
	}
}
echo "..done!\n";

echo "Scraping credit stats...";
for($i=5;$i<1000;$i+=10) {
	$html = file_get_contents("http://gameview.outer-empires.com/Ladders/LadderRichest.asp?c=0&F=0&s=" . $i);
	
	$working = explode('<table class="ladders"', $html);
	$working = explode('</table>', $working[1]);
	
	$rows = explode('<tr>', $working[0]);
	unset($rows[0]);
	unset($rows[1]);
	unset($rows[12]);
	
	foreach($rows as $id => $row) {
		$rows[$id] = str_replace(array("\n", "\r", "</tr>", "\t", "</td>"), "", $rows[$id]);
		$rows[$id] = trim($rows[$id]);
	}
	
	foreach($rows as $id => $row) {
		$rows[$id] = explode("<td >", $rows[$id]);
	}
	
	foreach($rows as $row) {
		$bean = new PlayerCreditStatsBean;
		$bean->player_name = trim($row[2]);
		$bean->credits = trim($row[3]);
		$bean->stats_set = $statsSet->id;
		$bean->leaderboard_position = trim($row[1]);			
		$bean->insert();
	}
}
echo "..done!\n";

echo "Scraping empire stats...";
for($i=5;$i<1000;$i+=10) {
	$html = file_get_contents("http://gameview.outer-empires.com/Ladders/LadderEmpire.asp?c=0&F=0&s=" . $i);
	
	$working = explode('<table class="ladders"', $html);
	$working = explode('</table>', $working[1]);
	
	$rows = explode('<tr>', $working[0]);
	unset($rows[0]);
	unset($rows[1]);
	unset($rows[12]);
	
	foreach($rows as $id => $row) {
		$rows[$id] = str_replace(array("\n", "\r", "</tr>", "\t", "</td>"), "", $rows[$id]);
		$rows[$id] = trim($rows[$id]);
	}
	
	foreach($rows as $id => $row) {
		$rows[$id] = explode("<td >", $rows[$id]);
	}
		
	foreach($rows as $row) {
		if ((!$row[1]) || (!$row[2]))
			break 2;
	
		$bean = new PlayerEmpireStatsBean;
		$bean->player_name = trim($row[2]);
		$bean->colonies = trim($row[3]);
		$bean->population = trim($row[4]);
		$bean->stats_set = $statsSet->id;
		$bean->leaderboard_position = trim($row[1]);			
		$bean->insert();
	}
}
echo "..done!\n";

echo "Scraping bounty stats...";
for($i=5;$i<1000;$i+=10) {
	$html = file_get_contents("http://gameview.outer-empires.com/Ladders/LadderBounty.asp?c=0&F=0&s=" . $i);
	
	$working = explode('<table class="ladders"', $html);
	$working = explode('</table>', $working[1]);
	
	$rows = explode('<tr bgcolor="red">', $working[0]);
	unset($rows[0]);
	unset($rows[12]);
	
	foreach($rows as $id => $row) {
		$rows[$id] = str_replace(array("\n", "\r", "</tr>", "\t", "</td>"), "", $rows[$id]);
		$rows[$id] = trim($rows[$id]);
	}
	
	foreach($rows as $id => $row) {
		$rows[$id] = explode("<td >", $rows[$id]);
	}
		
	foreach($rows as $row) {
		if ((!$row[1]) || (!$row[2]))
			break 2;
	
		$bean = new PlayerBountyStatsBean;
		$bean->player_name = trim($row[2]);
		$bean->bounty = trim($row[3]);
		$bean->stats_set = $statsSet->id;
		$bean->leaderboard_position = trim($row[1]);			
		$bean->insert();
	}
}
echo "..done!\n";

echo "\nAll stats scraped successfully\n";
?>