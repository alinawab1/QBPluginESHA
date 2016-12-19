<?php

/**
 * @class LogError
 * @description Catching and handling all the QuickBooks error
 * 
 */

class LogError {

	/**
	 * Catch and handle a "internal server" error (err no. 500) from QuickBooks
	 * 
	 * @param string $requestID			
	 * @param string $action
	 * @param mixed $ID
	 * @param mixed $extra
	 * @param string $err
	 * @param string $xml
	 * @param mixed $errnum
	 * @param string $errmsg
	 * @return void
	 */
	static public function _quickbooks_500_error($requestID, $user, $action, $ID, $extra, &$err, $xml, $errnum, $errmsg) {
		$GLOBALS['log']->fatal("User: $user action: $action");
		$GLOBALS['log']->fatal("QB Error: $errmsg");
		$userId = getUserId($user);
		logMe("Catch & handle error: $action", "Error Message: $errmsg", $userId);
		return true;
	}

	/**
	 * Catch and handle a "that string is too long for that field" error (err no. 3070) from QuickBooks
	 * 
	 * @param string $requestID			
	 * @param string $action
	 * @param mixed $ID
	 * @param mixed $extra
	 * @param string $err
	 * @param string $xml
	 * @param mixed $errnum
	 * @param string $errmsg
	 * @return void
	 */
	static public function _quickbooks_error_stringtoolong($requestID, $user, $action, $ID, $extra, &$err, $xml, $errnum, $errmsg) {
		$GLOBALS['log']->fatal("User: $user action: $action");
		$GLOBALS['log']->fatal("QB Error: $errmsg");
		$userId = getUserId($user);
		logMe("Catch & handle error: $action", "Error Message: $errmsg", $userId);
		return true;
	}

	static public function _quickbooks_error_convertingField($requestID, $user, $action, $ID, $extra, &$err, $xml, $errnum, $errmsg) {
		$GLOBALS['log']->fatal("User: $user action: $action");
		$GLOBALS['log']->fatal("QB Error: $errmsg");
		$userId = getUserId($user);
		logMe("Catch & handle error: $action", "Error Message: $errmsg", $userId);
		return true;
	}

	/**
	 * Catch and handle a CustomQuery error
	 * 
	 * @param string $requestID			
	 * @param string $action
	 * @param mixed $ID
	 * @param mixed $extra
	 * @param string $err
	 * @param string $xml
	 * @param mixed $errnum
	 * @param string $errmsg
	 * @return void
	 */
	static public function _quickbooks_error_customerAdd($requestID, $user, $action, $ID, $extra, &$err, $xml, $errnum, $errmsg) {
		$GLOBALS['log']->fatal("$user - $ID: Inside Error Customer Add");
		$GLOBALS['log']->fatal("QB Error: $errmsg");
		$userId = getUserId($user);
		logMe("Catch & handle error: $action", "Error occurred while adding customer in QB\nError Message: $errmsg", $userId);
		if ($ID == 'CSMA' && count($extra) > 0) {
			$aclogic = new ACLogic($user);
			foreach ($extra as $key => $ext) {
				$extra[$key]['qb_id'] = '';
				$extra[$key]['EditSequence'] = '';
			}
			$aclogic->queue->enqueue(QUICKBOOKS_QUERY_CUSTOMER, 'CSMUQ', $aclogic->Upriority, $extra, $aclogic->user);
		}
		return true;
	}

	static public function _quickbooks_error_customerMod($requestID, $user, $action, $ID, $extra, &$err, $xml, $errnum, $errmsg) {
		$GLOBALS['log']->fatal("User: $user - ident: $ID - action: $action (Inside Error Customer Mod)");
		$GLOBALS['log']->fatal("QB Error: $errmsg");
		$userId = getUserId($user);
		logMe("Catch & handle error: $action", "Error occurred while updating customer in QB\nError Message: $errmsg", $userId);
		return true;
	}

	/**
	 * Catch and handle a CustomQuery error
	 * 
	 * @param string $requestID			
	 * @param string $action
	 * @param mixed $ID
	 * @param mixed $extra
	 * @param string $err
	 * @param string $xml
	 * @param mixed $errnum
	 * @param string $errmsg
	 * @return void
	 */
	static public function _quickbooks_error_customerQuery($requestID, $user, $action, $ID, $extra, &$err, $xml, $errnum, $errmsg) {
		$GLOBALS['log']->fatal("$user - $ID: Inside Error Customer Query");
		$GLOBALS['log']->fatal("QB Error: $errmsg");
		$xmlArray = xmlstr_to_array($xml);
		if ($ID == 'CSMQ') {
			return true;
		}
		$data = array();
		$dataArray = $xmlArray['QBXMLMsgsRs']['CustomerQueryRs'];
		if (isset($dataArray['CustomerRet'][0])) {
			$data = $dataArray['CustomerRet'];
		} else {
			$data[] = $dataArray['CustomerRet'];
		}
		$GLOBALS['log']->fatal("$user Records Count: " . count($data));
		if (count($data) > 0 && count($extra) > 0) {
			foreach ($data as $record) {
				$sugarID = '';
				try {
					foreach ($extra as $key => $rec) {
						if (html_entity_decode($rec['name']) == $record['Name']) {
							$sugarID = $rec['id'];
							unset($extra[$key]);
							break;
						}
					}
					if (!empty($sugarID)) {
						updateQBId("Accounts", $sugarID, $record['ListID']);
					}
				} catch (Exception $e) {
					logMe("Exception while mapping Account records", "Error Message: {$e->getMessage()}", $userId);
				}
			}
			$aclogic = new ACLogic($user);
			$aclogic->getAddReq($extra);
			$aclogic->getUpdateReq();
		}
		return true;
	}

