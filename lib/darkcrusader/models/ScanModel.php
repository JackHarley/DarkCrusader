<?php
/**
 * Scan Model
 * Handles data requests regarding the
 * scans database
 *
 * Copyright (c) 2012, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\models;

use hydrogen\model\Model;

use hydrogen\database\Query;
use hydrogen\recache\RECacheManager;

use darkcrusader\sqlbeans\ScanResultBean;
use darkcrusader\sqlbeans\ScanBean;
use darkcrusader\sqlbeans\SystemBean;

use darkcrusader\models\UserModel;
use darkcrusader\models\SystemModel;

class ScanModel extends Model{
	
	protected static $modelID = "Scan";
	
	/**
	 * Adds a scan to the database
	 * 
	 * @param int $system system id
	 * @param string $planetNumber planet number as roman numerals (IV, V, VI, etc)
	 * @param int $submitter submitter user id
	 * @param int $scannerLevel level of planetary scans
	 * @return int scan id inserted
	 */
	public function addScan($system, $planetNumber, $moonNumber, $submitter, $scannerLevel) {
		$sb = new ScanBean;
		$sb->system_id = $system;
		$sb->planet_number = $planetNumber;
		$sb->moon_number = $moonNumber;
		$sb->submitter_id = $submitter;
		$sb->scanner_level = $scannerLevel;
		$sb->set("date_submitted", "NOW()", true);
		$sb->insert();

		$q = new Query("SELECT");
		$q->where("system_id = ?", $system);
		$q->where("planet_number = ?", $planetNumber);
		$q->orderby("id", "DESC");
		$sbs = ScanBean::select($q);
		return $sbs[0]->id;
	}

	/**
	 * Adds a scan result
	 * 
	 * @param int $scan scan id
	 * @param string $resourceName resource name, e.g. Hutsonium Ore
	 * @param string $resourceQuality resource quality, e.g. low, medium, high
	 * @param string $resourceExtractionRate resource extraction rate, e.g. 5
	 */
	public function addScanResult($scan, $resourceName, $resourceQuality, $resourceExtractionRate) {
		$srb = new ScanResultBean;
		$srb->scan_id = $scan;
		$srb->resource_name = $resourceName;
		$srb->resource_quality = $resourceQuality;
		$srb->resource_extraction_rate = $resourceExtractionRate;
		$srb->insert();
	}
	
	/**
	 * Gets the latest scans from the db
	 * 
	 * @return array array of ScanBeans
	 */
	public function getLatestScans($amount) {
		$query = new Query("SELECT");
		$query->orderby("scans.id", "DESC");
		$query->limit($amount);
		
		$scans = ScanBean::select($query, true);
		return $scans;
	}
	
	/**
	 * Gets a scan by id
	 * 
	 * @param int $id scan id
	 * @return ScanBean scan
	 */
	public function getScan($id) {
		$q = new Query("SELECT");
		$q->where("scans.id = ?", $id);
		$sbs = ScanBean::select($q, true);
		return $sbs[0];
	}

	/**
	 * Gets scan results for a scan
	 * 
	 * @param int $scan scan id
	 * @return array array of ScanResultBeans
	 */
	public function getScanResults($scan) {
		$q = new Query("SELECT");
		$q->where("scan_id = ?", $scan);
		$srbs = ScanResultBean::select($q, true);
		return $srbs;
	}

	/**
	 * Adds a scan paste and then returns the submitted scan as a ScanBean
	 * 
	 * @param string $scanPaste pasted content to parse
	 * @param int $scannerLevel total planetary scans rating of scanners used to get scan
	 * @return ScanBean the scan that was just submitted
	 */
	public function addScanPaste($scanPaste) {
		
		$line = explode("\n", $scanPaste);
		
		// find the scan rating
		$workingScanRating = explode("Scan Rating ", $line[0]);
		$scanRating = $workingScanRating[1];
		$scanRating = floatval($scanRating);

		// split off everything except the planet/moon string (sss ss p Mm) s=system, p=planet, m=moon
		$planet = explode("Scan Results for : Scan : ", $line[0]);
		$planet = explode("|", $planet[1]);
		$planet = $planet[0];
		$planet = explode(" ", $planet);
		foreach($planet as $key => $val)
			if (!$val)
				unset($planet[$key]);

		// we're going to knock off the last term each time until we have only the system name left, and
		// the planet numeral, and if applicable moon number, stored
		$numberOfWords = count($planet);

		// check if last word has an M (this is a moon if it does)
		$lastWord = $planet[($numberOfWords - 1)];
		if (strpos($lastWord, "M") !== false) {
			$moonid = intval(str_replace("M", "", $lastWord));
			unset($planet[($numberOfWords - 1)]);
			$numberOfWords--;
		}

		// last word must now be the planet numeral
		$lastWord = $planet[($numberOfWords - 1)];
		$planetid = $lastWord;
		unset($planet[($numberOfWords - 1)]);
		$numberOfWords--;

		// we're left with the system name
		$system = "";
		foreach($planet as $word) {
			$system .= $word . " ";
		}
		$system = trim($system);
		
		if (!$moonid)
			$moonid = 0;
		
		$scanId = $this->addScan(
			SystemModel::getInstance()->getSystem(false, $system)->id,
			$planetid,
			$moonid,
			UserModel::getInstance()->getActiveUser()->id,
			$scanRating
		);

		for ($i=2; $line[$i] != ""; $i++) {	
			$resource = explode(" ", trim($line[$i]));
			$resource[0] = str_replace("\t", "", $resource[0]);
			$resource[0] = str_replace(",", "", $resource[0]);
			
			if ($resource[1] == "Ore,") {
				$resource = $resource[0] . " Ore";
			}
			else {
				$resource = $resource[0];
			}
				
			$quality = explode(", ", $line[$i]);
			$quality = explode(" (", $quality[1]);
			$quality = $quality[0];
				
			$rate = explode("(", $line[$i]);
			$rate = explode("/", $rate[1]);
			$rate = $rate[0];
				
			$system = $system;
			$resource = $resource;
			
			switch ($quality) {
				case "":
					$dbquality = "na";
				break;
				case "Low Quality":
					$dbquality = "low";
				break;
				case "Medium Quality":
					$dbquality = "medium";
				break;
				case "Good Quality":
					$dbquality = "good";
				break;
			}

			$this->addScanResult($scanId, $resource, $dbquality, $rate);					  
		}

		RECacheManager::getInstance()->clearGroup("systemscans");

		return $this->getScan($scanId);
	}

	/**
	 * Get scans for a system
	 * 
	 * @param int $system system id
	 * @return array array of ScanBeans
	 */
	public function getScansForSystem($system) {
		$q = new Query("SELECT");
		$q->where("system_id = ?", $system);
		$q->orderby("planet_number");
		$q->orderby("moon_number");
		$q->orderby("scanner_level", "DESC");

		return ScanBean::select($q, true);
	}

	/**
	 * Searches scans by resource and orders by quality and then rate
	 * 
	 * @param string $resource resource name to search
	 * @return array array of ScanResultBeans
	 */
	public function searchScansByResource($resource) {
		$scans = array();
		$qualities = array("good", "medium", "low", "na");

		foreach($qualities as $quality) {
			$q = new Query("SELECT");
			$q->where("resource_name LIKE ?", '%' . $resource . '%');
			$q->orderby("resource_extraction_rate", "DESC");
			$q->where("resource_quality = ?", $quality);
			$scanBatch = ScanResultBean::select($q, true);
			foreach($scanBatch as $scan)
				$scans[] = $scan;
		}

		return $scans;
	}

	public function createScanningRouteForLocality($q, $s, $r, $l, $startLocation, $fuelCapacity, $fuelConsumptionPerLightyear, $displaySystemsAlreadyScannedByUser=false, $displaySystemsAlreadyScanned=true) {
		$systems = SystemModel::getInstance()->getNonGovernmentSystemsInLocality($q, $s, $r, $l);

		if (!$displaySystemsAlreadyScannedByUser) {
			$user = UserModel::getInstance()->getActiveUser();

			foreach($systems as $id => $system) {
				$scans = $system->scans;

				foreach($scans as $scan) {
					if ($scan->submitter_id == $user->id) {
						unset($systems[$id]);
					}
				}
			}
		}

		if (!$displaySystemsAlreadyScanned) {
			foreach($systems as $id => $system) {
				$scans = $system->scans;

				foreach($scans as $scan) {
					if ($scan->id) {
						unset($systems[$id]);
					}
				}
			}
		}

		$fuelInLightyears = $fuelCapacity / $fuelConsumptionPerLightyear;

		$sm = SystemModel::getInstance();

		$startSystem = $sm->getSystem(false, $startLocation);
		$currentLocation = $startSystem;

		$instructions = array();

		// now write instructions for the route
		foreach ($systems as $nextSystem) {

			$fuelInLightyearsAfterJump = $fuelInLightyears - $sm->getDistanceBetweenSystems(false, false, $currentLocation->x, $currentLocation->y, $nextSystem->x, $nextSystem->y);

			$nearestStationSystemToNextSystem = $sm->getNearestStationSystemToSystem(false, $nextSystem->x, $nextSystem->y);
			$fuelNecessaryToGetToClosestStation = $sm->getDistanceBetweenSystems(false, false, $nextSystem->x, $nextSystem->y, $nearestStationSystemToNextSystem->x, $nearestStationSystemToNextSystem->y);

			if ($fuelInLightyearsAfterJump > $fuelNecessaryToGetToClosestStation) {
				$currentLocation = $nextSystem;
				$fuelInLightyears = $fuelInLightyearsAfterJump;
				$instructions[] = "Jump to " . $nextSystem->system_name . " and scan all systems there (" . $fuelInLightyears * $fuelConsumptionPerLightyear . " fuel remaining)";
			}
			else {
				$nearestStationSystem = $sm->getNearestStationSystemToSystem(false, $currentLocation->x, $currentLocation->y);
				$currentLocation = $nearestStationSystem;
				$fuelInLightyears = $fuelCapacity / $fuelConsumptionPerLightyear; // refuel
				$instructions[] = "Refuel at " . $nearestStationSystem->system_name . " (" . $fuelInLightyears * $fuelConsumptionPerLightyear . " fuel remaining)";

				$fuelInLightyearsAfterJump = $fuelInLightyears - $sm->getDistanceBetweenSystems(false, false, $currentLocation->x, $currentLocation->y, $nextSystem->x, $nextSystem->y);
				$currentLocation = $nextSystem;
				$fuelInLightyears = $fuelInLightyearsAfterJump;
				$instructions[] = "Jump to " . $nextSystem->system_name . " and scan all systems there (" . $fuelInLightyears * $fuelConsumptionPerLightyear . " fuel remaining)";
			}

		}

		return $instructions;
	}

	/**
	 * Gets scans with good 10 resources (not water/food)
	 * 
	 * @return array ScanBeans
	 */
	public function getScansWithGood10Resources() {
		$q = new Query("SELECT");
		$q->whereOpenGroup();
		$q->where("resource_quality = ?", "Good");
		$q->where("resource_quality = ?", "na", "OR");
		$q->whereCloseGroup();
		$q->where("resource_extraction_rate = ?", 10, "AND");
		$q->where("resource_name != ?", "Food");
		$q->where("resource_name != ?", "Water");

		$srbs = ScanResultBean::select($q, true);

		$sbs = array();
		foreach($srbs as $srb) {
			$sbs[] = $srb->scan;
		}

		unset($sbs[0]);

		return $sbs;
	}

}
?>