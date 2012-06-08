<?php
/**
 * Bank Transaction
 *
 * Copyright (c) 2012, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\bank;

class PersonalBankTransaction {

	public $type; // e.g. Ship Refuel, Job, Market Sale, etc
	public $direction; // 'in' or 'out'
	public $amount; // amount of credits
	public $balance; // balance after transaction
	public $description; // oe description of transaction, for backwards compatibility when more info
				         // is dealt with in future (e.g. market breakdown by items, etc)
	public $characterName; // if another character was involved (job, market, transfer, etc) this should hold
						   // their name
	public $date; // date as datetime, YYYY-MM-DD HH MM SS
}
?>