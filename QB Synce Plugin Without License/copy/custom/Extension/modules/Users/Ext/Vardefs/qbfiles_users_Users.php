<?php
// created: 2014-12-02 06:25:13
$dictionary["User"]["fields"]["qbfiles"] = array (
  'name' => 'qbfiles',
  'type' => 'link',
  'relationship' => 'qbfiles_users',
  'source' => 'non-db',
  'module' => 'QBFiles',
  'bean_name' => false,
  'vname' => 'LBL_QBFILES_USERS_TITLE',
  'id_name' => 'qbfiles_id',
);
$dictionary["User"]["fields"]["qbfiles_name"] = array (
  'name' => 'qbfiles_name',
  'type' => 'relate',
  'source' => 'non-db',
  'vname' => 'LBL_QBFILES_TITLE',
  'save' => true,
  'id_name' => 'qbfiles_id',
  'link' => 'qbfiles',
  'table' => 'qbfiles',
  'module' => 'QBFiles',
  'rname' => 'name',
);
$dictionary["User"]["fields"]["qbfiles_id"] = array (
  'name' => 'qbfiles_id',
  'type' => 'id',
  'source' => 'non-db',
  'vname' => 'LBL_QBFILES_TITLE',
  'id_name' => 'qbfiles_id',
  'link' => 'qbfiles',
  'table' => 'qbfiles',
  'module' => 'QBFiles',
  'rname' => 'id',
  'reportable' => false,
  'side' => 'left',
  'massupdate' => false,
  'duplicate_merge' => 'disabled',
  'hideacl' => true,
);
