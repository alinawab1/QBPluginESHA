<?php

/**
 * @class PILogic
 */
class ISHLogic {

	public $queue;
	public $user;
	public $qbConfig;
	public $INVQpriority;

	/**
	 * 
	 * @param type $config
	 */
	function __construct($config) {
		$this->user = $config->user;
		$this->config = $config;
		$this->queue = $config->queue;
		$this->qbConfig = $config->qbConfig;
		$this->INVQpriority = 2;
	}

	/**
	 * @function getQueryReq
	 */
	public function getQueryReq() {
		$GLOBALS['log']->fatal("User: $this->user (Inside Products Query Req)");
		$this->queue->enqueue(QUICKBOOKS_QUERY_INVOICE, 'INVQ', $this->INVQpriority, null, $this->user);
	}

	public function prepareProductsArray($productArray) {
		$products = array();
		$productGrpData = array();
		$productData = array();
		if (isset($productArray['InvoiceLineGroupRet'])) {
			if (isset($productArray['InvoiceLineGroupRet'][0])) {
				$productGrpData = $productArray['InvoiceLineGroupRet'];
			} else {
				$productGrpData[] = $productArray['InvoiceLineGroupRet'];
			}
			foreach ($productGrpData as $products) {
				if (isset($products['InvoiceLineRet'][0])) {
					$productData = $products['InvoiceLineRet'];
				} else {
					$productData[] = $products['InvoiceLineRet'];
				}
				if (isset($products['InvoiceLineRet']) && count($products['InvoiceLineRet']) > 0) {
					foreach ($productData as $product) {
						$products[] = array(
							'Name' => $product['ItemRef']['FullName'],
							'Description' => wordwrap($product['Desc'], "55", "\n"),
							'Quantity' => !empty($product['Quantity']) ? $product['Quantity'] : 0,
							'Rate' => !empty($product['Rate']) ? $product['Rate'] : 0.00,
							'Amount' => !empty($product['Amount']) ? $product['Amount'] : 0.00,
						);
					}
				}
			}
		}
		if (isset($productArray['InvoiceLineRet'])) {
			$productData = array();
			if (isset($productArray['InvoiceLineRet'][0])) {
				$productData = $productArray['InvoiceLineRet'];
			} else {
				$productData[] = $productArray['InvoiceLineRet'];
			}
			foreach ($productData as $product) {
				$products[] = array(
					'Name' => $product['ItemRef']['FullName'],
					'Description' => wordwrap($product['Desc'], "55", "\n"),
					'Quantity' => !empty($product['Quantity']) ? $product['Quantity'] : 0,
					'Rate' => !empty($product['Rate']) ? $product['Rate'] : 0.00,
					'Amount' => !empty($product['Amount']) ? $product['Amount'] : 0.00,
				);
			}
		}
		return $products;
	}

}
