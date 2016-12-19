<?php

/**
 * 
 * @author Keith Palmer <keith@consolibyte.com>
 * 
 * @package QuickBooks
 * @subpackage Documentation
 */
//error_reporting(E_ALL | E_STRICT);
//ini_set('display_errors', 1);
// Require the framework and other classes
require_once 'QuickBooks.php';
require_once 'classes/AccountCustomer.php';
require_once 'classes/ProductItem.php';
require_once 'classes/QuoteEstimate.php';
require_once 'classes/QuoteSalesOrder.php';
require_once 'classes/QuoteInvoice.php';
require_once 'classes/InvoiceSalesHistory.php';
require_once 'classes/ACLogic.php';
require_once 'classes/PILogic.php';
require_once 'classes/QESLogic.php';
require_once 'classes/ISHLogic.php';
require_once 'classes/Configuration.php';
require_once 'classes/LogError.php';
require_once 'classes/PreRequests.php';

$user = 'admin';
$pass = 'admin99';
// Map QuickBooks actions to handler functions
$map = array(
	QUICKBOOKS_ADD_CUSTOMER => array('AccountCustomer::_quickbooks_customer_add_request', 'AccountCustomer::_quickbooks_customer_add_response'),
	QUICKBOOKS_MOD_CUSTOMER => array('AccountCustomer::_quickbooks_customer_update_request', 'AccountCustomer::_quickbooks_customer_update_response'),
	QUICKBOOKS_QUERY_CUSTOMER => array('AccountCustomer::_quickbooks_customer_query_request', 'AccountCustomer::_quickbooks_customer_query_response'),
	QUICKBOOKS_QUERY_ITEM => array('ProductItem::_quickbooks_item_query_request', 'ProductItem::_quickbooks_item_query_response'),
	QUICKBOOKS_ADD_INVENTORYITEM => array('ProductItem::_quickbooks_inventory_add_request', 'ProductItem::_quickbooks_inventory_add_response'),
	QUICKBOOKS_MOD_INVENTORYITEM => array('ProductItem::_quickbooks_inventory_update_request', 'ProductItem::_quickbooks_inventory_update_response'),
	QUICKBOOKS_QUERY_INVENTORYITEM => array('ProductItem::_quickbooks_inventory_query_request', 'ProductItem::_quickbooks_inventory_query_response'),
	QUICKBOOKS_ADD_NONINVENTORYITEM => array('ProductItem::_quickbooks_noninventory_add_request', 'ProductItem::_quickbooks_noninventory_add_response'),
	QUICKBOOKS_MOD_NONINVENTORYITEM => array('ProductItem::_quickbooks_noninventory_update_request', 'ProductItem::_quickbooks_noninventory_update_response'),
	QUICKBOOKS_QUERY_NONINVENTORYITEM => array('ProductItem::_quickbooks_noninventory_query_request', 'ProductItem::_quickbooks_noninventory_query_response'),
	QUICKBOOKS_ADD_SERVICEITEM => array('ProductItem::_quickbooks_service_add_request', 'ProductItem::_quickbooks_service_add_response'),
	QUICKBOOKS_MOD_SERVICEITEM => array('ProductItem::_quickbooks_service_update_request', 'ProductItem::_quickbooks_service_update_response'),
	QUICKBOOKS_QUERY_SERVICEITEM => array('ProductItem::_quickbooks_service_query_request', 'ProductItem::_quickbooks_service_query_response'),
	QUICKBOOKS_ADD_OTHERCHARGEITEM => array('ProductItem::_quickbooks_other_add_request', 'ProductItem::_quickbooks_other_add_response'),
	QUICKBOOKS_MOD_OTHERCHARGEITEM => array('ProductItem::_quickbooks_other_update_request', 'ProductItem::_quickbooks_other_update_response'),
	QUICKBOOKS_QUERY_OTHERCHARGEITEM => array('ProductItem::_quickbooks_other_query_request', 'ProductItem::_quickbooks_other_query_response'),
	QUICKBOOKS_ADD_ESTIMATE => array('QuoteEstimate::_quickbooks_estimate_add_request', 'QuoteEstimate::_quickbooks_estimate_add_response'),
	QUICKBOOKS_ADD_SALESORDER => array('QuoteSalesOrder::_quickbooks_salesorder_add_request', 'QuoteSalesOrder::_quickbooks_salesorder_add_response'),
	QUICKBOOKS_ADD_INVOICE => array('QuoteInvoice::_quickbooks_invoice_add_request', 'QuoteInvoice::_quickbooks_invoice_add_response'),
	QUICKBOOKS_QUERY_INVOICE => array('InvoiceSalesHistory::_quickbooks_invoice_query_request', 'InvoiceSalesHistory::_quickbooks_invoice_query_response'),
		//QUICKBOOKS_ADD_SALESRECEIPT => array( '_quickbooks_salesreceipt_add_request', '_quickbooks_salesreceipt_add_response' ), 
		//'*' => array( '_quickbooks_customer_add_request', '_quickbooks_customer_add_response' ), 
);