	static public function _quickbooks_error_itemQuery($requestID, $user, $action, $ID, $extra, &$err, $xml, $errnum, $errmsg) {
		$GLOBALS['log']->fatal("User: $user - ident: $ID - action: $action (Inside Error Item Query)");
		$GLOBALS['log']->fatal("QB Error! $errmsg");
		$xmlArray = xmlstr_to_array($xml);
		$config = new Configuration($user);
		$config->getUserId();
		//loading the configuration
		$config->loadConfiguration($config->userId);
		if ($ID == 'ITMQ') {
			if ($config->qbConfig['sales_history_invoice'] != '1') {
				updateField("qbconfig", "qb_last_sync_date", getSyncDate("qb_sync_start", $config->userId), $config->userId);
			}
			return true;
		}
		$productArray = $xmlArray['QBXMLMsgsRs']['ItemQueryRs'];
		$productsData = PILogic::getProductArray($productArray);
		$productCount = PILogic::getProductCount($productsData);
		if ($productCount > 0 && count($extra) > 0) {
			$GLOBALS['log']->fatal("Records Mapped Count: $productCount");
			foreach ($productsData as $products) {
				foreach ($products as $record) {
					$sugarID = '';
					try {
						foreach ($extra as $key => $rec) {
							if (html_entity_decode($rec['name']) == $record['Name']) {
								$sugarID = $rec['id'];
								unset($extra[$key]);
								break;
							}
						}
						if (!empty($sugarID)) {
							updateQBId("ProductTemplates", $sugarID, $record['ListID']);
						}
					} catch (Exception $e) {
						logMe("Exception while mapping Account records", "Error Message: {$e->getMessage()}", $config->userId);
					}
				}
			}
		}
		if (count($extra) > 0) {
			$config->getQBFileId();
			$pilogic = new PILogic($config);
			$pilogic->getAddReq($extra);
		}
		return true;
	}

	static public function _quickbooks_error_invoiceQuery($requestID, $user, $action, $ID, $extra, &$err, $xml, $errnum, $errmsg) {
		$userId = getUserId($user);
		$GLOBALS['log']->fatal("User: $user - ident: $ID - action: $action (Inside Error Paid Invoice Query)");
		$GLOBALS['log']->fatal("QB Error! $errmsg");
		updateField("qbconfig", "qb_last_sync_date", getSyncDate("qb_sync_start", $userId), $userId);
		return true;
	}

	static public function _quickbooks_error_estimateAdd($requestID, $user, $action, $ID, $extra, &$err, $xml, $errnum, $errmsg) {
		$userId = getUserId($user);
		$GLOBALS['log']->fatal("User: $user action: $action (Cought Error while creating Estimate)");
		$GLOBALS['log']->fatal("QB Error: $errmsg");
		logMe("Catch & handle error: $action", "Error occurred while adding Estimate in QB\nError Message: $errmsg", $userId);
		return true;
	}

	static public function _quickbooks_error_salesOrderAdd($requestID, $user, $action, $ID, $extra, &$err, $xml, $errnum, $errmsg) {
		$userId = getUserId($user);
		$GLOBALS['log']->fatal("User: $user action: $action (Cought Error while creating Sales Order)");
		$GLOBALS['log']->fatal("QB Error: $errmsg");
		logMe("Catch & handle error: $action", "Error occurred while adding Sales Order in QB\nError Message: $errmsg", $userId);
		return true;
	}
	static public function _quickbooks_error_invoiceAdd($requestID, $user, $action, $ID, $extra, &$err, $xml, $errnum, $errmsg) {
		$userId = getUserId($user);
		$GLOBALS['log']->fatal("User: $user action: $action (Cought Error while creating Invoice)");
		$GLOBALS['log']->fatal("QB Error: $errmsg");
		logMe("Catch & handle error: $action", "Error occurred while adding Sales Order in QB\nError Message: $errmsg", $userId);
		return true;
	}

	static public function _quickbooks_error_catchAll($requestID, $user, $action, $ID, $extra, &$err, $xml, $errnum, $errmsg) {
		$userId = getUserId($user);
		$GLOBALS['log']->fatal("User: $user action: $action");
		$GLOBALS['log']->fatal("QB Error: $errmsg");
		logMe("Catch & handle error: $action", "Catching all Errors\nError Message: $errmsg", $userId);
		return true;
	}

}
