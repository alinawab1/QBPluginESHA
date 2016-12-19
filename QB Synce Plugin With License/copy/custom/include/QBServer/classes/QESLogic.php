<?php

/**
 * @class QELogic
 */
require_once 'utils.php';

class QESLogic {

    public $queue;
    public $user;
    public $userId;
    public $qbFileId;
    public $qbConfig;
    public $QApriority;
    public $quoteList;

    /**
     * 
     * @param type $config
     */
    function __construct($config) {
        $this->user = $config->user;
        $this->userId = !empty($config->userId) ? $config->userId : $config->config->userId;
        $this->qbFileId = !empty($config->qbFileId) ? $config->qbFileId : $config->config->qbFileId;
        $this->queue = $config->queue;
        $this->qbConfig = $config->qbConfig;
        // Setting the Priorities on the basis of Master System
        $this->QApriority = 3;
        $this->quoteList = $this->getQuoteList();
    }

    /**
     * @function getAddReq
     * @param type $ids
     */
    public function getAddReq() {
        $GLOBALS['log']->fatal("User: $this->user (Inside Quotes Add Req)");
        $quoteData = getIDs("Quotes", array(
            'billing_accounts' => true,
            'qbfile_id' => $this->qbFileId,
            'quote_status' => $this->qbConfig['qb_quote_stage'],
            //'whereRaw' => "IFNULL(quotes.qb_id,'') = '' AND IFNULL(quotes.qbfile_id,'')!='' AND quotes.qbfile_id='{$this->qbFileId}'"
            'whereRaw' => "IFNULL(quotes.qb_id,'') = '' AND IFNULL(quotes.qbfile_id,'')!='' AND quotes.qbfile_id='{$this->qbFileId}' AND quote_stage = '" . $this->qbConfig['qb_quote_stage'] . "' "
                ), 1);
        if (count($quoteData) > 0) {
            $extra = array();
            foreach ($quoteData as $quote) {
                //Getting related Opp name as Customer name  to quote. If Opp exist then link this quote to this opp job
                if ($this->qbConfig['opp_job_sync'] == '1') {
                    $opp_name = getQuoteOppName($quote['id']);
                    $opp_name = $opp_name[0]['name'];
                    if (!empty($opp_name)) {
                        $quote['CustomerName'] = $opp_name;
                    }
                }
                $extra = array(
                    'id' => $quote['id'],
                    'CustomerName' => $quote['CustomerName'],
                    'qb_id' => $quote['qb_id'],
                );
            }
            $this->queue->enqueue($this->quoteList[$this->qbConfig['quote_maps_to']], 'QTA', $this->QApriority, $extra, $this->user);
        }
    }

    /**
     * @function prepareEstimateXML
     * @description Preparing XML for different products
     * @param type $quoteData
     * @return string
     */
    public function prepareEstimateXML($quoteData, $user) {
        $GLOBALS['log']->fatal("Preparing Estimates XML of " . count($quoteData) . " records");
        $qb_config = getQbconfigByUser($user);
        $qb_config=$qb_config->qbConfig;
        $xml = '';
        foreach ($quoteData as $quote) {
            if (!empty($quote['qb_id'])) {
                continue;
            }
            if ($qb_config['opp_job_sync'] == '1') {
                $opp_qb_id = getQuoteOppQbId($quote['id']);
                $opp_qb_id = $opp_qb_id[0]['qb_id'];
                if (!empty($opp_qb_id)) {
                    $quote['CustomerID'] = $opp_qb_id;
                }
            }
            $xml .= '<EstimateAddRq requestID="$requestID"><EstimateAdd>';
            $xml .= "<CustomerRef><ListID>{$quote['CustomerID']}</ListID></CustomerRef>
					<TxnDate>{$quote['date_quote_expected_closed']}</TxnDate>
					<BillAddress>
						" . getStreetAddXML($quote['billing_address_street']) . "
						<City >{$quote['billing_address_city']}</City>
						<State >{$quote['billing_address_state']}</State>
						<PostalCode >{$quote['billing_address_postalcode']}</PostalCode>
						<Country >{$quote['billing_address_country']}</Country>
					</BillAddress>
					<ShipAddress>
						" . getStreetAddXML($quote['shipping_address_street']) . "
						<City >{$quote['shipping_address_city']}</City>
						<State >{$quote['shipping_address_state']}</State>
						<PostalCode >{$quote['shipping_address_postalcode']}</PostalCode>
						<Country >{$quote['shipping_address_country']}</Country>
					</ShipAddress>
					<IsActive>1</IsActive>
					<PONumber>{$quote['purchase_order_num']}</PONumber>
					<DueDate>{$quote['date_quote_expected_closed']}</DueDate>";
            if (isset($quote['TaxName']) && !empty($quote['TaxName'])) {
                $xml .= "<ItemSalesTaxRef><FullName >{$quote['TaxName']}</FullName></ItemSalesTaxRef>";
            }
            $products = getQuoteProducts($quote['id']);
            if (count($products) > 0) {
                foreach ($products as $product) {
                    $product_name=getName($product['name'],31);
                    $xml .= "<EstimateLineAdd>
								<ItemRef><FullName >{$product_name}</FullName></ItemRef>
								<Desc >{$product['description']}</Desc>
								<Quantity >{$product['quantity']}</Quantity>
								<Rate >" . str_replace(",", "", format_number($product['discount_usdollar'], 2)) . "</Rate>
								<SalesTaxCodeRef ><FullName >" . ($product['tax_class'] == "Taxable" ? "TAX" : "NON") . "</FullName></SalesTaxCodeRef>
								<MarkupRate >0.00</MarkupRate>
							</EstimateLineAdd>";
                }
                if ((double) $quote['shipping'] > 0.00 && shipping_product_exist() == true) {
                    $xml .= "<EstimateLineAdd>
								<ItemRef><FullName >Shipping</FullName></ItemRef>
								<Desc >Shipping cost</Desc>
								<Quantity >1</Quantity>
								<Rate >" . str_replace(",", "", format_number($quote['shipping'], 2)) . "</Rate>
								<SalesTaxCodeRef ><FullName >NON</FullName></SalesTaxCodeRef>
								<MarkupRate >0.00</MarkupRate>
							</EstimateLineAdd>";
                }
            }
            $xml .= '</EstimateAdd></EstimateAddRq>';
        }
        return $xml;
    }

