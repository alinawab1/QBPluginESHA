<?php

if (!defined('sugarEntry') || !sugarEntry)
	die('Not A Valid Entry Point');
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

$module_name = 'QBFiles';
$listViewDefs[$module_name] = array(
	'NAME' => array(
		'width' => '40',
		'label' => 'LBL_NAME',
		'default' => true,
		'link' => true
	),
	'USERS_NAME' => array(
		'width' => '20',
		'label' => 'LBL_USERS_TITLE',
		'id' => 'USERS_ID',
		'default' => true
	),
	'ASSIGNED_USER_NAME' => array(
		'width' => '20',
		'label' => 'LBL_ASSIGNED_TO_NAME',
		'module' => 'Employees',
		'id' => 'ASSIGNED_USER_ID',
		'default' => true
	),
	'DATE_MODIFIED' => array(
		'label' => 'LBL_DATE_MODIFIED',
		'width' => '20',
		'default' => true,
	),
);
