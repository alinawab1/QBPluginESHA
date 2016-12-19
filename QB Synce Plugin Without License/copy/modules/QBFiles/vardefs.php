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

$dictionary['QBFiles'] = array(
    'table' => 'qbfiles',
    'audited' => true,
    'activity_enabled' => false,
    'duplicate_merge' => true,
    'fields' => array(
        'accounts' => array(
            'name' => 'accounts',
            'type' => 'link',
            'relationship' => 'qbfiles_accounts',
            'source' => 'non-db',
            'module' => 'Accounts',
            'bean_name' => 'Account',
            'vname' => 'LBL_ACCOUNT',
        ),
        'opportunities' => array(
            'name' => 'opportunities',
            'type' => 'link',
            'relationship' => 'qbfiles_opportunities',
            'source' => 'non-db',
            'module' => 'Opportunities',
            'bean_name' => 'Opportunities',
            'vname' => 'LBL_OPPORTUNITY',
        ),
//        'producttemplates' => array(
//            'name' => 'producttemplates',
//            'type' => 'link',
//            'relationship' => 'qbfiles_producttemplates',
//            'source' => 'non-db',
//            'module' => 'ProductTemplates',
//            'bean_name' => 'ProductTemplate',
//            'vname' => 'LBL_PRODUCT',
//        ),
        'quotes' => array(
            'name' => 'quotes',
            'type' => 'link',
            'relationship' => 'qbfiles_quotes',
            'source' => 'non-db',
            'module' => 'Quotes',
            'bean_name' => 'Quote',
            'vname' => 'LBL_QUOTE',
        ),
        'saleshistory' => array(
            'name' => 'saleshistory',
            'type' => 'link',
            'relationship' => 'qbfiles_saleshistory',
            'source' => 'non-db',
            'module' => 'SalesHistory',
            'bean_name' => 'SalesHistory',
            'vname' => 'LBL_SALES_HISTORY',
        ),
        'product_template_qbfiles' => array(
            'name' => 'product_template_qbfiles',
            'type' => 'link',
            'relationship' => 'product_template_qbfiles_relationship',
            'source' => 'non-db',
        ),
    ),
    'relationships' => array(
        'qbfiles_accounts' => array(
            'lhs_module' => 'QBFiles',
            'lhs_table' => 'qbfiles',
            'lhs_key' => 'id',
            'rhs_module' => 'Accounts',
            'rhs_table' => 'accounts',
            'rhs_key' => 'qbfile_id',
            'relationship_type' => 'one-to-many',
        ),
        'qbfiles_opportunities' => array(
            'lhs_module' => 'QBFiles',
            'lhs_table' => 'qbfiles',
            'lhs_key' => 'id',
            'rhs_module' => 'Opportunities',
            'rhs_table' => 'Opportunities',
            'rhs_key' => 'qbfile_id',
            'relationship_type' => 'one-to-many',
        ),
//        'qbfiles_producttemplates' => array(
//            'lhs_module' => 'QBFiles',
//            'lhs_table' => 'qbfiles',
//            'lhs_key' => 'id',
//            'rhs_module' => 'ProductTemplates',
//            'rhs_table' => 'product_templates',
//            'rhs_key' => 'qbfile_id',
//            'relationship_type' => 'one-to-many',
//        ),
        'qbfiles_quotes' => array(
            'lhs_module' => 'QBFiles',
            'lhs_table' => 'qbfiles',
            'lhs_key' => 'id',
            'rhs_module' => 'Quotes',
            'rhs_table' => 'quotes',
            'rhs_key' => 'qbfile_id',
            'relationship_type' => 'one-to-many',
        ),
        'qbfiles_saleshistory' => array(
            'lhs_module' => 'QBFiles',
            'lhs_table' => 'qbfiles',
            'lhs_key' => 'id',
            'rhs_module' => 'SalesHistory',
            'rhs_table' => 'saleshistory',
            'rhs_key' => 'qbfile_id',
            'relationship_type' => 'one-to-many',
        ),
    ),
    'optimistic_locking' => true,
    'unified_search' => true,
);

if (!class_exists('VardefManager')) {
    require_once 'include/SugarObjects/VardefManager.php';
}
VardefManager::createVardef('QBFiles', 'QBFiles', array('basic', 'team_security', 'assignable'));
