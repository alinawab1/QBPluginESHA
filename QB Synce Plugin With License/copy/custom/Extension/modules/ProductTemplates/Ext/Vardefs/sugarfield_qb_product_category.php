<?php
/**
 * Created by PhpStorm.
 * User: umair.awan
 * Date: 19-Oct-15
 * Time: 2:16 PM
 */

$dictionary['ProductTemplate']['fields']['qb_item_category_c'] =   array(
    'name' => 'qb_item_category_c',
    'label' => 'LBL_QB_ITEM_CATEGORY',
    'vname' => 'LBL_QB_ITEM_CATEGORY',
    'type' => 'enum',
    'module' => 'ProductTemplate',
    'options' => 'qb_item_category_list', //maps to options - specify list name
    'default_value' => '', //key of entry in specified list
    'mass_update' => false, // true or false
    'required' => false, // true or false
    'reportable' => true, // true or false
    'audited' => false, // true or false
    'importable' => 'true', // 'true', 'false' or 'required'
    'duplicate_merge' => false, // true or false
);

?>