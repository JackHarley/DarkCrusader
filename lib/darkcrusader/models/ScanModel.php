<?php
/**
 * Scan Model
 * Handles data requests regarding the
 * scans database
 *
 * Copyright (c) 2011, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\models;

use hydrogen\model\Model;

use hydrogen\database\Query;
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

		$planet = explode("Scan Results for : Scan : ", $line[0]);
		$planet = explode("|", $planet[1]);
		$planet = $planet[0];
		$planet = explode(" ", $planet);
		foreach($planet as $key => $val)
			if (!$val)
				unset($planet[$key]);

		$numberOfWords = count($planet);

		if ($numberOfWords == 2) {
			$system = $planet[0];
			$planetid = $planet[1];
			$moonid = 0;
		}
		else if ($numberOfWords == 4) {
			$system = $planet[0] . " " . $planet[1];
			$planetid = $planet[2];
			$moonid = $planet[3];
			$moonid = str_replace("M", "", $moonid);
			$moonid = intval($moonid);
		}
		else if ($numberOfWords == 3) {
			if (strpos($planet[2], "M") !== false) {
				$system = $planet[0];
				$planetid = $planet[1];
				$moonid = $planet[2];
				$moonid = str_replace("M", "", $moonid);
				$moonid = intval($moonid);
			}
			else {
				$system = $planet[0] . " " . $planet[1];
				$planetid = $planet[2];
				$moonid = 0;
			}
		}
		
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
			$resource = explode(" ", $line[$i]);
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
				
			if ($quality == "") {
				$dbquality = "na";
			}
			else if ($quality == "Low Quality") {
				$dbquality = "low";
			}
			else if ($quality == "Medium Quality") {
				$dbquality = "medium";
			}
			else if ($quality == "Good Quality") {
				$dbquality = "good";
			}

			$this->addScanResult($scanId, $resource, $dbquality, $rate);					  
		}

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
}
?>