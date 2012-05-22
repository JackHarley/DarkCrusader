<?php
/**
 * Bank Transaction SQLBean
 *
 * Copyright (c) 2012, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\sqlbeans;

use hydrogen\sqlbeans\SQLBean;

class FactionBankTransactionBean extends SQLBean {

	protected static $tableNoPrefix = 'faction_bank_transactions';
	protected static $tableAlias = 'faction_bank_transactions';
	protected static $primaryKey = 'id';
	protected static $primaryKeyIsAutoIncrement = true;
	protected static $fields = array(
		'id',
		'type', // 'transfer' or 'join_fee'
		'direction', // 'in' or 'out'
		'amount', // amount of C
		'balance', // balance after transaction
		'date', // date as datetime, YYYY-MM-DD HH MM SS
		'player_name' // player involved, if applicable
	);
	
	protected static $beanMap = array(
	);
}
?>