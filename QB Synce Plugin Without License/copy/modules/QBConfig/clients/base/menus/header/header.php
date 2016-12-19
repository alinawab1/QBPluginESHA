<?php





$moduleName = 'QBConfig';
$viewdefs[$moduleName]['base']['menu']['header'] = array(
    array(
        'label' =>'LNK_SETUP',
        'acl_action'=>'qbconfig',
        'acl_module'=>$moduleName,
        'icon' => 'icon-plus',
        'route'=>'#'.$moduleName.'/layout/qbconfig',
    ),
);
