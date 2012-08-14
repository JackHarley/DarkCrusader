<?php
namespace darkcrusader\sqlbeans;
use hydrogen\sqlbeans\SQLBean;
use hydrogen\config\Config;
use darkcrusader\models\ColoniesModel;
use darkcrusader\models\StoredItemsModel;

class ColonyBean extends SQLBean {

	protected static $tableNoPrefix = 'colonies';
	protected static $tableAlias = 'colonies';
	protected static $primaryKey = 'id';
	protected static $primaryKeyIsAutoIncrement = true;
	protected static $fields = array(
		'id',
		'user_id',
		'name',
		'system_id',
		'planet_numeral',
		'moon_number',
		'population',
		'max_population',
		'morale',
		'power',
		'free_power',
		'size',
		'free_size',
		'max_size',
		'storage_capacity',
		'displayed_size',
		'primary_activity', // 'mining', 'manufacturing', 'research', 'processing', 'refining'
	);
	
	protected static $beanMap = array(
		'user' => array(
			'joinType' => 'LEFT',
			'joinBean' => 'darkcrusader\sqlbeans\UserBean',
			'foreignKey' => 'user_id'
		),
		'system' => array(
			'joinType' => 'LEFT',
			'joinBean' => 'darkcrusader\sqlbeans\SystemBean',
			'foreignKey' => 'system_id'
		)
	);

	public function get_location_string() {
		$system = $this->getMapped("system");

		$system_name = '<a href="' . Config::getVal("general", "app_url") . '/index.php/systems/system?name=' . $system->system_name . '">' . $system->system_name . '</a>';

		$locationString = $system_name . " " . $this->planet_numeral;
		
		if ($this->moon_number != 0)
			$locationString .= " M" . $this->moon_number;

		return $locationString;
	}

	public function get_worker_costs_per_25_hours() {
		return $this->population * 6;
	}

	public function get_formatted_primary_activity() {
		if (!$this->primary_activity)
			return "Unclassified";
		else
			return ucfirst($this->primary_activity);
	}

	public function get_system() {
		return $this->getMapped("system");
	}

	public function get_user() {
		return $this->getMapped("user");
	}

	protected $Status = false;
	public function get_status() {
		if (!$this->Status)
			$this->Status = ColoniesModel::getInstance()->getColonyStatus($this->id);

		return $this->Status;
	}

	protected $FreeCapacity = false;
	public function get_free_capacity() {
		if (!$this->FreeCapacity)
			$this->FreeCapacity = ColoniesModel::getInstance()->getFreeCapacityInColony($this->id);

		return $this->FreeCapacity;
	}

	protected $Resources = false;
	public function get_resources() {
		if (!$this->Resources)
			$this->Resources = StoredItemsModel::getInstance()->getStoredResourcesInColony($this->id, false, false);

		return $this->Resources;
	}

	protected $StoredItems = false;
	public function get_stored_items() {
		if (!$this->StoredItems)
			$this->StoredItems = StoredItemsModel::getInstance()->getStoredItemsInColony($this->id);

		return $this->StoredItems;
	}
}
?>