// This is entirely optional, use it to trigger actions when an error is returned by QuickBooks
$errmap = array(
	3070 => 'LogError::_quickbooks_error_stringTooLong', // Whenever a string is too long to fit in a field, call this function: _quickbooks_error_stringtolong()
	3040 => 'LogError::_quickbooks_error_convertingField', // Whenever a converting field value to fit in a field, call this function: _quickbooks_error_convertingField()
	//500 => 'LogError::_quickbooks_500_error',
	'CustomerQuery' => 'LogError::_quickbooks_error_customerQuery', // Whenever an error occurs while trying to perform an 'QueryCustomer' action, call this function: _quickbooks_error_customerQuery()
	'CustomerAdd' => 'LogError::_quickbooks_error_customerAdd', // Whenever an error occurs while trying to perform an 'AddCustomer' action, call this function: _quickbooks_error_customerAdd()
	'CustomerMod' => 'LogError::_quickbooks_error_customerMod', // Whenever an error occurs while trying to perform an 'ModCustomer' action, call this function: _quickbooks_error_customerMod()
	'ItemQuery' => 'LogError::_quickbooks_error_itemQuery', // Whenever an error occurs while trying to perform an 'QueryItem' action, call this function: _quickbooks_error_itemQuery()
	'EstimateAdd' => 'LogError::_quickbooks_error_estimateAdd', // Whenever an error occurs while trying to perform an 'AddEstimate' action, call this function: _quickbooks_error_estimateAdd()
	'SalesorderAdd' => 'LogError::_quickbooks_error_salesOrderAdd', // Whenever an error occurs while trying to perform an 'AddSalesOrder' action, call this function: _quickbooks_error_salesOrderAdd()
	'InvoiceAdd' => 'LogError::_quickbooks_error_invoiceAdd', // Whenever an error occurs while trying to perform an 'AddInvoice' action, call this function: _quickbooks_error_invoiceAdd()
	'InvoiceQuery' => 'LogError::_quickbooks_error_invoiceQuery', // Whenever an error occurs while trying to perform an 'QueryInvoice' action, call this function: _quickbooks_error_invoiceQuery()
	'*' => 'LogError::_quickbooks_error_catchAll', // Using a key value of '*' will catch any errors which were not caught by another error handler
		// ... more error handlers here ...
);
// An array of callback hooks
$hooks = array(
	QuickBooks_WebConnector_Handlers::HOOK_LOGINSUCCESS => '_quickbooks_hook_loginSuccess', // Run this function whenever a successful login occurs
	QUICKBOOKS_HANDLERS_HOOK_CLOSECONNECTION => '_quickbooks_hook_closeConnection', // Run this function whenever connection closes
	QUICKBOOKS_HANDLERS_HOOK_CONNECTIONERROR => '_quickbooks_hook_connectionError', // Run this function whenever connection closes
//	QUICKBOOKS_HANDLERS_HOOK_GETLASTERROR => '_quickbooks_hook_getLastError', // Run this function to get the Last Error
	QUICKBOOKS_HANDLERS_HOOK_SENDREQUESTXML => '_quickbooks_hook_sendRequestXML', // Run this function to get the Last Error
);

/**
 * 
 * @param type $requestID
 * @param type $user
 * @param type $hook
 * @param type $err
 * @param type $hook_data
 * @param type $callback_config
 * @return boolean
 */
