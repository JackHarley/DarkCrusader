<?php
/**
 * Market Model
 * Handles market stuff
 *
 * Copyright (c) 2012, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\models;

use hydrogen\model\Model;
use hydrogen\database\Query;

use darkcrusader\sqlbeans\PersonalBankTransactionBean;
use darkcrusader\models\UserModel;
use darkcrusader\market\MarketCustomer;

class MarketModel extends Model {

	protected static $modelID = "Market";

	/**
	 * Gets a user's best customers in terms of credits earned from sales to them
	 * 
	 * @param int $user user id
	 * @param string $period 'forever', 'last30days', 'last7days' or 'last24hours'
	 * @param int $limit number of customers to get
	 * @return array array of MarketCustomer objects
	 */
	public function getTopCustomers($user, $period="forever", $limit=10) {

		$q = new Query("SELECT");
		$q->where("direction = ?", "in"); // money in = sales
		$q->where("type = ?", "Market");

		switch($period) {
			case "last24hours":
				$q->where("date > DATE_SUB(NOW(), INTERVAL 1 DAY)");
			break;
			case "last7days":
				$q->where("date > DATE_SUB(NOW(), INTERVAL 7 DAY)");
			break;
			case "last30days":
				$q->where("date > DATE_SUB(NOW(), INTERVAL 30 DAY)");
			break;
		}

		$btbs = PersonalBankTransactionBean::select($q);

		$customers = array();
		foreach($btbs as $btb) {
			// grab the buyer out of the description
			$description = explode(" to ", $btb->description);
			$customerName = $description[1];
			$customerName = trim($customerName);

			if (!$customerName)
				continue;

			if (!$customers[$customerName]) {
				$customers[$customerName] = new MarketCustomer;
			}

			$customers[$customerName]->name = $customerName;
			$customers[$customerName]->totalSales += $btb->amount;
			$customers[$customerName]->numberOfIndividualSales++;
		}

		unset($btbs);

		// order by total sales
		$orderedCustomers = array();
		$timesToRun = count($customers);
		for ($timesRun=0; $timesRun<$timesToRun; $timesRun++) {

			$best = new MarketCustomer;

			foreach($customers as $customer) {
				if ($customer->totalSales > $best->totalSales) {
					$best = $customer;
				}

			}

			unset($customers[$best->name]);
			$orderedCustomers[] = $best;

			unset($best);
		}

		if ($limit) {
			for($i=1;$orderedCustomers[$i];$i++) {
				if ($i > $limit)
					unset($orderedCustomers[$i]);
			}
		}

		return $orderedCustomers;

	}

}
?>