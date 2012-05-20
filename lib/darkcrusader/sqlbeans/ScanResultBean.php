<?php
/**
 * Scan Result SQLBean
 *
 * Copyright (c) 2012, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\sqlbeans;
use hydrogen\sqlbeans\SQLBean;

class ScanResultBean extends SQLBean {

	protected static $tableNoPrefix = 'scan_results';
	protected static $tableAlias = 'scan_results';
	protected static $primaryKey = 'id';
	protected static $primaryKeyIsAutoIncrement = true;
	protected static $fields = array(
		'id',
		'scan_id',
		'resource_name',
		'resource_quality',
		'resource_extraction_rate',
	);
	
	protected static $beanMap = array(
		'scan' => array(
			'joinType' => 'LEFT',
			'joinBean' => 'darkcrusader\sqlbeans\ScanBean',
			'foreignKey' => 'scan_id'
		)
	);

	public function get_scan() {
		return $this->getMapped("scan");
	}
	
	public function get_resource_string() {
		$resourceString = $this->resource_name;
		if ($this->resource_quality != "na") {
			switch($this->resource_quality) {
				case "good":
					$resourceString .= ", Good Quality";
				break;
				case "medium":
					$resourceString .= ", Medium Quality";
				break;
				case "low":
					$resourceString .= ", Low Quality";
				break;
			}
		}
		
		$resourceString .= " (" . $this->resource_extraction_rate . "/hour)";

		return $resourceString;
	}
}
?>