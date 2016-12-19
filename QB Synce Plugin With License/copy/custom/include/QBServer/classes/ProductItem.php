<?php

require_once 'utils.php';

/**
 * @file ProductItem.php
 */
class ProductItem {

    static public function _quickbooks_item_query_request($requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $version, $locale) {
        $GLOBALS['log']->fatal("User: $user - ident: $ID - action: $action (Inside Item Query Request)");
        $iterator = !empty($extra['iterator']) ? $extra['iterator'] : 'Start';
        $iteratorID = !empty($extra['iteratorID']) ? 'iteratorID="' . $extra['iteratorID'] . '"' : '';
        $qbxml = '<?xml version="1.0" encoding="utf-8"?><?qbxml version="12.0"?>
				<QBXML>
					<QBXMLMsgsRq onError="continueOnError">';
        if ($ID == "ITMQ") {
            $qbxml .= '<ItemQueryRq requestID="' . $requestID . '" iterator="' . $iterator . '" ' . $iteratorID . '>
							<MaxReturned>20</MaxReturned>';
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
            $qbxml .= '<ItemQueryRq requestID="' . $requestID . '">';
            if (count($extra) > 0) {
                foreach ($extra as $record) {
                    if (!empty($record['qb_id']) && $ID == 'ITMUQ') {
                        $qbxml .= '<ListID >' . $record['qb_id'] . '</ListID>';
                    } else if ($ID == 'ITMAQ') {
                        $qbxml .= '<FullName >' . $record['name'] . '</FullName>';
                    }
                }
            }
        }
        $qbxml .= '</ItemQueryRq>
					</QBXMLMsgsRq>
				</QBXML>';
        $GLOBALS['log']->fatal("User: $user - Action: $ID XML ItemRequest: " . removeLines($qbxml));
        return $qbxml;
    }

    static public function _quickbooks_item_query_response($requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $xml, $idents) {
        $GLOBALS['log']->fatal("User: $user - ident: $ID - action: $action (Inside Item Query Response)");
        $xmlArray = xmlstr_to_array($xml);
        $GLOBALS['log']->fatal("XML ItemQueryResponse: " . removeLines($xml));
        $productArray = $xmlArray['QBXMLMsgsRs']['ItemQueryRs'];
        $productsData = PILogic::getProductArray($productArray);
        $productCount = PILogic::getProductCount($productsData);
        $properties = $productArray['@attributes'];
        $GLOBALS['log']->fatal("Records Mapped Count: $productCount against Action: $action");
        if ($ID == "ITMQ" && $productCount > 0) {
            $config = new Configuration($user);
            $config->getUserId();
            $config->getQBFileId();
            //loading the configuration
            $config->loadConfiguration($config->userId);
            $pilogic = new PILogic($config);
            //createProduct($productsData, $pilogic);
            if (isset($properties['iteratorRemainingCount']) && $properties['iteratorRemainingCount'] > 0) {
                $iterator = array(
                    'iteratorID' => $properties['iteratorID'],
                    'iterator' => 'Continue',
                );
                $pilogic->queue->enqueue(QUICKBOOKS_QUERY_ITEM, 'ITMQ', $pilogic->ITQpriority, $iterator, $pilogic->user);
            } else {
                if ($config->qbConfig['sales_history_invoice'] != '1') {
                    updateField("qbconfig", "qb_last_sync_date", getSyncDate("qb_sync_start", $config->userId), $config->userId);
                }
                updateField("qbconfig", "sugar_last_sync_date", $GLOBALS['timedate']->nowDb(), $config->userId);
            }
        } else if ($ID == "ITMUQ" && count($productsData) > 0) {
            $config = new Configuration($user);
            $config->getUserId();
            $config->getQBFileId();
            //loading the configuration
            $config->loadConfiguration($config->userId);
            $pilogic = new PILogic($config);
            $ext = array();
            foreach ($productsData as $products) {
                foreach ($products as $record) {
                    foreach ($extra as $key => $rec) {
                        if ($rec['qb_id'] == $record['ListID']) {
                            $ext['UPDATE'][$key] = $rec;
                            $ext['UPDATE'][$key]['EditSequence'] = $record['EditSequence'];
                            unset($extra[$key]);
                            break;
                        }
                    }
                }
            }
            if (isset($ext['UPDATE'])) {
                $pilogic->getUpdateReq($ext['UPDATE']);
            }
        } else if ($ID == "ITMAQ") {
            $userId = getUserId($user);
            logMe("Product found in QB", "$productCount products found in QuickBooks mapping in Sugar", $userId);
            foreach ($productsData as $products) {
                foreach ($products as $record) {
                    $sugarID = '';
                    try {
                        foreach ($extra as $rec) {
                            if (html_entity_decode($rec['name']) == $record['Name']) {
                                $sugarID = $rec['id'];
                                break;
                            }
                        }
                        if (!empty($sugarID)) {
                            //updateQBId("ProductTemplates", $sugarID, $record['ListID']);
                            $qb_config = getQbconfigByUser($user);
                            updateProdQBId("ProductTemplates", $sugarID, $record['ListID'], $qb_config->qbFileId);
                        }
                    } catch (Exception $e) {
                        logMe("Exception while mapping Product records", "Error Message: {$e->getMessage()}", $userId);
                    }
                }
            }
        }
    }

