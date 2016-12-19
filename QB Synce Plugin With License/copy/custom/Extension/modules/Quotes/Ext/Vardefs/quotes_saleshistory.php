<?php

$dictionary["Quote"]["fields"]["saleshistory"] = array(
	'name' => 'saleshistory',
	'type' => 'link',
	'relationship' => 'quotes_saleshistory',
	'source' => 'non-db',
	'module' => 'SalesHistory',
	'bean_name' => 'SalesHistory',
	'vname' => 'LBL_SALES_HISTORY',
);
$dictionary["Quote"]["relationships"]["quotes_saleshistory"] = array(
	'lhs_module' => 'Quotes',
	'lhs_table' => 'quotes',
	'lhs_key' => 'id',
	'rhs_module' => 'SalesHistory',
	'rhs_table' => 'saleshistory',
	'rhs_key' => 'quote_id',
	'relationship_type' => 'one-to-many',
);
