<?php
/**
 * Faction Model
 * Handles data requests regarding factions
 *
 * Copyright (c) 2011, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\models;

use hydrogen\model\Model;
use hydrogen\database\Query;

use darkcrusader\models\UserModel;
use darkcrusader\models\SystemModel;
use darkcrusader\sqlbeans\ScanResultBean;
use darkcrusader\sqlbeans\SystemBean;
use darkcrusader\sqlbeans\SystemStatsBean;
use darkcrusader\sqlbeans\SystemStatsSetBean;

class FactionModel extends Model {
	
	protected static $modelID = "Faction";

	/**
	 * Gets the number of systems a faction owns (controls 51%+ colonies in system)
	 * 
	 * @param string $factionName faction full name
	 * @return int number of owned systems
	 */
	public function getNumberOfOwnedSystems($factionName) {

		// Get number of owned systems
		$query = new Query("SELECT");
		$query->from("system_stats");
		$query->field("faction");
		$query->where("faction LIKE ?", '%' . $factionName . '%');
		$query->where("stats_set = ?", SystemModel::getInstance()->getLatestSystemStatsSetCached()->id);
		$stmt = $query->prepare();
		$stmt->execute();
		$numberOfOwnedSystems = $stmt->rowCount();

		return $numberOfOwnedSystems;
	}

	/**
	 * Gets the number of station systems a faction owns (controls 51%+ colonies in system)
	 * 
	 * @param string $factionName faction full name
	 * @return int number of owned station systems
	 */
	public function getNumberOfOwnedStationSystems($factionName) {

		// Get number of owned systems
		$query = new Query("SELECT");
		$query->from("system_stats");
		$query->field("faction");
		$query->where("faction LIKE ?", '%' . $factionName . '%');
		$query->where("stats_set = ?", SystemModel::getInstance()->getLatestSystemStatsSetCached()->id);
		$query->where("has_station = ?", 1);
		$stmt = $query->prepare();
		$stmt->execute();
		$numberOfOwnedStationSystems = $stmt->rowCount();

		return $numberOfOwnedStationSystems;
	}

	/**
	 * Searches for a faction name from a search string and returns the full name
	 * 
	 * @param string $terms faction name search, e.g. tactical
	 * @return string faction full name, e.g. Tactical Response Team
	 */
	public function searchFactionName($terms) {
		$q = new Query("SELECT");
		$q->where("faction LIKE ?", '%' . $terms . '%');
		$q->orderby("stats_set", "DESC"); // order by stats set so we get latest info
		$q->limit(1);

		$ssbs = SystemStatsBean::select($q);
		return $ssbs[0]->faction;
	}
	
	public function createFactionCharts($faction) {
		
		$faction = '%' . $faction . '%';
		
		$factionName = $this->searchFactionName($faction);
		$factionName = str_replace(" ", "_", $factionName);

		// Get latest stats sets
		$query = new Query("SELECT");
		$query->orderby("time", "DESC");
		$query->limit(14);
		$latestSystemStatsSets = SystemStatsSetBean::select($query);
		
		// Get number of owned systems for each set
		$historicalNumberOfOwnedSystems = array();
		$historicalNumberOfOwnedSystemsDates = array();
		
		foreach($latestSystemStatsSets as $systemStatsSet) {
			$time = explode(" ", $systemStatsSet->time);
			$time = explode("-", $time[0]);
			$time = $time[2] . "/" . $time[1];
			
			$query = new Query("SELECT");
			$query->from("system_stats");
			$query->field("faction");
			$query->where("faction LIKE ?", $faction);
			$query->where("stats_set = ?", $systemStatsSet->id);
			$stmt = $query->prepare();
			$stmt->execute();
			$numberOfOwnedSystems = $stmt->rowCount();
			
			$historicalNumberOfOwnedSystems[] = $numberOfOwnedSystems;
			$historicalNumberOfOwnedSystemsDates[] = $time;
		}
		
		// Reverse the order of the data (newest->oldest ---> oldest->newest)
		$historicalNumberOfOwnedSystems = array_reverse($historicalNumberOfOwnedSystems);
		$historicalNumberOfOwnedSystemsDates = array_reverse($historicalNumberOfOwnedSystemsDates);
		
		// Create the chart
		$DataSet = new \pData;
		foreach($historicalNumberOfOwnedSystems as $id => $numberOfOwnedSystems) {
			$DataSet->AddPoint($numberOfOwnedSystems, "Serie1", $historicalNumberOfOwnedSystemsDates[$id]);
		}
		$DataSet->AddSerie("Serie1");
		$DataSet->SetSerieName("Number of Owned Systems","Serie1");
		
		$chart = new \pChart(700,230);
		$chart->setFontProperties(__DIR__ ."/../../pChart/Fonts/tahoma.ttf",10);
		$chart->setGraphArea(40,30,680,200);
		$chart->drawGraphArea(252,252,252,TRUE);
		$chart->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_NORMAL,0,0,0,TRUE,0,0);
		$chart->drawGrid(4,TRUE,230,230,230,70);
		
		$chart->drawLineGraph($DataSet->GetData(),$DataSet->GetDataDescription());
		$chart->drawPlotGraph($DataSet->GetData(),$DataSet->GetDataDescription(),3,2,255,255,255);
		
		$chart->setFontProperties(__DIR__ ."/../../pChart/Fonts/tahoma.ttf",8);
		$chart->drawLegend(45,35,$DataSet->GetDataDescription(),255,255,255);
		$chart->setFontProperties(__DIR__ ."/../../pChart/Fonts/tahoma.ttf",10);
		$chart->drawTitle(60,22,"Number of Owned Systems over the Past 2 Weeks",50,50,50,585);
		
		$chart->Render(__DIR__ . "/../../../graphs/" . $factionName . "-Systems.png");
		
		// Get number of owned systems for each set
		$historicalNumberOfOwnedStationSystems = array();
		$historicalNumberOfOwnedStationSystemsDates = array();
		
		foreach($latestSystemStatsSets as $systemStatsSet) {
			$time = explode(" ", $systemStatsSet->time);
			$time = explode("-", $time[0]);
			$time = $time[2] . "/" . $time[1];
			
			$query = new Query("SELECT");
			$query->from("system_stats");
			$query->field("faction");
			$query->where("faction LIKE ?", $faction);
			$query->where("stats_set = ?", $systemStatsSet->id);
			$query->where("has_station = ?", 1);
			$stmt = $query->prepare();
			$stmt->execute();
			$numberOfOwnedStationSystems = $stmt->rowCount();
			
			$historicalNumberOfOwnedStationSystems[] = $numberOfOwnedStationSystems;
			$historicalNumberOfOwnedStationSystemsDates[] = $time;
		}
		
		// Reverse the order of the data (newest->oldest ---> oldest->newest)
		$historicalNumberOfOwnedStationSystems = array_reverse($historicalNumberOfOwnedStationSystems);
		$historicalNumberOfOwnedStationSystemsDates = array_reverse($historicalNumberOfOwnedStationSystemsDates);
		
		// Create the chart
		$DataSet = new \pData;
		foreach($historicalNumberOfOwnedStationSystems as $id => $numberOfOwnedStationSystems) {
			$DataSet->AddPoint($numberOfOwnedStationSystems, "Serie1", $historicalNumberOfOwnedStationSystemsDates[$id]);
		}
		$DataSet->AddSerie("Serie1");
		$DataSet->SetSerieName("Number of Owned Station Systems","Serie1");
		
		$chart = new \pChart(700,230);
		$chart->setFontProperties(__DIR__ ."/../../pChart/Fonts/tahoma.ttf",10);
		$chart->setGraphArea(40,30,680,200);
		$chart->drawGraphArea(252,252,252,TRUE);
		$chart->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_NORMAL,0,0,0,TRUE,0,0);
		$chart->drawGrid(4,TRUE,230,230,230,70);
		
		$chart->drawLineGraph($DataSet->GetData(),$DataSet->GetDataDescription());
		$chart->drawPlotGraph($DataSet->GetData(),$DataSet->GetDataDescription(),3,2,255,255,255);
		
		$chart->setFontProperties(__DIR__ ."/../../pChart/Fonts/tahoma.ttf",8);
		$chart->drawLegend(45,35,$DataSet->GetDataDescription(),255,255,255);
		$chart->setFontProperties(__DIR__ ."/../../pChart/Fonts/tahoma.ttf",10);
		$chart->drawTitle(60,22,"Number of Owned Station Systems over the Past 2 Weeks",50,50,50,585);
		
		$chart->Render(__DIR__ . "/../../../graphs/" . $factionName . "-StationSystems.png");

		$return = array(
			"systems" => $factionName . "-Systems.png",
			"station_systems" => $factionName . "-StationSystems.png");
		
		return $return;
	}
}
?>