    static public function _quickbooks_inventory_add_request($requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $version, $locale) {
        $GLOBALS['log']->fatal("User: $user - ident: $ID - action: $action (Inside Inventory Add Request)");
        $qbxml = '';
        if (count($extra) > 0) {
            $ids = array();
            foreach ($extra as $record) {
                $ids[] = $record['id'];
            }
            $productData = getProductData("ProductTemplates", array('where_ids' => $ids));
            if (count($productData) > 0) {
                $productXML = PILogic::prepareProductXML($productData, 'Inventory');
                $qbxml = '<?xml version="1.0" encoding="utf-8"?>
		<?qbxml version="12.0"?>
		<QBXML>
			<QBXMLMsgsRq onError="continueOnError">
				' . str_replace('$requestID', $requestID, $productXML) . '
			</QBXMLMsgsRq>
		</QBXML>';
                $GLOBALS['log']->fatal("User: $user - Action: $ID XML: " . removeLines($qbxml));
            } else {
                $GLOBALS['log']->fatal("Warning! No Product found for Request process");
            }
        }
        return $qbxml;
    }

    static public function _quickbooks_inventory_add_response($requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $xml, $idents) {
        $GLOBALS['log']->fatal("User: $user - ident: $ID - action: $action (Inside Inventory Add Response)");
        $userId = getUserId($user);
        $xmlArray = xmlstr_to_array($xml);
        $productArray = $xmlArray['QBXMLMsgsRs']['ItemInventoryAddRs'];
        if (isset($productArray[0])) {
            $productData = $productArray;
        } else {
            $productData[] = $productArray;
        }
        $GLOBALS['log']->fatal("Inventory Products Created in QB Count: " . count($productData));
        if (count($productData) > 0 && count($extra) > 0) {
            logMe("Inventory Items Created in QB", count($productData) . " Inventory items created in the QuickBooks", $userId);
            foreach ($productData as $product) {
                $sugarID = '';
                try {
                    foreach ($extra as $rec) {
                        if (htmlspecialchars_decode($rec['name'], ENT_QUOTES) == $product['ItemInventoryRet']['Name']) {
                            $GLOBALS['log']->fatal($rec['category'] . ' Product (' . htmlspecialchars_decode($rec['name'], ENT_QUOTES) . ') Added in QB');
                            $sugarID = $rec['id'];
                            break;
                        }
                    }
                    if (!empty($sugarID)) {
                        $qb_config = getQbconfigByUser($user);
                        updateProdQBId("ProductTemplates", $sugarID, $product['ItemInventoryRet']['ListID'], $qb_config->qbFileId);
                    }
                } catch (Exception $e) {
                    logMe("Exception while mapping Inventory records", "Error Message: {$e->getMessage()}", $userId);
                }
            }
        }
    }

