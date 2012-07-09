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
use darkcrusader\systems\exceptions\NoSuchSystemException;

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
		$q->orderby("y", "ASC");

		return SystemBean::select($q, true);
	}

	/**
	 * Get all the non government systems in a locality
	 *
	 * @param int $quadrant quadrant
	 * @param int $sector sector
	 * @param int $region region
	 * @param int $locality locality 
	 * @return array array of SystemBeans
	 */
	public function getNonGovernmentSystemsInLocality__3600_systems($quadrant, $sector, $region, $locality) {
		$systems = $this->getSystemsInLocalityCached($quadrant, $sector, $region, $locality);

		foreach($systems as $id => $system) {
			if ($system->stats->faction == "Government")
				unset($systems[$id]);
		}

		return $systems;
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
	public function generateControlledSystemsGraph__3600_systemsgraph() {
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

		$myPicture = new \pImage(700,450,$data);
		$GradientSettings = array("StartR"=>0,"StartG"=>191,"StartB"=>255,"Alpha"=>100,"Levels"=>50);
		$myPicture->drawGradientArea(0,0,700,450,DIRECTION_VERTICAL,$GradientSettings);
		$myPicture->drawText(350,45,"Breakdown of Colonised Systems",array("FontSize"=>20,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE));

		$pie = new \pPie($myPicture, $data);
		$pie->draw2DPie(350,250,array("DrawLabels"=>TRUE,"WriteValues"=>PIE_VALUE_NATURAL,"LabelStacked"=>TRUE,"Border"=>TRUE,"Radius"=>135,"ValuePosition"=>PIE_VALUE_INSIDE));
		
		$myPicture->render(__DIR__ . "/../../../graphs/controlledsystems.png"); 

	} 

	public function scrapeLocality($quadrant, $sector, $region, $locality) {
		$html = file_get_contents('http://gameview.outer-empires.com/GalaxyViewer/GView.asp?VS=1&Q=' . $quadrant . '&S=' . $sector . '&R=' . $region . '&L=' . $locality);
		
		$working = explode('<div id="GView" class="GView">', $html);
		$working = explode('<div id="dpad"', $working[1]);
		$working = explode('<div class="', $working[0]);
		unset($working[0]);
		$systemsHTMLArray = $working;

		foreach($systemsHTMLArray as $systemHTML) {
			$workingFaction = explode('<span title="', $systemHTML);
			$workingFaction = explode('"><img src="images/tiny_star.png"', $workingFaction[1]);
			$faction = $workingFaction[0];
			
			$workingSystemName = explode('" face="Verdana" size="1">', $systemHTML);
			$workingSystemName = explode('</font></span></center></div>', $workingSystemName[1]);
			$systemName = $workingSystemName[0];
			
			$workingStarID = explode('"><center><span', $systemHTML);
			$starID = str_replace("Star", "", $workingStarID[0]);

			$q = new Query("SELECT");
			$q->where("oe_star_id = ?", $starID);
			$sbs = SystemBean::select($q);

			$systemBean = ($sbs[0]) ? $sbs[0] : new SystemBean;

			$systemBean->system_name = $systemName;
			$systemBean->oe_star_id = $starID;
			$systemBean->quadrant = $quadrant;
			$systemBean->sector = $sector;
			$systemBean->region = $region;
			$systemBean->locality = $locality;
			
			if (!$sbs[0])
				$systemBean->insert();
			else
				$systemBean->update();
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

	public function scrapeSystemLocationsFromTalon() {
		$html = file_get_contents(__DIR__ . "/../../../fullmap.html");

		$working = explode("<!--div.Star1 { position:absolute; top:854; Left:5738; }-->", $html);
		$working = explode("</style>", $working[1]);
		$working = explode("\n", $working[0]);
		unset($working[0]);
		unset($working[20001]);
		unset($working[20002]);
		unset($working[20003]);
		$systemsHTMLArray = $working;

		foreach($systemsHTMLArray as $systemHTML) {

			$workingStarID = explode(" }", $systemHTML);
			$starID = str_replace("div.Star", "", $workingStarID[0]);

			$q = new Query("SELECT");
			$q->where("oe_star_id = ?", $starID);
			$sbs = SystemBean::select($q);
			$sb = $sbs[0];

			if (!$sb)
				continue;

			$workingX = explode("left:", $systemHTML);
			$workingX = explode(";", $workingX[1]);
			$x = $workingX[0];

			$workingY = explode("top:", $systemHTML);
			$workingY = explode(";", $workingY[1]);
			$y = $workingY[0];

			$sb->x = $x;
			$sb->y = $y;
			$sb->update();
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
		if ((!$id) && (!$name))
			throw new NoSuchSystemException;

		$q = new Query("SELECT");

		if ($id)
			$q->where("systems.id = ?", $id);

		if ($name)
			$q->where("system_name = ?", $name);

		$q->limit(1);
		$sbs = SystemBean::select($q);
		$sb = $sbs[0];

		if (!$sb)
			throw new NoSuchSystemException;

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
		
		// Get system stats
		$query = new Query("SELECT");
		$query->where("system_id = ?", $system);
		$query->where("stats_set = ?", $this->getLatestSystemStatsSetCached()->id);
		$systemStatsBeans = SystemStatsBean::select($query, true);
		$systemStatsBean = $systemStatsBeans[0];

		// To keep DB size down, we only store stats for systems with a non standard faction
		// and or station, if there's no stats entry for a system, just fake what a default
		// system would have
		if (!$systemStatsBean) {
			$systemStatsBean = new SystemStatsBean;
			$systemStatsBean->id = rand(100000000,100000000000000); // random id
			$systemStatsBean->hex_colour = "#ffffff";
			$systemStatsBean->has_station = 0;
			$systemStatsBean->faction = "None";
			$systemStatsBean->system_id = $system;
			$systemStatsBean->stats_set = $statsSets[0]->id;
			$systemStatsBean->system = $this->getSystem($system);
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
	public function getHistoricalSystemStats__3600_systemstats($id, $count=10) {
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

	/**
	 * Gets all the system stats beans for any systems which are owned by a faction/
	 * ibdependent/government
	 * 
	 * @return array array of SystemStatsBeans
	 */
	public function getColonisedSystemsLatestStats() {

		$q = new Query("SELECT");
		$q->where("faction != ?", "None");
		$q->where("stats_set = ?", $this->getLatestSystemStatsSetCached()->id);

		$ssbs = SystemStatsBean::select($q, true);
		return $ssbs;
	}

	/**
	 * Gets all the system stats beans for any systems which have a station
	 * 
	 * @return array array of SystemStatsBeans
	 */
	public function getStationSystemsLatestStats() {

		$q = new Query("SELECT");
		$q->where("has_station = ?", 1);
		$q->where("stats_set = ?", $this->getLatestSystemStatsSetCached()->id);

		$ssbs = SystemStatsBean::select($q, true);
		return $ssbs;
	}

	/**
	 * Gets all the system stats beans for any systems which are owned by Government
	 * 
	 * @return array array of SystemStatsBeans
	 */
	public function getGovernmentSystemsLatestStats() {

		$q = new Query("SELECT");
		$q->where("faction = ?", "Government");
		$q->where("stats_set = ?", $this->getLatestSystemStatsSetCached()->id);

		$ssbs = SystemStatsBean::select($q, true);
		return $ssbs;
	}

	/**
	 * Gets an array of SystemBeans which have, as far as we know, a station in them
	 */
	public function getStationSystems__3600_systemstats() {
		$q = new Query("SELECT");
		$q->where("has_station = ?", 1);
		$q->where("stats_set = ?", $this->getLatestSystemStatsSetCached()->id);

		$ssbs = SystemStatsBean::select($q, true);

		$sbs = array();

		foreach($ssbs as $ssb) {
			$sbs[] = $ssb->system;
		}

		return $sbs;
	}

	/**
	 * Gets the distance between 2 systems in lightyears
	 * 
	 * @param int $systemOne first system id
	 * @param int $systemTwo second system id
	 * OR
	 * @param int $systemOneX first system x coord
	 * @param int $systemOneY first system y coord
	 * @param int $systemTwoX second system x coord
	 * @param int $systemTwoY second system y coord
	 */
	public function getDistanceBetweenSystems($systemOne, $systemTwo, $systemOneX=false, $systemOneY=false, $systemTwoX=false, $systemTwoY=false) {
		if ((!$systemOneX) || (!$systemOneY)) {
			$system = $this->getSystem($systemOne);
			$systemOneX = $system->x;
			$systemOneY = $system->y;
		}

		if ((!$systemTwoX) || (!$systemTwoY)) {
			$system = $this->getSystem($systemTwo);
			$systemTwoX = $system->x;
			$systemTwoY = $system->y;
		}

		// use pyhtagoras theorem, dist = sqrt(horizontaldistance^2 + verticaldistance^2)
		$x = $systemTwoX - $systemOneX;
		$y = $systemTwoY - $systemOneY;
		$distance = sqrt(($x*$x) + ($y*$y));

		return ceil($distance);
	}

	/**
	 * Gets the closest station system to the given system
	 * 
	 * @param int $system system id
	 * OR
	 * @param int $systemX system x coord
	 * @param int $systemY system y coord
	 */
	public function getNearestStationSystemToSystem($system, $systemX, $systemY) {
		if ((!$systemX) || (!$systemY)) {
			$system = $this->getSystem($system);
			$systemX = $system->x;
			$systemY = $system->y;
		}

		// get all systems with a station
		$stationSystems = $this->getStationSystemsCached();

		$closestStationSystemDistance = 40000; // double of the entire universe lol

		foreach($stationSystems as $stationSystem) {
			$distance = $this->getDistanceBetweenSystems(false, false, $systemX, $systemY, $stationSystem->x, $stationSystem->y);

			if ($distance < $closestStationSystemDistance) {
				$closestStationSystem = $stationSystem;
				$closestStationSystemDistance = $distance;
			}
		}

		return $closestStationSystem;

	}

	/**
	 * Checks if it is possible to make it to the specified system and then to a station
	 * with your current fuel
	 * 
	 * @param string $currentSystem system currently in
	 * @param string $destinationSystem system name you want to get to
	 * @param int $fuel current amount of fuel
	 * @param int $fuelConsumptionPerLightyear jump drive fuel consumption per ly
	 * @return mixed station name if it is possible to make it to the system and then to 
	 * a station, otherwise false
	 */
	public function canPlayerMakeItThereAndToStationWithFuel($currentSystem, $destinationSystem, $fuel, $fuelConsumptionPerLightyear=2) {
		
		// work out how much fuel will be left after the player gets to the destination system
		$currentSystem = $this->getSystem(false, $currentSystem);
		$destinationSystem = $this->getSystem(false, $destinationSystem);

		$distance = $this->getDistanceBetweenSystems(false, false, $currentSystem->x, $currentSystem->y, $destinationSystem->x, $destinationSystem->y);
		$fuelRequired = $distance * $fuelConsumptionPerLightyear;

		$fuel = $fuel - $fuelRequired;

		// now work out if we can make it to a station
		$currentSystem = $destinationSystem; // we're at the destination now

		// start by getting nearest station system
		$nearestStationSystem = $this->getNearestStationSystemToSystem(false, $currentSystem->x, $currentSystem->y);

		// find the fuel required
		$distance = $this->getDistanceBetweenSystems(false, false, $currentSystem->x, $currentSystem->y, $nearestStationSystem->x, $nearestStationSystem->y);
		$fuelRequired = $distance * $fuelConsumptionPerLightyear;

		$fuel = $fuel - $fuelRequired;

		if ($fuel > -1)
			return $nearestStationSystem->system_name; // we can make it!
		else
			return false; // we can't :(

	}

		
}
?>