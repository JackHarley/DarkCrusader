<?php
/**
 * Bank Transaction SQLBean
 *
 * Copyright (c) 2012, Jack Harley
 * All Rights Reserved
 */
namespace darkcrusader\sqlbeans;

use hydrogen\sqlbeans\SQLBean;

class PersonalBankTransactionBean extends SQLBean {

	protected static $tableNoPrefix = 'personal_bank_transactions';
	protected static $tableAlias = 'personal_bank_transactions';
	protected static $primaryKey = 'id';
	protected static $primaryKeyIsAutoIncrement = true;
	protected static $fields = array(
		'id',
		'user_id', // user id
		'type', // e.g. Ship Refuel, Job, Market Sale, etc
		'direction', // 'in' or 'out'
		'amount', // amount of credits
		'balance', // balance after transaction
		'date', // date as datetime, YYYY-MM-DD HH MM SS
	);
	
	protected static $beanMap = array(
	);
}
?>