    static public function _quickbooks_inventory_update_request($requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $version, $locale) {
        $GLOBALS['log']->fatal("User: $user - ident: $ID - action: $action (Inside Inventory Mod Request)");
        $ids = array();
        $editSeq = array();
        $qbxml = '';
        if (count($extra) > 0) {
            foreach ($extra as $record) {
                $ids[] = $record['id'];
                $editSeq[$record['id']] = $record['EditSequence'];
            }
            $productData = getProductData("ProductTemplates", array('where_ids' => $ids));
            if (count($productData) > 0) {
                foreach ($productData as $key => $product) {
                    $productData[$key]['EditSequence'] = $editSeq[$product['id']];
                }
            }
            $productXML = PILogic::prepareProductXMl($productData, "Inventory");
            $qbxml = '<?xml version="1.0" encoding="utf-8"?>
			<?qbxml version="12.0"?>
			<QBXML>
				<QBXMLMsgsRq onError="continueOnError">
						' . str_replace('$requestID', $requestID, $productXML) . '
				</QBXMLMsgsRq>
			</QBXML>';
            $GLOBALS['log']->fatal("User: $user - Action: $ID XML: " . removeLines($qbxml));
        } else {
            $GLOBALS['log']->fatal("Warning! No Product found for Request process");
        }
        return $qbxml;
    }

    static public function _quickbooks_inventory_update_response($requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $xml, $idents) {
        $GLOBALS['log']->fatal("User: $user - ident: $ID - action: $action (Inside Inventory Item Mod Response)");
        $xmlArray = xmlstr_to_array($xml);
        $productArray = $xmlArray['QBXMLMsgsRs'];
        if (isset($productArray['ItemInventoryModRs'][0])) {
            $productData = $productArray['ItemInventoryModRs'];
        } else {
            $productData[] = $productArray['ItemInventoryModRs'];
        }
        $GLOBALS['log']->fatal("Inventory Items Updated Successfully in QB Count: " . count($productData));
    }

    static public function _quickbooks_noninventory_add_request($requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $version, $locale) {
        $GLOBALS['log']->fatal("User: $user - ident: $ID - action: $action (Inside Non Inventory Add Request)");
        $qbxml = '';
        if (count($extra) > 0) {
            $ids = array();
            foreach ($extra as $record) {
                $ids[] = $record['id'];
            }
            $productData = getProductData("ProductTemplates", array('where_ids' => $ids));
            if (count($productData) > 0) {
                $productXML = PILogic::prepareProductXML($productData, 'Noninventory');
                $qbxml = '<?xml version="1.0" encoding="utf-8"?>
		<?qbxml version="12.0"?>
		<QBXML>
			<QBXMLMsgsRq onError="continueOnError">
				' . str_replace('$requestID', $requestID, $productXML) . '
			</QBXMLMsgsRq>
		</QBXML>';
                $GLOBALS['log']->fatal("User: $user - Action: $ID XML: " . removeLines($qbxml));
            } else {
                $GLOBALS['log']->fatal("Warning! No Product found for Request process");
            }
        }
        return $qbxml;
    }

    static public function _quickbooks_noninventory_add_response($requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $xml, $idents) {
        $GLOBALS['log']->fatal("User: $user - ident: $ID - action: $action (Inside Non Inventory Add Response)");
        $userId = getUserId($user);
        $xmlArray = xmlstr_to_array($xml);
        $productArray = $xmlArray['QBXMLMsgsRs']['ItemNoninventoryAddRs'];
        if (isset($productArray[0])) {
            $productData = $productArray;
        } else {
            $productData[] = $productArray;
        }
        $GLOBALS['log']->fatal("Non Inventory Products Created in QB Count: " . count($productData));
        if (count($productData) > 0 && count($extra) > 0) {
            logMe("Non-inventory Items Created in QB", count($productData) . " Non-inventory items created in the QuickBooks", $userId);
            foreach ($productData as $product) {
                $sugarID = '';
                try {
                    foreach ($extra as $rec) {
                        if (htmlspecialchars_decode($rec['name'], ENT_QUOTES) == $product['ItemNoninventoryRet']['Name']) {
                            $GLOBALS['log']->fatal($rec['category'] . ' Product (' . htmlspecialchars_decode($rec['name'], ENT_QUOTES) . ') Added in QB');
                            $sugarID = $rec['id'];
                            break;
                        }
                    }
                    if (!empty($sugarID)) {
                        $qb_config = getQbconfigByUser($user);
                        updateProdQBId("ProductTemplates", $sugarID, $product['ItemNoninventoryRet']['ListID'], $qb_config->qbFileId);
                    }
                } catch (Exception $e) {
                    logMe("Exception while mapping Non-inventory records", "Error Message: {$e->getMessage()}", $userId);
                }
            }
        }
    }

