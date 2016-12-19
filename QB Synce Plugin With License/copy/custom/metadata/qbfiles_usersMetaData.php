<?php
// created: 2014-12-02 06:25:13
$dictionary["qbfiles_users"] = array (
  'true_relationship_type' => 'one-to-one',
  'relationships' => 
  array (
    'qbfiles_users' => 
    array (
      'lhs_module' => 'QBFiles',
      'lhs_table' => 'qbfiles',
      'lhs_key' => 'id',
      'rhs_module' => 'Users',
      'rhs_table' => 'users',
      'rhs_key' => 'id',
      'relationship_type' => 'many-to-many',
      'join_table' => 'qbfiles_users',
      'join_key_lhs' => 'qbfiles_id',
      'join_key_rhs' => 'users_id',
    ),
  ),
  'table' => 'qbfiles_users',
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
      'name' => 'qbfiles_id',
      'type' => 'varchar',
      'len' => 36,
    ),
    4 => 
    array (
      'name' => 'users_id',
      'type' => 'varchar',
      'len' => 36,
    ),
  ),
  'indices' => 
  array (
    0 => 
    array (
      'name' => 'qbfiles_usersspk',
      'type' => 'primary',
      'fields' => 
      array (
        0 => 'id',
      ),
    ),
    1 => 
    array (
      'name' => 'qbfiles_users_id1',
      'type' => 'index',
      'fields' => 
      array (
        0 => 'qbfiles_id',
      ),
    ),
    2 => 
    array (
      'name' => 'qbfiles_users_id2',
      'type' => 'index',
      'fields' => 
      array (
        0 => 'users_id',
      ),
    ),
  ),
);