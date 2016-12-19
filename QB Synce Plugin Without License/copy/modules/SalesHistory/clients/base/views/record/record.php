<?php

/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
$module_name = 'SalesHistory';
$_module_name = 'saleshistory';
$viewdefs[$module_name]['base']['view']['record'] = array(
	'buttons' => array(
        array(
            'name' => 'sidebar_toggle',
            'type' => 'sidebartoggle',
        ),
    ),
	'panels' => array(
		array(
			'name' => 'panel_header',
			'label' => 'LBL_RECORD_HEADER',
			'header' => true,
			'fields' => array(
				array(
					'name' => 'picture',
					'type' => 'avatar',
					'width' => 42,
					'height' => 42,
					'dismiss_label' => true,
					'readonly' => true,
				),
				array(
					'name' => 'name',
					'readonly' => true,
				),
				array(
					'name' => 'favorite',
					'label' => 'LBL_FAVORITE',
					'type' => 'favorite',
					'readonly' => true,
					'dismiss_label' => true,
				),
			)
		),
		array(
			'name' => 'panel_body',
			'label' => 'LBL_RECORD_BODY',
			'columns' => 2,
			'labelsOnTop' => true,
			'placeholders' => true,
			'fields' => array(
				array(
					'name' => 'quote_name',
					'readonly' => true,
				),
				array(
					'name' => 'quote_type',
					'readonly' => true,
				),
				array(
					'name' => 'account_name',
					'readonly' => true,
				),
				array(
					'name' => 'invoice_number',
					'readonly' => true,
				),
				array(
					'name' => 'quote_number',
					'readonly' => true,
				),
				array(
					'name' => 'duedate',
					'readonly' => true,
				),
				array(
					'name' => 'assigned_user_name',
					'readonly' => true,
				),
				array(
					'name' => 'qbfile_name',
					'readonly' => true,
				),
				array(
					'name' => 'billing_address',
					'type' => 'fieldset',
					'readonly' => true,
					'css_class' => 'address',
					'label' => 'LBL_BILLING_ADDRESS',
					'fields' => array(
						array(
							'name' => 'billing_address_street',
							'css_class' => 'address_street',
							'placeholder' => 'LBL_BILLING_ADDRESS_STREET',
						),
						array(
							'name' => 'billing_address_city',
							'css_class' => 'address_city',
							'placeholder' => 'LBL_BILLING_ADDRESS_CITY',
						),
						array(
							'name' => 'billing_address_state',
							'css_class' => 'address_state',
							'placeholder' => 'LBL_BILLING_ADDRESS_STATE',
						),
						array(
							'name' => 'billing_address_postalcode',
							'css_class' => 'address_zip',
							'placeholder' => 'LBL_BILLING_ADDRESS_POSTALCODE',
						),
						array(
							'name' => 'billing_address_country',
							'css_class' => 'address_country',
							'placeholder' => 'LBL_BILLING_ADDRESS_COUNTRY',
						),
					),
				),
				array(
					'name' => 'shipping_address',
					'type' => 'fieldset',
					'readonly' => true,
					'css_class' => 'address',
					'label' => 'LBL_SHIPPING_ADDRESS',
					'fields' => array(
						array(
							'name' => 'shipping_address_street',
							'css_class' => 'address_street',
							'placeholder' => 'LBL_SHIPPING_ADDRESS_STREET',
						),
						array(
							'name' => 'shipping_address_city',
							'css_class' => 'address_city',
							'placeholder' => 'LBL_SHIPPING_ADDRESS_CITY',
						),
						array(
							'name' => 'shipping_address_state',
							'css_class' => 'address_state',
							'placeholder' => 'LBL_SHIPPING_ADDRESS_STATE',
						),
						array(
							'name' => 'shipping_address_postalcode',
							'css_class' => 'address_zip',
							'placeholder' => 'LBL_SHIPPING_ADDRESS_POSTALCODE',
						),
						array(
							'name' => 'shipping_address_country',
							'css_class' => 'address_country',
							'placeholder' => 'LBL_SHIPPING_ADDRESS_COUNTRY',
						),
					),
				),
				array(
					'name' => 'line_items',
					'type' => 'items',
					'readonly' => true,
					'span' => 12,
				),
				array(
					'name' => 'ship_date',
					'readonly' => true,
				),
				array(
					'name' => 'subtotal',
					'readonly' => true,
				),
			),
		),
	),
);