    static public function _quickbooks_noninventory_update_request($requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $version, $locale) {
        $GLOBALS['log']->fatal("User: $user - ident: $ID - action: $action (Inside Noninventory Item Update Request)");
        $qbxml = '';
        $ids = array();
        $editSeq = array();
        if (count($extra) > 0) {
            foreach ($extra as $record) {
                $ids[] = $record['id'];
                $editSeq[$record['id']] = $record['EditSequence'];
            }
            $productData = getProductData("ProductTemplates", array('where_ids' => $ids));
            if (count($productData) > 0) {
                foreach ($productData as $key => $product) {
                    $productData[$key]['EditSequence'] = $editSeq[$product['id']];
                }
            }
            $productXML = PILogic::prepareProductXMl($productData, "Noninventory");
            $qbxml = '<?xml version="1.0" encoding="utf-8"?>
			<?qbxml version="12.0"?>
			<QBXML>
				<QBXMLMsgsRq onError="continueOnError">
						' . str_replace('$requestID', $requestID, $productXML) . '
				</QBXMLMsgsRq>
			</QBXML>';
            $GLOBALS['log']->fatal("User: $user - Action: $ID XML: " . removeLines($qbxml));
        } else {
            $GLOBALS['log']->fatal("Warning! No Product found for Request process");
        }
        return $qbxml;
    }

    static public function _quickbooks_noninventory_update_response($requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $xml, $idents) {
        $GLOBALS['log']->fatal("User: $user - ident: $ID - action: $action (Inside Noninventory Item Mod Response)");
        $xmlArray = xmlstr_to_array($xml);
        $productArray = $xmlArray['QBXMLMsgsRs'];
        if (isset($productArray['ItemNoninventoryModRs'][0])) {
            $productData = $productArray['ItemNoninventoryModRs'];
        } else {
            $productData[] = $productArray['ItemNoninventoryModRs'];
        }
        $GLOBALS['log']->fatal("Noninventory Items Updated Successfully in QB Count: " . count($productData));
    }

    static public function _quickbooks_service_add_request($requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $version, $locale) {
        $GLOBALS['log']->fatal("User: $user - ident: $ID - action: $action (Inside Service Add Request)");
        $qbxml = '';
        if (count($extra) > 0) {
            $ids = array();
            foreach ($extra as $record) {
                $ids[] = $record['id'];
            }
            $productData = getProductData("ProductTemplates", array('where_ids' => $ids));
            if (count($productData) > 0) {
                $productXML = PILogic::prepareProductXML($productData, 'Service');
                $qbxml = '<?xml version="1.0" encoding="utf-8"?>
		<?qbxml version="12.0"?>
		<QBXML>
			<QBXMLMsgsRq onError="continueOnError">
				' . str_replace('$requestID', $requestID, $productXML) . '
			</QBXMLMsgsRq>
		</QBXML>';
                $GLOBALS['log']->fatal("User: $user - Action: $ID XML: " . removeLines($qbxml));
            } else {
                $GLOBALS['log']->fatal("Warning! No Product found for Request process");
            }
        }
        return $qbxml;
    }

