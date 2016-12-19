<?php

/**
 * Sales History subapnel in Quotes module
 */
$layout_defs["Quotes"]["subpanel_setup"]['saleshistory'] = array(
	'order' => 100,
	'module' => 'SalesHistory',
	'subpanel_name' => 'default',
	'sort_order' => 'desc',
	'sort_by' => 'date_modified',
	'title_key' => 'LBL_SALES_HISTORY_SUBPANEL_TITLE',
	'get_subpanel_data' => 'saleshistory',
	'top_buttons' => array(
		
	),
);
