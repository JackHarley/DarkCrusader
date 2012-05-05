<?php
/**
 * System Model
 * Handles data requests regarding systems
 *
 * Copyright (c) 2011, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\models;

use hydrogen\model\Model;

use darkcrusader\models\UserModel;

use hydrogen\database\Query;
use darkcrusader\sqlbeans\ScanResultBean;
use darkcrusader\sqlbeans\SystemBean;
use darkcrusader\sqlbeans\SystemStatsBean;
use darkcrusader\sqlbeans\SystemStatsSetBean;

class SystemModel extends Model {
	
	protected static $modelID = "Sys";
	
	public function getLocalityInformation($quadrant, $sector, $region, $locality) {
		$UserModel = UserModel::getInstance();
		$user = $UserModel->getLoggedInUser();
		
		$return = array();
		$return["location"] = $quadrant . ':' . $sector . ':' . $region . ':' . $locality;
		$return["systems"] = array();
		
		$query = new Query("SELECT");
		$query->from("systems");
		$query->field("systems.system_name");
		$query->field("scan_results.planet_number");
		$query->field("scan_results.moon_number");
		$query->field("scan_results.submitter_id");
		$query->where("systems.quadrant = ?", $quadrant);
		$query->where("systems.sector = ?", $sector);
		$query->where("systems.region = ?", $region);
		$query->where("systems.locality = ?", $locality);
		$query->join("scan_results", "scan_results", "LEFT");
		$query->on("systems.id = scan_results.system_id");
		$stmt = $query->prepare();
		$stmt->execute();
		
		while($result = $stmt->fetchObject()) {
			if ($result->planet_number) {
				$return["systems"][$result->system_name]["planetsScanned"]++;
				
				if ($user->id == $result->submitter_id)
					$return["systems"][$result->system_name]["planetsScannedByUser"]++;
				else
					if (!$return["systems"][$result->system_name]["planetsScannedByUser"])
						$return["systems"][$result->system_name]["planetsScannedByUser"] = 0;
			}
			else {
				$return["systems"][$result->system_name]["planetsScanned"] = 0;
				$return["systems"][$result->system_name]["planetsScannedByUser"] = 0;
			}
		}
		
		return $return;
	}
			
		
	public function scrapeLocality($quadrant, $sector, $region, $locality) {
		$html = file_get_contents('http://gameview.outer-empires.com/GalaxyViewer/GView.asp?VS=1&Q=' . $quadrant . '&S=' . $sector . '&R=' . $region . '&L=' . $locality);
		
		$working = explode('<div id="GView" class="GView">', $html);
		$working = explode('<div id="dpad"', $working[1]);
		$working = explode('<center>', $working[0]);
		unset($working[0]);
		$systemsHTMLArray = $working;
		
		foreach($systemsHTMLArray as $systemHTML) {
			$workingFaction = explode('<span title="', $systemHTML);
			$workingFaction = explode('"><img src="images/tiny_star.png"', $workingFaction[1]);
			$faction = $workingFaction[0];
			
			$workingSystemName = explode('" face="Verdana" size="1">', $systemHTML);
			$workingSystemName = explode('</font></span></center></div>', $workingSystemName[1]);
			$systemName = $workingSystemName[0];
			
			$systemBean = new SystemBean;
			$systemBean->system_name = $systemName;
			$systemBean->quadrant = $quadrant;
			$systemBean->sector = $sector;
			$systemBean->region = $region;
			$systemBean->locality = $locality;
			$systemBean->insert();
		}
	}
	
	public function scrapeAllLocalities() {
		for($q=1;$q<5;$q++) {
			for($s=1;$s<5;$s++) {
				for($r=1;$r<5;$r++) {
					for($l=1;$l<5;$l++) {
						$this->scrapeLocality($q, $s, $r, $l);
					}
				}
			}
		}
	}
	
	/**
	 * Gets a system and returns it
	 * 
	 * @param int $id system id
	 * @param string $name system name
	 * @return SystemBean system info
	 */
	public function getSystem($id=false, $name=false) {
		$q = new Query("SELECT");

		if ($id)
			$q->where("systems.id = ?", $id);

		if ($name)
			$q->where("system_name = ?", $name);

		$q->limit(1);
		$sbs = SystemBean::select($q);
		return $sbs[0];
	}

	/**
	 * Gets the latest system stats set for the system and returns it
	 * as a SystemStatsBean
	 * 
	 * @param int $system system id
	 * @return SystemStatsBean system stats
	 */
	public function getSystemStats($system) {
		
		// Get latest stats set
		$query = new Query("SELECT");
		$query->orderby("time", "DESC");
		$query->limit(1);
		$statsSets = SystemStatsSetBean::select($query);
		
		// Get system stats
		$query = new Query("SELECT");
		$query->where("system_id = ?", $system);
		$query->where("stats_set = ?", $statsSets[0]->id);
		$systemStatsBeans = SystemStatsBean::select($query);
		
		return $systemStatsBeans[0];
	}

	/**
	 * Gets an array of system stats beans and returns them, useful for
	 * historical info
	 * 
	 * @param int $id system id
	 * @param int $count number of stats sets to get
	 * @return array array of SystemStatsBeans
	 */
	public function getHistoricalSystemStats($id, $count=10) {
		$q = new Query("SELECT");
		$q->where("system_id = ?", $id);
		$q->orderby("stats_set", "DESC");
		$q->limit($count);
		$stbs = SystemStatsBean::select($q, true);

		return $stbs;
	}

	/**
	 * Gets the latest system stats set
	 * 
	 * @return SystemStatsSetBean latest stats set
	 */
	public function getLatestSystemStatsSet__3600_systemstats() {
		
		// Get latest stats set
		$query = new Query("SELECT");
		$query->orderby("time", "DESC");
		$query->limit(1);
		$latestSystemStatsSets = SystemStatsSetBean::select($query);
		$latestSystemStatsSet = $latestSystemStatsSets[0];

		return $latestSystemStatsSet;
	}
		
}
?>