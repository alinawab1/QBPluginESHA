<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$module_name = 'QBConfig';
$viewdefs[$module_name]['base']['view']['qbconfig'] = array(
	'title' => 'LBL_WELCOME_QB_ADMIN_PANEL',
	'buttons' => array(
		array(
			'type' => 'rowaction',
			'event' => 'button:save_button:click',
			'name' => 'save_button',
			'label' => 'LBL_SAVE_CONFIG_BUTTON',
			'css_class' => 'btn btn-primary',
		),
	),
	'tabs' => array(
		array(
			'name' => 'basic_tab',
			'active' => 'active',
			'label' => 'LBL_BASIC_TAB',
			'fields' => array(
				array(
					'name' => 'license_key',
					'type' => 'text',
					'label' => 'LBL_LICENSE_KEY',
					'labelspan' => 'span4',
					'span' => 'span6',
					'template' => 'edit',
					'buttons' => array(
						array(
							'btnspan' => 'span2',
							'name' => 'validate_button',
							'type' => 'button',
							'label' => 'LBL_VALIDATE_KEY',
							'css_class' => 'btn btn-primary',
						),
					),
				),
				array(
					'name' => 'key_expiration',
					'type' => 'date',
					'labelspan' => 'span4',
					'span' => 'span6',
					'template' => 'detail',
					'label' => 'LBL_KEY_EXPIRATION',
				),
				array(
					'html' => 'LBL_CONNECTOR_INFO',
					'span1' => 'span3',
					'span2' => 'span6',
					'span3' => 'span3',
				),
				array(
					'html' => 'LBL_SUGAR_URL_INST',
					'span2' => 'span6',
					'span3' => 'span6',
				),
				array(
					'name' => 'sugar_last_sync_date',
					'type' => 'datetime',
					'label' => 'LBL_SUGAR_LAST_SYNC_DATE',
					'labelspan' => 'span6',
					'span' => 'span6',
					'template' => 'detail',
				),
				array(
					'name' => 'qb_user_list',
					'type' => 'enum',
					'enum_width' => '220px',
					'label' => 'LBL_CONFIG_USER',
					'desc' => 'LBL_CONFIG_USER_DESC',
					'labelspan' => 'span6',
					'span' => 'span6',
					'template' => 'edit',
				),
				array(
					'name' => 'email',
					'type' => 'email',
					'label' => 'LBL_EMAIL',
					'labelspan' => 'span6',
					'span' => 'span6',
					'template' => 'edit',
				),
				array(
					'name' => 'connector_password',
					'type' => 'text',
					'required' => true,
					'label' => 'LBL_CONNECTOR_PASSWORD',
					'labelspan' => 'span6',
					'span' => 'span6',
					'template' => 'edit',
				),
				array(
					'label' => 'LBL_WEB_CONNECTOR_FILE',
					'labelspan' => 'span6',
					'buttons' => array(
						array(
							'btnspan' => 'span4',
							'name' => 'download_button',
							'type' => 'button',
							'label' => 'LBL_DOWNLOAD_FILE',
							'css_class' => 'btn btn-primary',
						),
					),
				),
				array(
					'html' => 'LBL_WEB_CONNECTOR_INST',
					'span2' => 'span6',
					'span3' => 'span6',
				),
			),
		),
		array(
			'name' => 'config_tab',
			'label' => 'LBL_CONFIG_TAB',
			'fields' => array(
				array(
					'name' => 'opp_job_sync',
					'type' => 'bool',
					'label' => 'LBL_OPP_JOB_SYNC',
					'desc' => 'LBL_OPP_JOB_SYNC_DESC',
				),
				array(
					'name' => 'master_system',
					'type' => 'enum',
					'options' => 'master_system_list',
					'enum_width' => '200px',
					'label' => 'LBL_MASTER_SYSTEM',
					'desc' => 'LBL_MASTER_SYSTEM_DESC',
				),
				array(
					'name' => 'quote_maps_to',
					'type' => 'enum',
					'options' => 'quote_maps_to_list',
					'enum_width' => '200px',
					'label' => 'LBL_QUOTE_MAPS_TO',
					'desc' => 'LBL_QUOTE_MAPS_TO_DESC',
				),
				array(
					'name' => 'qb_quote_stage',
					'type' => 'enum',
					'options' => 'quote_stage_dom',
					'enum_width' => '200px',
					'label' => 'LBL_QUOTE_STAGE',
					'desc' => 'LBL_QUOTE_STAGE_DESC',
				),
				array(
					'name' => 'qb_timezone',
					'type' => 'enum',
					'enum_width' => '200px',
					'label' => 'LBL_QB_TIMEZONE',
					'desc' => 'LBL_QB_TIMEZONE_DESC',
				),
			),
		),
		array(
			'name' => 'function_tab',
			'label' => 'LBL_FUNCTION_TAB',
			'fields' => array(
				/*array(
					'name' => 'multiple_qb',
					'type' => 'bool',
					'label' => 'LBL_MULTIPLE_QB',
					'desc' => 'LBL_MULTIPLE_QB_DESC',
				),*/
				array(
					'name' => 'duplicate_check',
					'type' => 'enum',
					'options' => 'duplicate_check_list',
					'enum_width' => '200px',
					'label' => 'LBL_DUPLICATE_CHECK',
					'desc' => 'LBL_DUPLICATE_CHECK_DESC',
				),
			),
			'buttons' => array(
				array(
					'btnspan' => 'span4',
					'name' => 'clear_button',
					'type' => 'button',
					'title' => 'LBL_CLEAR_BTN_TITLE',
					'label' => 'LBL_CLEAR_BTN',
					'css_class' => 'btn btn-primary disabled',
				),
			),
		),
	),
);

