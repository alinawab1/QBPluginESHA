<?php

require_once 'utils.php';

/**
 * @file AccountCustomer.php
 */
class AccountCustomer {

    /**
     * This means that every time QuickBooks tries to process a 
     * QUICKBOOKS_ADD_CUSTOMER action, it will call the 
     * '_quickbooks_customer_add_request' function, expecting that function to 
     * generate a valid qbXML request which can be processed. So, this function 
     * will generate a qbXML CustomerAddRq which tells QuickBooks to add a 
     * customer. 
     * 
     * @param string $requestID					You should include this in your qbXML request (it helps with debugging later)
     * @param string $action					The QuickBooks action being performed (CustomerAdd in this case)
     * @param mixed $ID							The unique identifier for the record (maybe a customer ID number in your database or something)
     * @param array $extra						Any extra data you included with the queued item when you queued it up
     * @param string $err						An error message, assign a value to $err if you want to report an error
     * @param integer $last_action_time			A unix timestamp (seconds) indicating when the last action of this type was dequeued (i.e.: for CustomerAdd, the last time a customer was added, for CustomerQuery, the last time a CustomerQuery ran, etc.)
     * @param integer $last_actionident_time	A unix timestamp (seconds) indicating when the combination of this action and ident was dequeued (i.e.: when the last time a CustomerQuery with ident of get-new-customers was dequeued)
     * @param float $version					The max qbXML version your QuickBooks version supports
     * @param string $locale					
     * @return string							A valid qbXML request
     */
    static public function _quickbooks_customer_add_request($requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $version, $locale) {
        $GLOBALS['log']->fatal("User: $user - ident: $ID - action: $action (Inside Customer Add Request)");
        $ids = array();
        if (count($extra) > 0) {
            foreach ($extra as $record) {
                $ids[] = $record['id'];
            }
            $data = getAccountData("Accounts", array('where_ids' => $ids, 'join' => 'pcontacts'));
            if (count($data) > 0) {
                foreach ($data as $key => $account) {
                    if (!empty($account['p_contact_id'])) {
                        $data[$key]['Contacts'] = getContactData("Contacts", array('whereRaw' => "contacts.id='{$account['p_contact_id']}'"));
                    }
                }
            }
            $opp_data = getOpportunityData("Opportunities", array('where_ids' => $ids));
            $opp_name=array();
            foreach ($opp_data as $opp) {
                if(!in_array($opp['name'], $opp_name)){
                $opp_name[]=$opp['name'];
                $opp['job'] = 1;
                $data[] = $opp;
            }
        }
        }
        $GLOBALS['log']->fatal('===================================all add data===============================');
        $GLOBALS['log']->fatal(print_r($data,true));
        $accountXML = ACLogic::prepareCustomerXML($data);
        if (empty($accountXML)) {
            return '';
        }
        $qbxml = '<?xml version="1.0" encoding="utf-8"?>
		<?qbxml version="12.0"?>
		<QBXML>
			<QBXMLMsgsRq onError="continueOnError">
					' . str_replace('$requestID', $requestID, $accountXML) . '
			</QBXMLMsgsRq>
		</QBXML>';
        $GLOBALS['log']->fatal("User: $user - Action: $ID XML: " . removeLines($qbxml));
        return $qbxml;
    }

