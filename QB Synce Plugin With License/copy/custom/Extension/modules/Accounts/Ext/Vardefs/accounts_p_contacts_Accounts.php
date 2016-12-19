<?php
// created: 2014-12-02 06:25:13
$dictionary["Account"]["fields"]["pcontacts"] = array (
  'name' => 'pcontacts',
  'type' => 'link',
  'relationship' => 'accounts_p_contacts',
  'source' => 'non-db',
  'module' => 'Contacts',
  'bean_name' => false,
  'vname' => 'LBL_P_CONTACT',
  'id_name' => 'primary_contact_id',
);
$dictionary["Account"]["fields"]["primary_contact_name"] = array (
  'name' => 'primary_contact_name',
  'type' => 'relate',
  'source' => 'non-db',
  'vname' => 'LBL_P_CONTACT',
  'save' => true,
  'id_name' => 'primary_contact_id',
  'link' => 'pcontacts',
  'table' => 'contacts',
  'module' => 'Contacts',
  'rname' => 'name',
);
$dictionary["Account"]["fields"]["primary_contact_id"] = array (
  'name' => 'primary_contact_id',
  'type' => 'id',
  'source' => 'non-db',
  'vname' => 'LBL_P_CONTACT',
  'id_name' => 'primary_contact_id',
  'link' => 'pcontacts',
  'table' => 'contacts',
  'module' => 'Contacts',
  'rname' => 'id',
  'reportable' => false,
  'side' => 'left',
  'massupdate' => false,
  'duplicate_merge' => 'disabled',
  'hideacl' => true,
);
