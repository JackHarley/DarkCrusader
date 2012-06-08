<?php
/**
 * Bank Transaction SQLBean
 *
 * Copyright (c) 2012, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\sqlbeans;

use hydrogen\sqlbeans\SQLBean;

class SiteBankTransferBean extends SQLBean {

	protected static $tableNoPrefix = 'site_bank_transfers';
	protected static $tableAlias = 'site_bank_transfers';
	protected static $primaryKey = 'id';
	protected static $primaryKeyIsAutoIncrement = true;
	protected static $fields = array(
		'id',
		'character_name', // character name
		'amount', // amount of credits
		'date', // date as datetime, YYYY-MM-DD HH MM SS
		'processed', // either 1 or 0 (true/false) whether or not this transaction
					 // has been processed
	);
	
	protected static $beanMap = array(
	);
}
?>