    /**
     * @function prepareSalesOrderXML
     * @description Preparing XML for different products
     * @param type $quoteData
     * @return string
     */
    public function prepareSalesOrderXML($quoteData, $user) {
        $GLOBALS['log']->fatal("Preparing Sales Order XML of " . count($quoteData) . " records");
        $qb_config = getQbconfigByUser($user);
        $qb_config=$qb_config->qbConfig;
        $xml = '';
        foreach ($quoteData as $quote) {
            if (!empty($quote['qb_id'])) {
                continue;
            }
            if ($qb_config['opp_job_sync'] == '1') {
                $opp_qb_id = getQuoteOppQbId($quote['id']);
                $opp_qb_id = $opp_qb_id[0]['qb_id'];
                if (!empty($opp_qb_id)) {
                    $quote['CustomerID'] = $opp_qb_id;
                }
            }
            $xml .= '<SalesOrderAddRq requestID="$requestID"><SalesOrderAdd>';
            $xml .= "<CustomerRef><ListID>{$quote['CustomerID']}</ListID></CustomerRef>
					<TxnDate>{$quote['date_quote_expected_closed']}</TxnDate>
					<BillAddress>
						" . getStreetAddXML($quote['billing_address_street']) . "
						<City >{$quote['billing_address_city']}</City>
						<State >{$quote['billing_address_state']}</State>
						<PostalCode >{$quote['billing_address_postalcode']}</PostalCode>
						<Country >{$quote['billing_address_country']}</Country>
					</BillAddress>
					<ShipAddress>
						" . getStreetAddXML($quote['shipping_address_street']) . "
						<City >{$quote['shipping_address_city']}</City>
						<State >{$quote['shipping_address_state']}</State>
						<PostalCode >{$quote['shipping_address_postalcode']}</PostalCode>
						<Country >{$quote['shipping_address_country']}</Country>
					</ShipAddress>
					<PONumber>{$quote['purchase_order_num']}</PONumber>
					<DueDate>{$quote['date_quote_expected_closed']}</DueDate>";
            if (isset($quote['TaxName']) && !empty($quote['TaxName'])) {
                $xml .= "<ItemSalesTaxRef><FullName >{$quote['TaxName']}</FullName></ItemSalesTaxRef>";
            }
            $products = getQuoteProducts($quote['id']);
            if (count($products) > 0) {
                foreach ($products as $product) {
                    $product_name=getName($product['name'],31);
                    $xml .= "<SalesOrderLineAdd>
								<ItemRef><FullName >{$product_name}</FullName></ItemRef>
								<Desc >{$product['description']}</Desc>
								<Quantity >{$product['quantity']}</Quantity>
								<Rate >" . str_replace(",", "", format_number($product['discount_usdollar'], 2)) . "</Rate>
								<SalesTaxCodeRef ><FullName >" . ($product['tax_class'] == "Taxable" ? "TAX" : "NON") . "</FullName></SalesTaxCodeRef>
							</SalesOrderLineAdd>";
                }
                if ((double) $quote['shipping'] > 0.00 && shipping_product_exist() == true) {
                    $xml .= "<SalesOrderLineAdd>
								<ItemRef><FullName >Shipping</FullName></ItemRef>
								<Desc >Shipping cost</Desc>
								<Quantity >1</Quantity>
								<Rate >" . str_replace(",", "", format_number($quote['shipping'], 2)) . "</Rate>
								<SalesTaxCodeRef ><FullName >NON</FullName></SalesTaxCodeRef>
							</SalesOrderLineAdd>";
                }
            }
            $xml .= '</SalesOrderAdd></SalesOrderAddRq>';
        }
        return $xml;
    }

