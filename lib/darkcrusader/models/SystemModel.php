<?php
/**
 * System Model
 * Handles data requests regarding systems
 *
 * Copyright (c) 2012, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\models;

use hydrogen\model\Model;

use darkcrusader\models\UserModel;

use hydrogen\database\Query;
use darkcrusader\sqlbeans\ScanBean;
use darkcrusader\sqlbeans\ScanResultBean;
use darkcrusader\sqlbeans\SystemBean;
use darkcrusader\sqlbeans\SystemStatsBean;
use darkcrusader\sqlbeans\SystemStatsSetBean;

class SystemModel extends Model {
	
	protected static $modelID = "Sys";
	
	/**
	 * Gets the number of scanned objects in a system, if $user is specified,
	 * only gets number of objects scanned by that user
	 * 
	 * @param int $system system id
	 * @param int $user user id, optional
	 * @return int number of objects (planets/moons) scanned in system
	 */
	public function getNumberOfScannedObjectsInSystem($system, $user=false) {
		$q = new Query("SELECT");
		$q->where("system_id = ?", $system);

		if ($user)
			$q->where("submitter_id = ?", $user);

		$sbs = ScanBean::select($q);
		$objectsScanned = array();

		foreach($sbs as $sb) {
			$object = $sb->planet_number . $sb->moon_number;

			if (!in_array($object, $objectsScanned))
				$objectsScanned[] = $object;
		}

		return count($objectsScanned);
	}

	/**
	 * Get systems in a locality
	 *
	 * @param int $quadrant quadrant
	 * @param int $sector sector
	 * @param int $region region
	 * @param int $locality locality 
	 * @return array array of SystemBeans
	 */
	public function getSystemsInLocality__3600_systems($quadrant, $sector, $region, $locality) {
		$q = new Query("SELECT");
		$q->where("quadrant = ?", $quadrant);
		$q->where("sector = ?", $sector);
		$q->where("region = ?", $region);
		$q->where("locality = ?", $locality);
		$q->orderby("system_name", "ASC");

		return SystemBean::select($q, true);
	}

	/**
	 * Gets all the systems in a locality and appends two properties to each
	 * SystemBean, "objects_scanned" and "objects_scanned_by_active_user"
	 * 
	 * @param int $quadrant quadrant
	 * @param int $sector sector
	 * @param int $region region
	 * @param int $locality locality
	 * @param int $user submitter id, optional
	 * @return array array of SystemBeans
	 */
	public function getSystemsInLocalityWithScanStats__3600_systemscans($quadrant, $sector, $region, $locality, $user=false) {
		
		// complex query that i coded ages ago, ill work out what it does later...
		$q = new Query("SELECT");
		$q->from("systems");
		$q->field("systems.system_name");
		$q->field("systems.id");
		$q->field("scans.planet_number");
		$q->field("scans.moon_number");
		$q->field("scans.submitter_id");
		$q->where("systems.quadrant = ?", $quadrant);
		$q->where("systems.sector = ?", $sector);
		$q->where("systems.region = ?", $region);
		$q->where("systems.locality = ?", $locality);
		$q->join("scans", "scans", "LEFT");
		$q->on("systems.id = scans.system_id");
		$stmt = $q->prepare();
		$stmt->execute();
		
		$results = array();
		while($result = $stmt->fetchObject())
			$results[] = $result;
		
		$systems = $this->getSystemsInLocalityCached($quadrant, $sector, $region, $locality); 

		foreach($systems as $key => $system) {
			foreach($results as $result) {
				if (($result->id == $system->id) && ($result->planet_number)) {
					
					// just increment the systems objects count for each planet/moon
					$objectsScanned = array();
					$objectsScannedByUser = array();

					$object = $result->planet_number . $result->moon_number;

					if (!in_array($object, $objectsScanned)) {
						$objectsScanned[] = $object;
						$systems[$key]->objects_scanned++;
					}

					if ((!in_array($object, $objectsScannedByUser)) && ($user == $result->submitter_id)) {
						$objectsScannedByUser[] = $object;
						$systems[$key]->objects_scanned_by_user++;
					}
				}
			}
		}

		return $systems;
	}
	
	/**
	 * Get number of systems in a locality which there is at least one scan for in the db
	 * If the user parameter is specified, it will give the number of systems which there is
	 * at least one scan for in the db submitted by that user
	 * 
	 * @param int $quadrant quadrant
	 * @param int $sector sector
	 * @param int $region region
	 * @param int $locality locality
	 * @param int $user submitter id, optional
	 * @return int number of systems
	 */
	public function getNumberOfSystemsInLocalityWithAtLeastOneScan__3600_systemscans($quadrant, $sector, $region, $locality, $user=false) {
		$systems = $this->getSystemsInLocalityWithScanStatsCached($quadrant, $sector, $region, $locality, $user);

		$systemsNumber = 0;
		$userSystems = 0;
		foreach($systems as $system) {
			if ($system->objects_scanned > 0)
				$systemsNumber++;
			if ($system->objects_scanned_by_user > 0)
				$userSystems++;
		}

		if ($user)
			return $userSystems;
		else
			return $systemsNumber;
	}

	/**
	 * Gets the number of systems in a locality
	 * 
	 * @param int $quadrant quadrant
	 * @param int $sector sector
	 * @param int $region region
	 * @param int $locality locality
	 * @return int number of systems
	 */ 
	public function getNumberOfSystemsInLocality__3600_systems($quadrant, $sector, $region, $locality) {
		$q = new Query("SELECT");
		$q->where("quadrant = ?", $quadrant);
		$q->where("sector = ?", $sector);
		$q->where("region = ?", $region);
		$q->where("locality = ?", $locality);

		return count(SystemBean::select($q));
	}
	
	/**
	 * Gets an associative array of factions and the amount of systems they control
	 * ordered by the amount of systems they control
	 * NOTE: "Colonised" is converted to "Independent"
	 * 
	 * @return array Faction Name => Number of Controlled Systems
	 */
	public function getFactionsWithNumberOfControlledSystems__5200_systems() {
		$q = new Query("SELECT");
		$q->where("stats_set = ?", $this->getLatestSystemStatsSetCached()->id);
		$q->where("faction != ?", "None");

		$ssbs = SystemStatsBean::select($q);

		$factions = array();

		foreach($ssbs as $ssb) {

			// convert Colonised to Independent (more descriptive)
			if ($ssb->faction == "Colonised")
				$ssb->faction = "Independent";

			// init factions array key if none exists
			if (!$factions[$ssb->faction])
				$factions[$ssb->faction] = 0;

			// increment number of systems
			$factions[$ssb->faction]++;
		}

		// sort factions by number of controlled systems
		$orderedFactions = array();
		while (count($factions) > 0) {
			$best = array("faction" => null, "systems" => 0);

			foreach($factions as $faction => $systems) {
				if ($systems > $best["systems"]) {
					$best["faction"] = $faction;
					$best["systems"] = $systems;
				}

			}

			$orderedFactions[] = $best;
			unset($factions[$best["faction"]]);
		}

		// remove any factions with less than 5% controlled and aggregrate them into "Other"
		$colonisedSystems = count($ssbs);
		$other = array("faction" => "Other Factions", "systems" => 0);
		foreach($orderedFactions as $id => $faction) {
			$percentage = ($faction["systems"] / $colonisedSystems) * 100;
			if ($percentage < 5) {
				$other["systems"] += $faction["systems"];
				unset($orderedFactions[$id]);
			}

		}
		if ($other["systems"] > 0)
			$orderedFactions[] = $other;

		return $orderedFactions;

	}

	/**
	 * Generates the controlled systems pie chart/ring chart and stores it /graphs/controlledsystems.png
	 */
	public function generateControlledSystemsGraph() {
		$bestFactions = $this->getFactionsWithNumberOfControlledSystemsCached();

		$systems = array();
		$factions = array();
		foreach($bestFactions as $faction) {
			$systems[] = $faction["systems"];
			$factions[] = $faction["faction"];
		}

		$data = new \pData;
		$data->addPoints($systems, "Systems");
		$data->addPoints($factions, "Factions");
		$data->setSerieDescription("Factions", "Factions");
		$data->setSerieDescription("Systems", "Systems");
		$data->setAbscissa("Factions");

		$myPicture = new \pImage(800,450,$data);
		$GradientSettings = array("StartR"=>0,"StartG"=>191,"StartB"=>255,"Alpha"=>100,"Levels"=>50);
		$myPicture->drawGradientArea(0,0,800,450,DIRECTION_VERTICAL,$GradientSettings);
		$myPicture->drawText(400,45,"Breakdown of Colonised Systems",array("FontSize"=>20,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE));

		$pie = new \pPie($myPicture, $data);
		$pie->draw2DPie(400,250,array("DrawLabels"=>TRUE,"WriteValues"=>PIE_VALUE_NATURAL,"LabelStacked"=>TRUE,"Border"=>TRUE,"Radius"=>135,"ValuePosition"=>PIE_VALUE_INSIDE));
		
		$myPicture->render(__DIR__ . "/../../../graphs/controlledsystems.png"); 

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
		$sb = $sbs[0];

		return $sb;
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
		$systemStatsBean = $systemStatsBeans[0];

		// To keep DB size down, we only store stats for systems with a non standard faction
		// and or station, if there's no stats entry for a system, just fake what a default
		// system would have
		if (!$systemStatsBean) {
			$systemStatsBean = new SystemStatsBean;
			$systemStatsBean->has_station = 0;
			$systemStatsBean->faction = "None";
			$systemStatsBean->system_id = $system;
			$systemStatsBean->stats_set = $statsSets[0]->id;
		}

		return $systemStatsBean;
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