    /**
     * Receive a response from QuickBooks 
     * 
     * @param string $requestID					The requestID you passed to QuickBooks previously
     * @param string $action					The action that was performed (CustomerAdd in this case)
     * @param mixed $ID							The unique identifier of the record
     * @param array $extra			
     * @param string $err						An error message, assign a valid to $err if you want to report an error
     * @param integer $last_action_time			A unix timestamp (seconds) indicating when the last action of this type was dequeued (i.e.: for CustomerAdd, the last time a customer was added, for CustomerQuery, the last time a CustomerQuery ran, etc.)
     * @param integer $last_actionident_time	A unix timestamp (seconds) indicating when the combination of this action and ident was dequeued (i.e.: when the last time a CustomerQuery with ident of get-new-customers was dequeued)
     * @param string $xml						The complete qbXML response
     * @param array $idents						An array of identifiers that are contained in the qbXML response
     * @return void
     */
    static public function _quickbooks_customer_add_response($requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $xml, $idents) {
        $GLOBALS['log']->fatal("User: $user - ident: $ID - action: $action (Inside Customer Add Response)");
        $userId = getUserId($user);
        $data = array();
        $xmlArray = xmlstr_to_array($xml);
        $dataArray = $xmlArray['QBXMLMsgsRs'];
        if (isset($dataArray['CustomerAddRs'][0])) {
            $data = $dataArray['CustomerAddRs'];
        } else {
            $data[] = $dataArray['CustomerAddRs'];
        }
        $GLOBALS['log']->fatal("$user Records Added Successfully Count: " . count($data));
        logMe("Customer Add Response from QB", count($data) . " records added successfully", $userId);
        if (count($data) > 0 && count($extra) > 0) {
            foreach ($data as $record) {
                $sugarID = '';
                try {
                    foreach ($extra as $rec) {
                        if (htmlspecialchars_decode($rec['name'], ENT_QUOTES) == $record['CustomerRet']['Name'] || htmlspecialchars_decode($rec['name'], ENT_QUOTES) == $record['CustomerRet']['FullName']) {
                            $GLOBALS['log']->fatal('Records Mapped Name: ' . htmlspecialchars_decode($rec['name'], ENT_QUOTES));
                            $sugarID = $rec['id'];
                            break;
                        }
                    }
                    if (!empty($sugarID)) {
                        if (count($record['CustomerRet']['ParentRef']) > 0) {
                            updateQBId("Opportunities", $sugarID, $record['CustomerRet']['ListID']);
                        } else {
                            updateQBId("Accounts", $sugarID, $record['CustomerRet']['ListID']);
                        }
                    }
                } catch (Exception $e) {
                    logMe("Exception while mapping Account records", "Error Message: {$e->getMessage()}", $userId);
                }
            }
        }
    }

    /**
     * This means that every time QuickBooks tries to process a 
     * QUICKBOOKS_MOD_CUSTOMER action, it will call the 
     * '_quickbooks_customer_update_request' function, expecting that function to 
     * generate a valid qbXML request which can be processed. So, this function 
     * will generate a qbXML CustomerModRq which tells QuickBooks to add a 
     * customer. 
     * 
     * Our response function will in turn receive a qbXML response from QuickBooks 
     * which contains all of the data stored for that customer within QuickBooks. 
     * 
     * @param string $requestID					You should include this in your qbXML request (it helps with debugging later)
     * @param string $action					The QuickBooks action being performed (CustomerAdd in this case)
     * @param mixed $ID							The unique identifier for the record (maybe a customer ID number in your database or something)
     * @param array $extra						Any extra data you included with the queued item when you queued it up
     * @param string $err						An error message, assign a value to $err if you want to report an error
     * @param integer $last_action_time			A unix timestamp (seconds) indicating when the last action of this type was dequeued (i.e.: for CustomerAdd, the last time a customer was added, for CustomerQuery, the last time a CustomerQuery ran, etc.)
     * @param integer $last_actionident_time	A unix timestamp (seconds) indicating when the combination of this action and ident was dequeued (i.e.: when the last time a CustomerQuery with ident of get-new-customers was dequeued)
     * @param float $version					The max qbXML version your QuickBooks version supports
     * @param string $locale					
     * @return string							A valid qbXML request
     */
    static public function _quickbooks_customer_update_request($requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $version, $locale) {
        $GLOBALS['log']->fatal("User: $user - ident: $ID - action: $action (Inside Customer UPDATE Request)");
        $ids = array();
        $editSeq = array();
        if (count($extra) > 0) {
            foreach ($extra as $record) {
                $ids[] = $record['id'];
                $editSeq[$record['id']] = $record['EditSequence'];
            }
            $data = getAccountData("Accounts", array('where_ids' => $ids, 'join' => 'pcontacts'));

            if (count($data) > 0) {
                foreach ($data as $key => $account) {
                    $data[$key]['EditSequence'] = $editSeq[$account['id']];
                    if (!empty($account['p_contact_id'])) {
                        $data[$key]['Contacts'] = getContactData("Contacts", array('whereRaw' => "contacts.id='{$account['p_contact_id']}'"));
                    }
                }
            }
            $opp_data = getOpportunityData("Opportunities", array('where_ids' => $ids));
            foreach ($opp_data as $key => $opp) {
                if(!in_array($opp['name'], $opp_name)){
                $opp_name[]=$opp['name'];
                $opp['job'] = 1;
                $opp['EditSequence'] = $editSeq[$opp['id']];
                $data[] = $opp;
            }
        }
        }
        $accountXML = ACLogic::prepareCustomerXML($data);
        if (empty($accountXML)) {
            return '';
        }
        $qbxml = '<?xml version="1.0" encoding="utf-8"?>
		<?qbxml version="12.0"?>
		<QBXML>
			<QBXMLMsgsRq onError="continueOnError">
					' . str_replace('$requestID', $requestID, $accountXML) . '
			</QBXMLMsgsRq>
		</QBXML>';
        $GLOBALS['log']->fatal("User: $user - Action: $ID XML: " . removeLines($qbxml));
        return $qbxml;
    }

