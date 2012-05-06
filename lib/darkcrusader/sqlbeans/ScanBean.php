<?php
/**
 * Scan Result SQLBean
 *
 * Copyright (c) 2011, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\sqlbeans;
use hydrogen\sqlbeans\SQLBean;
use hydrogen\config\Config;
use darkcrusader\models\ScanModel;

class ScanBean extends SQLBean {

	protected static $tableNoPrefix = 'scans';
	protected static $tableAlias = 'scans';
	protected static $primaryKey = 'id';
	protected static $primaryKeyIsAutoIncrement = true;
	protected static $fields = array(
		'id',
		'system_id',
		'planet_number',
		'moon_number',
		'date_submitted',
		'submitter_id',
		'scanner_level'
	);
	
	protected static $beanMap = array(
		'system' => array(
			'joinType' => 'LEFT',
			'joinBean' => 'darkcrusader\sqlbeans\SystemBean',
			'foreignKey' => 'system_id'
		),
		'submitter' => array(
			'joinType' => 'LEFT',
			'joinBean' => 'darkcrusader\sqlbeans\UserBean',
			'foreignKey' => 'submitter_id'
		),
	);

	public function get_location_string() {
		$system = $this->getMapped("system");

		$system_name = '<a href="' . Config::getVal("general", "app_url") . '/index.php/systems?name=' . $system->system_name . '">' . $system->system_name . '</a>';

		$locationString = $system_name . " " . $this->planet_number;
		
		if ($this->moon_number != 0)
			$locationString .= " M" . $this->moon_number;

		return $locationString;
	}

	public function get_system() {
		return $this->getMapped("system");
	}

	public function get_submitter() {
		return $this->getMapped("submitter");
	}

	protected $scanResults = false;
	public function get_scan_results() {
		if (!$this->scanResults)
			$this->scanResults = ScanModel::getInstance()->getScanResults($this->id);

		return $this->scanResults;
	}
}
?>