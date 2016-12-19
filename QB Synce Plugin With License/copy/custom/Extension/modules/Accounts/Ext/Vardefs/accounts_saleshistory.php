<?php

$dictionary["Account"]["fields"]["saleshistory"] = array(
	'name' => 'saleshistory',
	'type' => 'link',
	'relationship' => 'accounts_saleshistory',
	'source' => 'non-db',
	'module' => 'SalesHistory',
	'bean_name' => 'SalesHistory',
	'vname' => 'LBL_SALES_HISTORY',
);
$dictionary["Account"]["relationships"]["accounts_saleshistory"] = array(
	'lhs_module' => 'Accounts',
	'lhs_table' => 'accounts',
	'lhs_key' => 'id',
	'rhs_module' => 'SalesHistory',
	'rhs_table' => 'saleshistory',
	'rhs_key' => 'account_id',
	'relationship_type' => 'one-to-many',
);
