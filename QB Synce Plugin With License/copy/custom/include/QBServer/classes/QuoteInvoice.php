<?php

require_once 'utils.php';

/**
 * @file QuoteInvoice.php
 */
class QuoteInvoice {

	static public function _quickbooks_invoice_add_request($requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $version, $locale) {
		$GLOBALS['log']->fatal("User: $user - ident: $ID - action: $action (Inside Invoice Add Request)");
		$qbxml = '';
		if (isset($extra['id']) && !empty($extra['id'])) {
			$ids = array();
			$ids[] = $extra['id'];
			$quoteData = getQuotesData("Quotes", array('where_ids' => $ids));
			if (count($quoteData) > 0) {
				$quoteXML = QESLogic::prepareInvoiceXML($quoteData,$user);
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

	static public function _quickbooks_invoice_add_response($requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $xml, $idents) {
            $GLOBALS['log']->fatal("User: $user - ident: $ID - action: $action (Inside Invoice Add Response)");
		$xmlArray = xmlstr_to_array($xml);
		$quoteArray = $xmlArray['QBXMLMsgsRs']['InvoiceAddRs'];
		if(isset($quoteArray[0])) {
			$quoteData = $quoteArray;
		} else {
			$quoteData[] = $quoteArray;
		}
		if (count($quoteData) > 0 && isset($extra['id'])) {
			foreach ($quoteData as $quote) {
				$GLOBALS['log']->fatal('Customer (' . htmlspecialchars_decode($extra['CustomerName'], ENT_QUOTES) . ') Invoice Created in QB');
				$sugarID = $extra['id'];
				if (!empty($sugarID)) {
                                    $invoice_id=$idents['RefNumber'];
                                     $GLOBALS['log']->fatal("invoice_id in: $invoice_id");
                                    updateQBId("Quotes", $sugarID, $quote['InvoiceRet']['TxnID'],$invoice_id);
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