    static public function _quickbooks_service_add_response($requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $xml, $idents) {
        $GLOBALS['log']->fatal("User: $user - ident: $ID - action: $action (Inside Service Add Response)");
        $userId = getUserId($user);
        $xmlArray = xmlstr_to_array($xml);
        $productArray = $xmlArray['QBXMLMsgsRs']['ItemServiceAddRs'];
        if (isset($productArray[0])) {
            $productData = $productArray;
        } else {
            $productData[] = $productArray;
        }
        $GLOBALS['log']->fatal("Service Products Created in QB Count: " . count($productData));
        if (count($productData) > 0 && count($extra) > 0) {
            logMe("Service Items Created in QB", count($productData) . " Service items created in the QuickBooks", $userId);
            foreach ($productData as $product) {
                $sugarID = '';
                try {
                    foreach ($extra as $rec) {
                        if (htmlspecialchars_decode($rec['name'], ENT_QUOTES) == $product['ItemServiceRet']['Name']) {
                            $GLOBALS['log']->fatal($rec['category'] . ' Product (' . htmlspecialchars_decode($rec['name'], ENT_QUOTES) . ') Added in QB');
                            $sugarID = $rec['id'];
                            break;
                        }
                    }
                    if (!empty($sugarID)) {
                        $qb_config = getQbconfigByUser($user);
                        updateProdQBId("ProductTemplates", $sugarID, $product['ItemServiceRet']['ListID'], $qb_config->qbFileId);
                    }
                } catch (Exception $e) {
                    logMe("Exception while mapping Service records", "Error Message: {$e->getMessage()}", $userId);
                }
            }
        }
    }

    static public function _quickbooks_service_update_request($requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $version, $locale) {
        $GLOBALS['log']->fatal("User: $user - ident: $ID - action: $action (Inside Service Item Update Request)");
        $qbxml = '';
        $ids = array();
        $editSeq = array();
        if (count($extra) > 0) {
            foreach ($extra as $record) {
                $ids[] = $record['id'];
                $editSeq[$record['id']] = $record['EditSequence'];
            }
            $productData = getProductData("ProductTemplates", array('where_ids' => $ids));
            if (count($productData) > 0) {
                foreach ($productData as $key => $product) {
                    $productData[$key]['EditSequence'] = $editSeq[$product['id']];
                }
            }
            $productXML = PILogic::prepareProductXMl($productData, "Service");
            $qbxml = '<?xml version="1.0" encoding="utf-8"?>
			<?qbxml version="12.0"?>
			<QBXML>
				<QBXMLMsgsRq onError="continueOnError">
						' . str_replace('$requestID', $requestID, $productXML) . '
				</QBXMLMsgsRq>
			</QBXML>';
            $GLOBALS['log']->fatal("User: $user - Action: $ID XML: " . removeLines($qbxml));
        } else {
            $GLOBALS['log']->fatal("Warning! No Product found for Request process");
        }
        return $qbxml;
    }

    static public function _quickbooks_service_update_response($requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $xml, $idents) {
        $GLOBALS['log']->fatal("User: $user - ident: $ID - action: $action (Inside Service Item Mod Response)");
        $xmlArray = xmlstr_to_array($xml);
        $productArray = $xmlArray['QBXMLMsgsRs'];
        if (isset($productArray['ItemServiceModRs'][0])) {
            $productData = $productArray['ItemServiceModRs'];
        } else {
            $productData[] = $productArray['ItemServiceModRs'];
        }
        $GLOBALS['log']->fatal("Service Items Updated Successfully in QB Count: " . count($productData));
    }

    static public function _quickbooks_other_add_request($requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $version, $locale) {
        $GLOBALS['log']->fatal("User: $user - ident: $ID - action: $action (Inside Other Charge Add Request)");
        $qbxml = '';
        if (count($extra) > 0) {
            $ids = array();
            foreach ($extra as $record) {
                $ids[] = $record['id'];
            }
            $productData = getProductData("ProductTemplates", array('where_ids' => $ids));
            if (count($productData) > 0) {
                $productXML = PILogic::prepareProductXML($productData, 'Other');
                $qbxml = '<?xml version="1.0" encoding="utf-8"?>
		<?qbxml version="12.0"?>
		<QBXML>
			<QBXMLMsgsRq onError="continueOnError">
				' . str_replace('$requestID', $requestID, $productXML) . '
			</QBXMLMsgsRq>
		</QBXML>';
                $GLOBALS['log']->fatal("User: $user - Action: $ID XML: " . removeLines($qbxml));
            } else {
                $GLOBALS['log']->fatal("Warning! No Product found for Request process");
            }
        }
        return $qbxml;
    }

