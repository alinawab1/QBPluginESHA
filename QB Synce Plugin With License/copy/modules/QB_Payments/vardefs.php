<?php

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

$dictionary['QB_Payments'] = array(
    'table' => 'qb_payments',
    'audited' => true,
    'activity_enabled' => false,
    'duplicate_merge' => true,
    'fields' => array(
        'saleshistory_id' => array(
            'required' => false,
            'name' => 'saleshistory_id',
            'vname' => 'LBL_SALESHISTORY_ID',
            'type' => 'id',
            'massupdate' => false,
            'no_default' => false,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'enabled',
            'duplicate_merge_dom_value' => 1,
            'audited' => false,
            'reportable' => false,
            'unified_search' => false,
            'merge_filter' => 'disabled',
            'calculated' => false,
            'len' => 36,
            'size' => '20',
        ),
        'saleshistory_name' => array(
            'required' => true,
            'source' => 'non-db',
            'name' => 'saleshistory_name',
            'vname' => 'LBL_SALESHISTORY_NAME',
            'type' => 'relate',
            'massupdate' => false,
            'no_default' => false,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'enabled',
            'duplicate_merge_dom_value' => '1',
            'audited' => false,
            'reportable' => true,
            'unified_search' => false,
            'merge_filter' => 'disabled',
            'full_text_search' => array(
                'boost' => '0',
                'enabled' => false,
            ),
            'calculated' => false,
            'len' => '255',
            'size' => '20',
            'id_name' => 'saleshistory_id',
            'ext2' => 'SalesHistory',
            'module' => 'SalesHistory',
            'rname' => 'name',
            'quicksearch' => 'enabled',
            'studio' => 'visible',
        ),
        'saleshistory' => array(
            'name' => 'saleshistory',
            'type' => 'link',
            'relationship' => 'saleshistory_payments',
            'module' => 'SalesHistory',
            'bean_name' => 'SalesHistory',
            'source' => 'non-db',
            'vname' => 'LBL_SALESHISTORY',
        ),
        'amount' =>
        array(
            'required' => false,
            'name' => 'amount',
            'vname' => 'LBL_AMOUNT',
            'type' => 'varchar',
            'massupdate' => false,
            'default' => '',
            'no_default' => false,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'enabled',
            'duplicate_merge_dom_value' => '1',
            'audited' => false,
            'reportable' => true,
            'unified_search' => false,
            'merge_filter' => 'disabled',
            'full_text_search' =>
            array(
                'boost' => '0',
                'enabled' => false,
            ),
            'calculated' => false,
            'len' => '255',
            'size' => '20',
        ),
        'payment_method' =>
        array(
            'required' => false,
            'name' => 'payment_method',
            'vname' => 'LBL_PAYMENT_METHOD',
            'type' => 'varchar',
            'massupdate' => false,
            'default' => '',
            'no_default' => false,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'enabled',
            'duplicate_merge_dom_value' => '1',
            'audited' => false,
            'reportable' => true,
            'unified_search' => false,
            'merge_filter' => 'disabled',
            'full_text_search' =>
            array(
                'boost' => '0',
                'enabled' => false,
            ),
            'calculated' => false,
            'len' => '255',
            'size' => '20',
        ),
        'reference_number' =>
        array(
            'required' => false,
            'name' => 'reference_number',
            'vname' => 'LBL_REFERENCE_NUMBER',
            'type' => 'varchar',
            'massupdate' => false,
            'default' => '',
            'no_default' => false,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'enabled',
            'duplicate_merge_dom_value' => '1',
            'audited' => false,
            'reportable' => true,
            'unified_search' => false,
            'merge_filter' => 'disabled',
            'full_text_search' =>
            array(
                'boost' => '0',
                'enabled' => false,
            ),
            'calculated' => false,
            'len' => '255',
            'size' => '20',
        ),
        'payment_date' =>
        array(
            'required' => false,
            'name' => 'payment_date',
            'vname' => 'LBL_PAYMENT_DATE',
            'type' => 'date',
            'massupdate' => true,
            'no_default' => false,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'enabled',
            'duplicate_merge_dom_value' => '1',
            'audited' => false,
            'reportable' => true,
            'unified_search' => false,
            'merge_filter' => 'disabled',
            'calculated' => false,
            'size' => '20',
            'enable_range_search' => false,
        ),
    ),
    'relationships' => array(
    ),
    'optimistic_locking' => true,
    'unified_search' => true,
);

if (!class_exists('VardefManager')) {
    require_once 'include/SugarObjects/VardefManager.php';
}
VardefManager::createVardef('QB_Payments', 'QB_Payments', array('basic', 'team_security', 'assignable'));
