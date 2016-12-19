<?php
// created: 2014-12-02 06:25:13
$dictionary["QBFiles"]["fields"]["qbusers"] = array (
  'name' => 'qbusers',
  'type' => 'link',
  'relationship' => 'qbfiles_users',
  'source' => 'non-db',
  'module' => 'Users',
  'bean_name' => 'User',
  'vname' => 'LBL_QBFILES_USERS_TITLE',
  'id_name' => 'users_id',
);
$dictionary["QBFiles"]["fields"]["users_name"] = array (
  'name' => 'users_name',
  'type' => 'relate',
  'source' => 'non-db',
  'vname' => 'LBL_USERS_TITLE',
  'save' => true,
  'id_name' => 'users_id',
  'link' => 'qbusers',
  'table' => 'users',
  'module' => 'Users',
  'rname' => 'name',
);
$dictionary["QBFiles"]["fields"]["users_id"] = array (
  'name' => 'users_id',
  'type' => 'id',
  'source' => 'non-db',
  'vname' => 'LBL_USERS_TITLE',
  'id_name' => 'users_id',
  'link' => 'qbusers',
  'table' => 'users',
  'module' => 'Users',
  'rname' => 'id',
  'reportable' => false,
  'side' => 'left',
  'massupdate' => false,
  'duplicate_merge' => 'disabled',
  'hideacl' => true,
);
