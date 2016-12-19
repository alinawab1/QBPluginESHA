<?php
// created: 2014-12-02 06:25:13
$dictionary["Contact"]["fields"]["paccounts"] = array (
  'name' => 'paccounts',
  'type' => 'link',
  'relationship' => 'accounts_p_contacts',
  'source' => 'non-db',
  'module' => 'Accounts',
  'bean_name' => 'Account',
  'vname' => 'LBL_P_ACCOUNT',
  'id_name' => 'primary_account_id',
);
$dictionary["Contact"]["fields"]["primary_account_name"] = array (
  'name' => 'primary_account_name',
  'type' => 'relate',
  'source' => 'non-db',
  'vname' => 'LBL_P_ACCOUNT',
  'save' => true,
  'id_name' => 'primary_account_id',
  'link' => 'paccounts',
  'table' => 'accounts',
  'module' => 'Accounts',
  'rname' => 'name',
);
$dictionary["Contact"]["fields"]["primary_account_id"] = array (
  'name' => 'primary_account_id',
  'type' => 'id',
  'source' => 'non-db',
  'vname' => 'LBL_P_ACCOUNT',
  'id_name' => 'primary_account_id',
  'link' => 'paccounts',
  'table' => 'accounts',
  'module' => 'Accounts',
  'rname' => 'id',
  'reportable' => false,
  'side' => 'left',
  'massupdate' => false,
  'duplicate_merge' => 'disabled',
  'hideacl' => true,
);
