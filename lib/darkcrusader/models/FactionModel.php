<?php
/**
 * Faction Model
 * Handles data requests regarding factions
 *
 * Copyright (c) 2012, Jack Harley
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
		$DataSet->addPoints($historicalNumberOfOwnedSystems, "NumberOfOwnedSystems");
		$DataSet->setAxisName(0, "Number of Owned Systems");
		$DataSet->addPoints($historicalNumberOfOwnedSystemsDates, "Labels");
		$DataSet->setSerieDescription("Labels", "Date");
		$DataSet->setAbscissa("Labels");
		$DataSet->setSerieShape("NumberOfOwnedSystems",SERIE_SHAPE_FILLEDCIRCLE);
		$serieSettings = array("R"=>0,"G"=>0,"B"=>0,"Alpha"=>80);
		$DataSet->setPalette("NumberOfOwnedSystems", $serieSettings);
		$DataSet->setSerieWeight("NumberOfOwnedSystems",1);

		$myPicture = new \pImage(700,230,$DataSet);
		$GradientSettings = array("StartR"=>0,"StartG"=>191,"StartB"=>255,"Alpha"=>100,"Levels"=>50);
		$myPicture->drawGradientArea(0,0,700,230,DIRECTION_VERTICAL,$GradientSettings);
		$myPicture->drawText(350,45,"Number Of Owned Systems",array("FontSize"=>20,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE));
		$myPicture->setGraphArea(60,60,640,190);
		$myPicture->drawFilledRectangle(60,60,640,190,array("R"=>255,"G"=>255,"B"=>255,"Surrounding"=>-200,"Alpha"=>10));
		$myPicture->drawScale(array("DrawSubTicks"=>TRUE));
		$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));
		$myPicture->setFontProperties(array("FontName"=>__DIR__ . "/../../pChart/fonts/Forgotte.ttf","FontSize"=>6));
		$myPicture->drawLineChart(array("DisplayValues"=>false,"DisplayColor"=>DISPLAY_AUTO));
		$myPicture->setShadow(FALSE); 
		
		unlink(__DIR__ . "/../../../graphs/" . $factionName . "-Systems.png");
		$myPicture->render(__DIR__ . "/../../../graphs/" . $factionName . "-Systems.png");
		
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
		$DataSet->addPoints($historicalNumberOfOwnedStationSystems, "NumberOfOwnedStationSystems");
		$DataSet->setAxisName(0, "NumberOfOwnedStationSystems");
		$DataSet->addPoints($historicalNumberOfOwnedStationSystemsDates, "Labels");
		$DataSet->setSerieDescription("Labels", "Date");
		$DataSet->setAbscissa("Labels");
		$serieSettings = array("R"=>0,"G"=>0,"B"=>0,"Alpha"=>80);
		$DataSet->setPalette("NumberOfOwnedStationSystems", $serieSettings);
		$DataSet->setSerieWeight("NumberOfOwnedStationSystems",1);

		$myPicture = new \pImage(700,230,$DataSet);
		$GradientSettings = array("StartR"=>50,"StartG"=>205,"StartB"=>50,"Alpha"=>100,"Levels"=>50); 
		$myPicture->drawGradientArea(0,0,700,230,DIRECTION_VERTICAL,$GradientSettings);
		$myPicture->drawText(350,45,"Number Of Owned Station Systems",array("FontSize"=>20,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE));
		$myPicture->setGraphArea(60,60,640,190);
		$myPicture->drawFilledRectangle(60,60,640,190,array("R"=>255,"G"=>255,"B"=>255,"Surrounding"=>-200,"Alpha"=>10));
		$myPicture->drawScale(array("DrawSubTicks"=>TRUE));
		$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));
		$myPicture->setFontProperties(array("FontName"=>__DIR__ . "/../../pChart/fonts/Forgotte.ttf","FontSize"=>6));
		$myPicture->drawLineChart(array("DisplayValues"=>false,"DisplayColor"=>DISPLAY_AUTO));
		$myPicture->setShadow(FALSE);
		
		unlink(__DIR__ . "/../../../graphs/" . $factionName . "-StationSystems.png");
		$myPicture->render(__DIR__ . "/../../../graphs/" . $factionName . "-StationSystems.png");
		
		$return = array(
			"systems" => $factionName . "-Systems.png",
			"station_systems" => $factionName . "-StationSystems.png");
		
		return $return;
	}
}
?>