    /**
     * Receive a response from QuickBooks 
     * 
     * @param string $requestID					The requestID you passed to QuickBooks previously
     * @param string $action					The action that was performed (CustomerAdd in this case)
     * @param mixed $ID							The unique identifier of the record
     * @param array $extra			
     * @param string $err						An error message, assign a valid to $err if you want to report an error
     * @param integer $last_action_time			A unix timestamp (seconds) indicating when the last action of this type was dequeued (i.e.: for CustomerAdd, the last time a customer was added, for CustomerQuery, the last time a CustomerQuery ran, etc.)
     * @param integer $last_actionident_time	A unix timestamp (seconds) indicating when the combination of this action and ident was dequeued (i.e.: when the last time a CustomerQuery with ident of get-new-customers was dequeued)
     * @param string $xml						The complete qbXML response
     * @param array $idents						An array of identifiers that are contained in the qbXML response
     * @return void
     */
    static public function _quickbooks_customer_update_response($requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $xml, $idents) {
        $GLOBALS['log']->fatal("User: $user - ident: $ID - action: $action (Inside Customer UPDATE Response)");
        $userId = getUserId($user);
        $data = array();
        $xmlArray = xmlstr_to_array($xml);
        $dataArray = $xmlArray['QBXMLMsgsRs'];
        if (isset($dataArray['CustomerModRs'][0])) {
            $data = $dataArray['CustomerModRs'];
        } else {
            $data[] = $dataArray['CustomerModRs'];
        }
        $GLOBALS['log']->fatal("$user Records Updated Successfully Count: " . count($data));
        logMe("Customer Update Response from QB", count($data) . " Records Updated Successfully", $userId);
    }