    static public function _quickbooks_other_add_response($requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $xml, $idents) {
        $GLOBALS['log']->fatal("User: $user - ident: $ID - action: $action (Inside Other Charge Add Response)");
        $userId = getUserId($user);
        $xmlArray = xmlstr_to_array($xml);
        $productArray = $xmlArray['QBXMLMsgsRs']['ItemOtherChargeAddRs'];
        if (isset($productArray[0])) {
            $productData = $productArray;
        } else {
            $productData[] = $productArray;
        }
        $GLOBALS['log']->fatal("OtherCharge Products Created in QB Count: " . count($productData));
        if (count($productData) > 0 && count($extra) > 0) {
            logMe("Other Charge Items Created in QB", count($productData) . " Other Charge items created in the QuickBooks", $userId);
            foreach ($productData as $product) {
                $sugarID = '';
                try {
                    foreach ($extra as $rec) {
                        if (htmlspecialchars_decode($rec['name'], ENT_QUOTES) == $product['ItemOtherChargeRet']['Name']) {
                            $GLOBALS['log']->fatal($rec['category'] . ' Product (' . htmlspecialchars_decode($rec['name'], ENT_QUOTES) . ') Added in QB');
                            $sugarID = $rec['id'];
                            break;
                        }
                    }
                    if (!empty($sugarID)) {
                        $qb_config = getQbconfigByUser($user);
                        updateProdQBId("ProductTemplates", $sugarID, $product['ItemOtherChargeRet']['ListID'], $qb_config->qbFileId);
                    }
                } catch (Exception $e) {
                    logMe("Exception while mapping Other Charge records", "Error Message: {$e->getMessage()}", $userId);
                }
            }
        }
    }

    static public function _quickbooks_other_update_request($requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $version, $locale) {
        $GLOBALS['log']->fatal("User: $user - ident: $ID - action: $action (Inside Other Charge Item Update Request)");
        $qbxml = '';
        $ids = array();
        $editSeq = array();
        if (count($extra) > 0) {
            foreach ($extra as $record) {
                $ids[] = $record['id'];
                $editSeq[$record['id']] = $record['EditSequence'];
            }
            $productData = getProductData("ProductTemplates", array('where_ids' => $ids));
            if (count($productData) > 0) {
                foreach ($productData as $key => $product) {
                    $productData[$key]['EditSequence'] = $editSeq[$product['id']];
                }
            }
            $productXML = PILogic::prepareProductXMl($productData, "Other");
            $qbxml = '<?xml version="1.0" encoding="utf-8"?>
			<?qbxml version="12.0"?>
			<QBXML>
				<QBXMLMsgsRq onError="continueOnError">
						' . str_replace('$requestID', $requestID, $productXML) . '
				</QBXMLMsgsRq>
			</QBXML>';
            $GLOBALS['log']->fatal("User: $user - Action: $ID XML: " . removeLines($qbxml));
        } else {
            $GLOBALS['log']->fatal("Warning! No Product found for Request process");
        }
        return $qbxml;
    }

    static public function _quickbooks_other_update_response($requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $xml, $idents) {
        $GLOBALS['log']->fatal("User: $user - ident: $ID - action: $action (Inside Other Charge Item Mod Response)");
        $xmlArray = xmlstr_to_array($xml);
        $productArray = $xmlArray['QBXMLMsgsRs'];
        if (isset($productArray['ItemOtherChargeModRs'][0])) {
            $productData = $productArray['ItemOtherChargeModRs'];
        } else {
            $productData[] = $productArray['ItemOtherChargeModRs'];
        }
        $GLOBALS['log']->fatal("Other Charge Items Updated Successfully in QB Count: " . count($productData));
    }

}
