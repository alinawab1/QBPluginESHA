<?php

require_once 'utils.php';

/**
 * @file QuoteSalesOrder.php
 */
class QuoteSalesOrder {

	static public function _quickbooks_salesorder_add_request($requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $version, $locale) {
		$GLOBALS['log']->fatal("User: $user - ident: $ID - action: $action (Inside Sales Order Add Request)");
		$qbxml = '';
		if (isset($extra['id']) && !empty($extra['id'])) {
			$ids = array();
			$ids[] = $extra['id'];
			$quoteData = getQuotesData("Quotes", array('where_ids' => $ids));
			if (count($quoteData) > 0) {
				$quoteXML = QESLogic::prepareSalesOrderXML($quoteData,$user);
				$qbxml = '<?xml version="1.0" encoding="utf-8"?>
		<?qbxml version="12.0"?>
		<QBXML>
			<QBXMLMsgsRq onError="continueOnError">
				' . str_replace('$requestID', $requestID, $quoteXML) . '
			</QBXMLMsgsRq>
		</QBXML>';
				$GLOBALS['log']->fatal("User: $user - Action: $ID XML: " . removeLines($qbxml));
			} else {
				$GLOBALS['log']->fatal("Warning! No Data found for Request process");
			}
		}
		return $qbxml;
	}

	static public function _quickbooks_salesorder_add_response($requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $xml, $idents) {
		$GLOBALS['log']->fatal("User: $user - ident: $ID - action: $action (Inside Sales Order Add Response)");
		$xmlArray = xmlstr_to_array($xml);
		$quoteArray = $xmlArray['QBXMLMsgsRs']['SalesOrderAddRs'];
		if(isset($quoteArray[0])) {
			$quoteData = $quoteArray;
		} else {
			$quoteData[] = $quoteArray;
		}
		if (count($quoteData) > 0 && isset($extra['id'])) {
			foreach ($quoteData as $quote) {
				$GLOBALS['log']->fatal('Customer (' . htmlspecialchars_decode($extra['CustomerName'], ENT_QUOTES) . ') Sales Order Created in QB');
				$sugarID = $extra['id'];
				if (!empty($sugarID)) {
                                        $invoice_id=$idents['RefNumber'];
					updateQBId("Quotes", $sugarID, $quote['SalesOrderRet']['TxnID'],$invoice_id);
				}
			}
			$config = new Configuration($user);
			$config->getUserId();
			$config->getQBFileId();
			//loading the configuration
			$config->loadConfiguration($config->userId);
			$qeslogic = new QESLogic($config);
			$qeslogic->getAddReq();
		}
	}

}
