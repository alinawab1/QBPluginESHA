<?php

/**
 * Accounts and Contacts 1-1 relationship for primary contact
 */
$dictionary["accounts_p_contacts"] = array(
	'true_relationship_type' => 'one-to-one',
	'relationships' => array(
		'accounts_p_contacts' => array(
			'lhs_module' => 'Contacts',
			'lhs_table' => 'contacts',
			'lhs_key' => 'id',
			'rhs_module' => 'Accounts',
			'rhs_table' => 'accounts',
			'rhs_key' => 'id',
			'relationship_type' => 'many-to-many',
			'join_table' => 'accounts_p_contacts',
			'join_key_lhs' => 'primary_contact_id',
			'join_key_rhs' => 'primary_account_id',
		),
	),
	'table' => 'accounts_p_contacts',
	'fields' => array(
		0 => array(
			'name' => 'id',
			'type' => 'varchar',
			'len' => 36,
		),
		1 => array(
			'name' => 'date_modified',
			'type' => 'datetime',
		),
		2 => array(
			'name' => 'deleted',
			'type' => 'bool',
			'len' => '1',
			'default' => '0',
			'required' => true,
		),
		3 => array(
			'name' => 'primary_contact_id',
			'type' => 'varchar',
			'len' => 36,
		),
		4 => array(
			'name' => 'primary_account_id',
			'type' => 'varchar',
			'len' => 36,
		),
	),
	'indices' => array(
		0 => array(
			'name' => 'accounts_p_contactsspk',
			'type' => 'primary',
			'fields' => array(
				0 => 'id',
			),
		),
		1 => array(
			'name' => 'primary_contact_id1',
			'type' => 'index',
			'fields' => array(
				0 => 'primary_contact_id',
			),
		),
		2 => array(
			'name' => 'primary_contact_id2',
			'type' => 'index',
			'fields' => array(
				0 => 'primary_account_id',
			),
		),
	),
);
