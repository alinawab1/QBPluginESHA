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
$_module_name = 'saleshistory';
$viewdefs[$module_name]['base']['view']['list'] = array(
    'panels' => array(
        array(
            'label' => 'LBL_PANEL_DEFAULT',
            'fields' => array(
                array(
                    'name' => 'name',
                    'label' => 'LBL_NAME',
                    'link' => true,
                    'default' => true,
                    'enabled' => true,
                    'width' => '15',
                ),
                array(
                    'name' => 'account_name',
                    'label' => 'LBL_ACCOUNT_NAME',
                    'link' => true,
                    'default' => true,
                    'enabled' => true,
                    'width' => '10',
                ),
                array(
                    'name' => 'quote_name',
                    'label' => 'LBL_QUOTE_NAME',
                    'link' => true,
                    'default' => true,
                    'enabled' => true,
                    'width' => '10',
                ),
                array(
                    'name' => 'quote_type',
                    'label' => 'LBL_QUOTE_TYPE',
                    'default' => true,
                    'enabled' => true,
                    'width' => '10',
                ),
                array(
                    'name' => 'account_name',
                    'label' => 'LBL_ACCOUNT_NAME',
                    'link' => true,
                    'default' => true,
                    'enabled' => true,
                    'width' => '10',
                ),
                array(
                    'name' => 'qbfile_name',
                    'label' => 'LBL_QBFILE_NAME',
                    'default' => true,
                    'enabled' => true,
                    'width' => '10',
                ),
                array(
                    'name' => 'assigned_user_name',
                    'label' => 'LBL_ASSIGNED_TO_NAME',
                    'default' => true,
                    'enabled' => true,
                    'width' => '10',
                ),
				array(
                    'name' => 'billing_address_city',
                    'label' => 'LBL_CITY',
                    'default' => true,
                    'enabled' => true,
                    'width' => '10',
                ),
                array(
                    'name' => 'billing_address_street',
                    'label' => 'LBL_BILLING_ADDRESS_STREET',
                    'default' => false,
                    'enabled' => true,
                    'width' => '15',
                ),
                array(
                    'name' => 'billing_address_state',
                    'label' => 'LBL_BILLING_ADDRESS_STATE',
                    'default' => false,
                    'enabled' => true,
                    'width' => '7',
                ),
                array(
                    'name' => 'billing_address_postalcode',
                    'label' => 'LBL_BILLING_ADDRESS_POSTALCODE',
                    'default' => false,
                    'enabled' => true,
                    'width' => '10',
                ),
                array(
                    'name' => 'billing_address_country',
                    'label' => 'LBL_BILLING_ADDRESS_COUNTRY',
                    'default' => false,
                    'enabled' => true,
                    'width' => '10',
                ),
                array(
                    'name' => 'shipping_address_street',
                    'label' => 'LBL_SHIPPING_ADDRESS_STREET',
                    'default' => false,
                    'enabled' => true,
                    'width' => '15',
                ),
                array(
                    'name' => 'shipping_address_city',
                    'label' => 'LBL_SHIPPING_ADDRESS_CITY',
                    'default' => false,
                    'enabled' => true,
                    'width' => '10',
                ),
                array(
                    'name' => 'shipping_address_state',
                    'label' => 'LBL_SHIPPING_ADDRESS_STATE',
                    'default' => false,
                    'enabled' => true,
                    'width' => '7',
                ),
                array(
                    'name' => 'shipping_address_postalcode',
                    'label' => 'LBL_SHIPPING_ADDRESS_POSTALCODE',
                    'default' => false,
                    'enabled' => true,
                    'width' => '10',
                ),
                array(
                    'name' => 'shipping_address_country',
                    'label' => 'LBL_SHIPPING_ADDRESS_COUNTRY',
                    'default' => false,
                    'enabled' => true,
                    'width' => '10',
                ),
            ),
        ),
    ),
);
