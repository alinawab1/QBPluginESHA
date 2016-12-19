<?php
$dictionary["product_template_qbfiles_relationship"] = array (
  'true_relationship_type' => 'many-to-many',
  'relationships' => 
  array (
    'product_template_qbfiles_relationship' => 
    array (
      'lhs_module' => 'ProductTemplates',
      'lhs_table' => 'product_templates',
      'lhs_key' => 'id',
      'rhs_module' => 'QBFiles',
      'rhs_table' => 'qbfiles',
      'rhs_key' => 'id',
      'relationship_type' => 'many-to-many',
      'join_table' => 'product_template_qbfiles',
      'join_key_lhs' => 'product_template_id',
      'join_key_rhs' => 'qbfile_id',
    ),
  ),
  'table' => 'product_template_qbfiles',
  'fields' => 
  array (
    0 => 
    array (
      'name' => 'id',
      'type' => 'varchar',
      'len' => 36,
    ),
    1 => 
    array (
      'name' => 'date_modified',
      'type' => 'datetime',
    ),
    2 => 
    array (
      'name' => 'deleted',
      'type' => 'bool',
      'len' => '1',
      'default' => '0',
      'required' => true,
    ),
    3 => 
    array (
      'name' => 'product_template_id',
      'type' => 'varchar',
      'len' => 36,
    ),
    4 => 
    array (
      'name' => 'qbfile_id',
      'type' => 'varchar',
      'len' => 36,
    ),
    5 => 
    array (
      'name' => 'qb_id',
      'type' => 'varchar',
      'len' => 36,
    ),
    6 => 
    array (
      'name' => 'qb_synced',
      'type' => 'bool',
      'default' => false,
    ),
  ),
  'indices' => 
  array (
    0 => 
    array (
      'name' => 'product_template_qbfiles_pk',
      'type' => 'primary',
      'fields' => 
      array (
        0 => 'id',
      ),
    ),
    1 => 
    array (
      'name' => 'product_template_qbfile_id',
      'type' => 'index',
      'fields' => 
      array (
        0 => 'product_template_id',
      ),
    ),
    2 => 
    array (
      'name' => 'product_template_qbfiles_alt',
      'type' => 'alternate_key',
      'fields' => 
      array (
        0 => 'qbfile_id',
      ),
    ),
  ),
);