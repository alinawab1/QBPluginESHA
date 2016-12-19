<?php
$dictionary['ProductTemplate']['fields']['qb_product_name'] = array(
    'name' => 'qb_product_name',
    'label' => 'LBL_QB_PRODUCT_NAME',
    'vname' => 'LBL_QB_PRODUCT_NAME',
    'type' => 'varchar',
    'len' => '31',
    'default_value' => '', //key of entry in specified list
    'required' => true, // true or false
    'reportable' => true, // true or false
    'audited' => false, // true or false
    'importable' => 'true', // 'true', 'false' or 'required'
    'duplicate_merge' => false, // true or false
    'comment' => 'QB Name should not contain a colon(:) and its length should be less than equal to 31 characters',
    'help' => 'QB Name should not contain a colon(:) and its length should be less than equal to 31 characters',
    'hint' => 'QB Product Name'
);
 ?>