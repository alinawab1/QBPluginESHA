<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */


$module_name = 'SalesHistory';
$subpanel_layout = array(
	'where' => '',
	'list_fields' => array(
		'name'=>array(
	 		'vname' => 'LBL_NAME',
			'widget_class' => 'SubPanelDetailViewLink',
	 		'width' => '25%',
		),
		'account_name'=>array(
	 		'vname' => 'LBL_ACCOUNT_NAME',
			'widget_class' => 'SubPanelDetailViewLink',
	 		'width' => '15%',
		),
		'quote_name'=>array(
	 		'vname' => 'LBL_QUOTE_NAME',
			'widget_class' => 'SubPanelDetailViewLink',
	 		'width' => '15%',
		),
		'quote_type'=>array(
	 		'vname' => 'LBL_QUOTE_TYPE',
	 		'width' => '15%',
		),
		'assigned_user_name' => array (
			'name' => 'assigned_user_name',
			'vname' => 'LBL_ASSIGNED_USER',
			'widget_class' => 'SubPanelDetailViewLink',
		 	'target_record_key' => 'assigned_user_id',
			'target_module' => 'Employees',
			'width' => '15%',
		),
		'qbfile_name'=>array(
	 		'vname' => 'LBL_QBFILE_NAME',
	 		'width' => '15%',
		),
	),
);

?>