    /**
     * This means that every time QuickBooks tries to process a 
     * QUICKBOOKS_QUERY_CUSTOMER action, it will call the 
     * '_quickbooks_customer_query_request' function, expecting that function to 
     * generate a valid qbXML request which can be processed. So, this function 
     * will generate a qbXML CustomerAddRq which tells QuickBooks to add a 
     * customer. 
     * 
     * Our response function will in turn receive a qbXML response from QuickBooks 
     * which contains all of the data stored for that customer within QuickBooks. 
     * 
     * @param string $requestID					You should include this in your qbXML request (it helps with debugging later)
     * @param string $action					The QuickBooks action being performed (CustomerAdd in this case)
     * @param mixed $ID							The unique identifier for the record (maybe a customer ID number in your database or something)
     * @param array $extra						Any extra data you included with the queued item when you queued it up
     * @param string $err						An error message, assign a value to $err if you want to report an error
     * @param integer $last_action_time			A unix timestamp (seconds) indicating when the last action of this type was dequeued (i.e.: for CustomerAdd, the last time a customer was added, for CustomerQuery, the last time a CustomerQuery ran, etc.)
     * @param integer $last_actionident_time	A unix timestamp (seconds) indicating when the combination of this action and ident was dequeued (i.e.: when the last time a CustomerQuery with ident of get-new-customers was dequeued)
     * @param float $version					The max qbXML version your QuickBooks version supports
     * @param string $locale					
     * @return string							A valid qbXML request
     */
    static public function _quickbooks_customer_query_request($requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $version, $locale) {
        $GLOBALS['log']->fatal("User: $user - ident: $ID - action: $action (Inside Customer Query Request)");
        $qbxml = '<?xml version="1.0" encoding="utf-8"?>
<?qbxml version="12.0"?>
<QBXML>
<QBXMLMsgsRq onError="continueOnError">';
        if ($ID == 'CSMQ') {
            $iterator = !empty($extra['iterator']) ? $extra['iterator'] : 'Start';
            $iteratorID = !empty($extra['iteratorID']) ? 'iteratorID="' . $extra['iteratorID'] . '"' : '';
            $qbxml .= '<CustomerQueryRq requestID="' . $requestID . '" iterator="' . $iterator . '" ' . $iteratorID . '>';
            $qbxml .= '<MaxReturned>20</MaxReturned>';
            $conf = new Configuration($user);
            $conf->getUserId();
            $conf->loadConfiguration($conf->userId);
            $tz = empty($conf->qbConfig['qb_timezone']) ? 'UTC' : $conf->qbConfig['qb_timezone'];
            date_default_timezone_set($tz);
            $timedate = new TimeDate();
            $fromDate = getSyncDate("qb_last_sync_date", $conf->userId);
            $converted_date = $timedate->handle_offset($fromDate, "Y-m-d H:i:sP", true, null, $conf->qbConfig['qb_timezone']);
            $qbxml .= '<FromModifiedDate >' . str_replace(' ', 'T', $converted_date) . '</FromModifiedDate>'; //2014-12-19T00:00:00
        } else {
            $qbxml .= '<CustomerQueryRq requestID="' . $requestID . '">';
            if (count($extra) > 0) {
                foreach ($extra as $record) {
                    if (!empty($record['qb_id']) && $ID == 'CSMUQ') {
                        $qbxml .= '<ListID >' . $record['qb_id'] . '</ListID>';
                    } else if ($ID == 'CSMAQ') {
                        $qbxml .= '<FullName >' . $record['name'] . '</FullName>';
                    }
                }
            }
        }
        $qbxml .= "			</CustomerQueryRq>
						</QBXMLMsgsRq>
					</QBXML>";
        $GLOBALS['log']->fatal("User: $user - Action: $ID XML: " . removeLines($qbxml));
        return $qbxml;
    }

