<?php

require_once 'utils.php';

/**
 * @file QuoteSalesOrder.php
 * @class QuoteSalesOrder
 */
class InvoiceSalesHistory {

	static public function _quickbooks_invoice_query_request($requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $version, $locale) {
		$GLOBALS['log']->fatal("User: $user - ident: $ID - action: $action (Inside Paid Invoice Query Request)");
		$qbxml = '<?xml version="1.0" encoding="utf-8"?>
<?qbxml version="12.0"?>
<QBXML>
<QBXMLMsgsRq onError="continueOnError">';
		if ($ID == 'INVQ') {
			$iterator = !empty($extra['iterator']) ? $extra['iterator'] : 'Start';
			$iteratorID = !empty($extra['iteratorID']) ? 'iteratorID="' . $extra['iteratorID'] . '"' : '';
			$qbxml .= '<InvoiceQueryRq requestID="' . $requestID . '" iterator="' . $iterator . '" ' . $iteratorID . '>';
			$qbxml .= '<MaxReturned>2</MaxReturned>';
			$config = new Configuration($user);
			$config->getUserId();
			$tz = empty($config->qbConfig['qb_timezone']) ? 'UTC' : $config->qbConfig['qb_timezone'];
			date_default_timezone_set($tz);
			$timedate = new TimeDate();
			if (!empty($config->userId)) {
				$config->loadConfiguration($config->userId);
			}
			$fromDate = getSyncDate("qb_last_sync_date", $config->userId);
			$converted_date = $timedate->handle_offset($fromDate, "Y-m-d H:i:sP", true, null, $config->qbConfig['qb_timezone']);
			$qbxml .= '<ModifiedDateRangeFilter><FromModifiedDate >' . str_replace(' ', 'T', $converted_date) . '</FromModifiedDate></ModifiedDateRangeFilter>'; //2014-12-19T00:00:00
		}
		$qbxml .= "<PaidStatus >PaidOnly</PaidStatus>"
				. "<IncludeLineItems >1</IncludeLineItems>"
				. "<IncludeLinkedTxns >1</IncludeLinkedTxns>"
				. "</InvoiceQueryRq></QBXMLMsgsRq></QBXML>";
		$GLOBALS['log']->fatal("User: $user - Action: $ID XML: " . removeLines($qbxml));
		return $qbxml;
	}

	static public function _quickbooks_invoice_query_response($requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $xml, $idents) {
                $GLOBALS['log']->fatal("User: $user - ident: $ID - action: $action (Inside Paid Invoice Query Response)");
		$data = array();
		$config = new Configuration($user);
		$config->getUserId();
		$config->getQBFileId();
		//loading the configuration
		$config->loadConfiguration($config->userId);
		$xmlArray = xmlstr_to_array($xml);
		$dataArray = $xmlArray['QBXMLMsgsRs']['InvoiceQueryRs'];
		$properties = $dataArray['@attributes'];
		if (isset($dataArray['InvoiceRet'][0])) {
			$data = $dataArray['InvoiceRet'];
		} else {
			$data[] = $dataArray['InvoiceRet'];
		}
		$GLOBALS['log']->fatal('Paid Invoices Count: ' . count($data));
		logMe("Paid Invoices Response", "Response of Invoice Query\n" . count($data) . " records found in QB", $config->userId);
		if ($ID == "INVQ" && count($data) > 0) {
			$ishlogic = new ISHLogic($config);
			createSalesHistory($data, $ishlogic->config);
			if (isset($properties['iteratorRemainingCount']) && $properties['iteratorRemainingCount'] > 0) {
				$iterator = array(
					'iteratorID' => $properties['iteratorID'],
					'iterator' => 'Continue',
				);
				$ishlogic->queue->enqueue(QUICKBOOKS_QUERY_INVOICE, 'INVQ', $ishlogic->INVQpriority, $iterator, $user);
			} else {
				updateField("qbconfig", "qb_last_sync_date", getSyncDate("qb_sync_start", $config->userId), $config->userId);
			}
		}
	}

}