function _quickbooks_hook_loginSuccess($requestID, $user, $hook, &$err, $hook_data, $callback_config) {
	$GLOBALS['log']->fatal("** User($user) Login Success! Sync Starts **");
	global $current_user;
	$aclogic = new ACLogic($user);
	$preRequests = new PreRequests($user);
	//The following function "duplicateFix" make all accounts records unique if there is any duplicate, it append 1,2,3.... with duplicate names
	//$preRequests->duplicateFix();
	$current_user->id = $aclogic->config->userId;
	$qbConfig = $aclogic->qbConfig;
	if (!empty($qbConfig) && !empty($aclogic->config->qbFileId)) {
		// Validating License
		if(!$aclogic->config->validateLicense()) {
		//if(false) {
			logMe("QB License Expired", "QBSync License Key has been expired for User($user)", $aclogic->config->userId);
			$GLOBALS['log']->fatal("QBSync License Key has been expired for User($user)");
			return true;
		}
		$aclogic->config->cleanQueue();
		if ($qbConfig['account_customer'] == '0' && $qbConfig['customer_account'] == '0') {
			$GLOBALS['log']->fatal("Disabled! Sync Accounts/Customer for User($user)");
			return true;
		} else {
                        $preRequests->createShippingProduct();
						//relateAccQbFileAction$preRequests->createShortNameForProductTemplates();
			// Process for Customers
			if ($qbConfig['account_customer'] == '1' && $qbConfig['qb_customer_create'] == '1') {
				$aclogic->getAddReq();
			}
			if ($qbConfig['account_customer'] == '1') {
				$aclogic->getUpdateReq();
			}
                       // $GLOBALS['log']->fatal(print_r($qbConfig,true));
			// Process for Product Items
			if ($qbConfig['product_item'] == '1') {
				$pilogic = new PILogic($aclogic);
				$pilogic->getAddReq();
				$pilogic->getUpdateReq();
			}
			if ($qbConfig['product_item'] == '0' && $qbConfig['item_product'] == '0') {
				$GLOBALS['log']->fatal("Products Sync Disabled! for User($user)");
			}
			// Process for Quotes Estimates
			if ($qbConfig['quotes_estimate'] == '1') {
				$qeslogic = new QESLogic($aclogic);
				$qeslogic->getAddReq();
			} else {
				$GLOBALS['log']->fatal("Quotes Sync Disabled! for User($user)");
			}
			// Process for getting data from QuickBooks
			updateField("qbconfig", "qb_sync_start", $GLOBALS['timedate']->nowDb(), $aclogic->config->userId);
			// Customers
			if ($qbConfig['customer_account'] == '1') {
				$aclogic->getQueryReq();
			}
			// Items
			if ($qbConfig['item_product'] == '1') {
				//$pilogic->getQueryReq();
			}
			// Paid Invoices
			if ($qbConfig['sales_history_invoice'] == '1') {
				$ishlogic = new ISHLogic($aclogic);
				$ishlogic->getQueryReq();
			} else {
				$GLOBALS['log']->fatal("Paid Invoices Sync Disabled! for User($user)");
			}
		}
	} else {
		$GLOBALS['log']->fatal("Configuration Error! No Configuration OR QBFile Exists for User($user)");
	}
	return true;
}

function _quickbooks_hook_sendRequestXML($requestID, $user, $hook, &$err, $hook_data, $callback_config) {
	$companyPath = $hook_data['strCompanyFileName'];
	updateCompanyPath($user, $companyPath);
	return true;
}

function _quickbooks_hook_closeConnection($requestID, $user, $hook, &$err, $hook_data, $callback_config) {
	logMe("Sync Ends: Connection Close", "Sync ends, connection is getting close for User ($user)", getUserId($user));
	$GLOBALS['log']->fatal("*** Sync Ends! Connection Closing ***");
	return true;
}

function _quickbooks_hook_connectionError($requestID, $user, $hook, &$err, $hook_data, $callback_config) {
	logMe("Connection Error", "Found Error in Connection with QuickBooks User($user)\n Error Message: {$hook_data['message']}", getUserId($user));
	$GLOBALS['log']->fatal("********* Connection Error **********");
	return true;
}

$log_level = QUICKBOOKS_LOG_DEBUG;
$soapserver = QUICKBOOKS_SOAPSERVER_BUILTIN;  // A pure-PHP SOAP server (no PHP ext/soap extension required, also makes debugging easier)
$soap_options = array();
$handler_options = array(
	'deny_concurrent_logins' => false,
	'deny_reallyfast_logins' => false,
);
$driver_options = array(
		//'max_log_history' => 1024,	// Limit the number of quickbooks_log entries to 1024
		//'max_queue_history' => 64, 	// Limit the number of *successfully processed* quickbooks_queue entries to 64
);
$callback_options = array();
global $sugar_config;

// * MAKE SURE YOU CHANGE THE DATABASE CONNECTION STRING BELOW TO A VALID MYSQL USERNAME/PASSWORD/HOSTNAME *
$dsn = "{$sugar_config['dbconfig']['db_type']}://{$sugar_config['dbconfig']['db_user_name']}:{$sugar_config['dbconfig']['db_password']}@{$sugar_config['dbconfig']['db_host_name']}/{$sugar_config['dbconfig']['db_name']}";

// __construct($dsn_or_conn, $map, $errmap = array(), $hooks = array(), $log_level = QUICKBOOKS_LOG_NORMAL, $soap = QUICKBOOKS_SOAPSERVER_PHP, $wsdl = QUICKBOOKS_WSDL, $soap_options = array(), $handler_options = array(), $driver_options = array(), $callback_options = array()
$Server = new QuickBooks_WebConnector_Server($dsn, $map, $errmap, $hooks, $log_level, $soapserver, QUICKBOOKS_WSDL, $soap_options, $handler_options, $driver_options, $callback_options);
$response = $Server->handle(true, true);