    /**
     * Receive a response from QuickBooks 
     * 
     * @param string $requestID					The requestID you passed to QuickBooks previously
     * @param string $action					The action that was performed (CustomerAdd in this case)
     * @param mixed $ID							The unique identifier of the record
     * @param array $extra			
     * @param string $err						An error message, assign a valid to $err if you want to report an error
     * @param integer $last_action_time			A unix timestamp (seconds) indicating when the last action of this type was dequeued (i.e.: for CustomerAdd, the last time a customer was added, for CustomerQuery, the last time a CustomerQuery ran, etc.)
     * @param integer $last_actionident_time	A unix timestamp (seconds) indicating when the combination of this action and ident was dequeued (i.e.: when the last time a CustomerQuery with ident of get-new-customers was dequeued)
     * @param string $xml						The complete qbXML response
     * @param array $idents						An array of identifiers that are contained in the qbXML response
     * @return void
     */
    static public function _quickbooks_customer_query_response($requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $xml, $idents) {
        $GLOBALS['log']->fatal("User: $user - ident: $ID - action: $action (Inside Customer Query Response)");
        $timedate = new TimeDate();
        $aclogic = new ACLogic($user);
        $actionArray = array(
            'CSMQ' => 'Querying Customers',
            'CSMAQ' => 'Checking Customer Duplication',
            'CSMUQ' => 'Getting Customer EditSequence for Update',
        );
        $data = array();
        $xmlArray = xmlstr_to_array($xml);
        $dataArray = $xmlArray['QBXMLMsgsRs']['CustomerQueryRs'];
        $properties = $dataArray['@attributes'];
        if (isset($dataArray['CustomerRet'][0])) {
            $data = $dataArray['CustomerRet'];
        } else {
            $data[] = $dataArray['CustomerRet'];
        }
        if ($ID == "CSMAQ" && count($data) > 0 && count($extra) > 0) {
            logMe("Customer Query Response: {$actionArray[$ID]}", "Response of Customer Query\n" . count($data) . " Duplicate records found in QB", $aclogic->config->userId);
            $GLOBALS['log']->fatal('Duplicate records found in QB Count: ' . count($data));
            foreach ($data as $record) {
                $sugarID = '';
                try {
                    foreach ($extra as $rec) {
                        if (htmlspecialchars_decode($rec['name'], ENT_QUOTES) == $record['Name'] || htmlspecialchars_decode($rec['name'], ENT_QUOTES) == $record['FullName']) {
                            $GLOBALS['log']->fatal('Records Mapped Name: ' . htmlspecialchars_decode($rec['name'], ENT_QUOTES));
                            $sugarID = $rec['id'];
                            break;
                        }
                    }
                    if (!empty($sugarID)) {
                        updateQBId("Accounts", $sugarID, $record['ListID']);
                    }
                } catch (Exception $e) {
                    logMe("Exception while mapping Account records", "Error Message: {$e->getMessage()}", $aclogic->config->userId);
                }
            }
        }
        if ($ID == "CSMUQ" && count($data) > 0) {
            $ext = array();
            foreach ($data as $record) {
                foreach ($extra as $key => $rec) {
                    if ($rec['qb_id'] == $record['ListID']) {
                        $ext['UPDATE'][$key] = $rec;
                        $ext['UPDATE'][$key]['EditSequence'] = $record['EditSequence'];
                        unset($extra[$key]);
                        break;
                    } else {
                        $ext['ADD'][$key] = $rec;
                    }
                }
            }
            if (isset($ext['UPDATE'])) {
                $aclogic->getUpdateReq($ext['UPDATE']);
            } else if (isset($ext['ADD'])) {
                $aclogic->getAddReq($ext['ADD']);
            }
        }

        if ($ID == "CSMQ" && count($data) > 0) {
            createAccount($data, $aclogic->config);
            if (isset($properties['iteratorRemainingCount']) && $properties['iteratorRemainingCount'] > 0) {
                $iterator = array(
                    'iteratorID' => $properties['iteratorID'],
                    'iterator' => 'Continue',
                );
                $aclogic->queue->enqueue(QUICKBOOKS_QUERY_CUSTOMER, 'CSMQ', $aclogic->Qpriority, $iterator, $aclogic->user);
            } else {
                if ($aclogic->qbConfig['product_item'] != '1') {
                    updateField("qbconfig", "qb_last_sync_date", getSyncDate("qb_sync_start", $aclogic->config->userId), $aclogic->config->userId);
                }
                updateField("qbconfig", "sugar_last_sync_date", $timedate->nowDb(), $aclogic->config->userId);
            }
        }
    }

}