    /**
     * @function prepareInvoiceXML
     * @description Preparing XML for different products
     * @param type $quoteData
     * @return string
     */
    public function prepareInvoiceXML($quoteData, $user) {
        $GLOBALS['log']->fatal("Preparing Invoice XML of " . count($quoteData) . " records");
        $qb_config = getQbconfigByUser($user);
        $qb_config=$qb_config->qbConfig;
        $xml = '';
        foreach ($quoteData as $quote) {

            if (!empty($quote['qb_id'])) {
                continue;
            }
            if ($qb_config['opp_job_sync'] == '1') {
                $opp_qb_id = getQuoteOppQbId($quote['id']);
                $opp_qb_id = $opp_qb_id[0]['qb_id'];
                if (!empty($opp_qb_id)) {
                    $quote['CustomerID'] = $opp_qb_id;
                }
            }
            $xml .= '<InvoiceAddRq requestID="$requestID"><InvoiceAdd>';
            $xml .= "<CustomerRef><ListID>{$quote['CustomerID']}</ListID></CustomerRef>
					<TxnDate>{$quote['date_quote_expected_closed']}</TxnDate>
					<BillAddress>
						" . getStreetAddXML($quote['billing_address_street']) . "
						<City >{$quote['billing_address_city']}</City>
						<State >{$quote['billing_address_state']}</State>
						<PostalCode >{$quote['billing_address_postalcode']}</PostalCode>
						<Country >{$quote['billing_address_country']}</Country>
					</BillAddress>
					<ShipAddress>
						" . getStreetAddXML($quote['shipping_address_street']) . "
						<City >{$quote['shipping_address_city']}</City>
						<State >{$quote['shipping_address_state']}</State>
						<PostalCode >{$quote['shipping_address_postalcode']}</PostalCode>
						<Country >{$quote['shipping_address_country']}</Country>
					</ShipAddress>
					<PONumber>{$quote['purchase_order_num']}</PONumber>
					<DueDate>{$quote['date_quote_expected_closed']}</DueDate>";
            if (isset($quote['TaxName']) && !empty($quote['TaxName'])) {
                $xml .= "<ItemSalesTaxRef><FullName >{$quote['TaxName']}</FullName></ItemSalesTaxRef>";
            }
            $products = getQuoteProducts($quote['id']);
            if (count($products) > 0) {
                foreach ($products as $product) {
                    $product_name=getName($product['name'],31);
                    $xml .= "<InvoiceLineAdd>
								<ItemRef><FullName >{$product_name}</FullName></ItemRef>
								<Desc >{$product['description']}</Desc>
								<Quantity >{$product['quantity']}</Quantity>
								<Rate >" . str_replace(",", "", format_number($product['discount_usdollar'], 2)) . "</Rate>
								<SalesTaxCodeRef ><FullName >" . ($product['tax_class'] == "Taxable" ? "TAX" : "NON") . "</FullName></SalesTaxCodeRef>
							</InvoiceLineAdd>";
                }
                //Adding quote Shipping as a line item in QB.
                if ((double) $quote['shipping'] > 0.00 && shipping_product_exist() == true) {
                    $xml .= "<InvoiceLineAdd>
                                                                        <ItemRef><FullName >Shipping</FullName></ItemRef>
                                                                        <Desc >Shipping cost</Desc>
                                                                        <Quantity >1</Quantity>
                                                                        <Rate >" . str_replace(",", "", format_number($quote['shipping'], 2)) . "</Rate>
                                                                        <SalesTaxCodeRef ><FullName >NON</FullName></SalesTaxCodeRef>
                                                                </InvoiceLineAdd>";
                }
            }
            $xml .= '</InvoiceAdd></InvoiceAddRq>';
        }
        return $xml;
    }

    /**
     * @function getPList
     * @description PList Setter function
     */
    function getQuoteList() {
        return array(
            'Invoice' => QUICKBOOKS_ADD_INVOICE,
            'Estimate' => QUICKBOOKS_ADD_ESTIMATE,
            'SalesOrder' => QUICKBOOKS_ADD_SALESORDER,
        );
    }

}
