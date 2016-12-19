<?php

require_once 'utils.php';

/**
 * @file QuoteEstimate.php
 */
class QuoteEstimate {

	static public function _quickbooks_estimate_add_request($requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $version, $locale) {
		$GLOBALS['log']->fatal("User: $user - ident: $ID - action: $action (Inside Estimate Add Request)");
		$qbxml = '';
		if (isset($extra['id']) && !empty($extra['id'])) {
			$ids = array();
			$ids[] = $extra['id'];
			$quoteData = getQuotesData("Quotes", array('where_ids' => $ids));
			if (count($quoteData) > 0) {
				$quoteXML = QESLogic::prepareEstimateXML($quoteData,$user);
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

	static public function _quickbooks_estimate_add_response($requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $xml, $idents) {
		$GLOBALS['log']->fatal("User: $user - ident: $ID - action: $action (Inside Estimate Add Response)");
		$xmlArray = xmlstr_to_array($xml);
		$quoteArray = $xmlArray['QBXMLMsgsRs']['EstimateAddRs'];
		if(isset($quoteArray[0])) {
			$quoteData = $quoteArray;
		} else {
			$quoteData[] = $quoteArray;
		}
		if (count($quoteData) > 0 && isset($extra['id'])) {
			foreach ($quoteData as $quote) {
				$GLOBALS['log']->fatal('Customer (' . htmlspecialchars_decode($extra['CustomerName'], ENT_QUOTES) . ') Estimate Created in QB');
				$sugarID = $extra['id'];
				if (!empty($sugarID)) {
                                        $invoice_id=$idents['RefNumber'];
					updateQBId("Quotes", $sugarID, $quote['EstimateRet']['TxnID'],$invoice_id);
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
