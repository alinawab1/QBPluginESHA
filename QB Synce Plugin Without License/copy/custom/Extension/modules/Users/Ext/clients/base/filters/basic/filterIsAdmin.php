<?php

$viewdefs['Users']['base']['filter']['basic']['filters'][] = array(
    'id' => 'filterIsAdmin',
    'name' => 'LBL_FILTER_IS_ADMIN',
    'filter_definition' => array(
        array(
            'is_admin' => '1',
        )
    ),
    'editable' => false,
    'is_template' => false,
);