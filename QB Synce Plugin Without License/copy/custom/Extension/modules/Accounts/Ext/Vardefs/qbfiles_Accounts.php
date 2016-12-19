<?php

$dictionary["Account"]["fields"]["qbfiles"] = array(
	'name' => 'qbfiles',
	'type' => 'link',
	'relationship' => 'qbfiles_accounts',
	'module' => 'QBFiles',
	'bean_name' => 'QBFiles',
	'source' => 'non-db',
	'vname' => 'LBL_QBFILE',
);
$dictionary["Account"]["fields"]["qbfile_id"] = array(
	'name' => 'qbfile_id',
	'rname' => 'id',
	'vname' => 'LBL_QBFILE_ID',
	'type' => 'id',
	'isnull' => 'true',
	'module' => 'QBFiles',
	'dbType' => 'id',
	'reportable' => false,
	'massupdate' => false,
	'duplicate_merge' => 'disabled',
	'hideacl' => true,
	'link' => 'qbfiles',
);
$dictionary["Account"]["fields"]["qbfile_name"] = array(
	'name' => 'qbfile_name',
	'rname' => 'name',
	'id_name' => 'qbfile_id',
	'vname' => 'LBL_QBFILE_NAME',
	'type' => 'relate',
	'link' => 'qbfiles',
	'table' => 'qbfiles',
	'isnull' => 'true',
	'module' => 'QBFiles',
	'len' => '255',
	'source' => 'non-db',
	'unified_search' => true,
	'importable' => 'true',
	'exportable' => true,
);
