<?php

/**
 * @class PILogic
 */
class PILogic {

	public $queue;
	public $user;
	public $userId;
	public $qbFileId;
	public $qbConfig;
	public $ITQpriority;
	public $Upriority;
	public $Apriority;
	public $plist;

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
		if ($this->qbConfig['master_system'] == 'QuickBooks') {
			$this->ITQpriority = 19;
		} else {
			$this->ITQpriority = 4;
		}
		$this->ITApriority = 12;
		$this->ITUpriority = 11;
		$this->pList = $this->getPList();
	}

	/**
	 * @function getAddReq
	 * @param type $ids
	 */
	public function getAddReq($ids = array()) {
		$GLOBALS['log']->fatal("User: $this->user (Inside Products Add Req)");
		if (count($ids) <= 0) {
			$productData = getProdIDs("ProductTemplates", array('item_category' => true, 'whereRaw' => "IFNULL(product_template_qbfiles.qb_id,'') = '' AND IFNULL( product_template_qbfiles.qbfile_id,'')!='' AND  product_template_qbfiles.deleted=0 AND  product_template_qbfiles.qbfile_id='{$this->qbFileId}'"));
		} else {
			$productData = $ids;
		}
		if (count($productData) > 0) {
			$extra = array();
			foreach ($productData as $product) {
				$extra[] = array(
					'id' => $product['id'],
					'name' => getName($product['name'],31),
					'category' => $product['category'],
				);
			}
			if (count($ids) <= 0) {
				$this->queue->enqueue(QUICKBOOKS_QUERY_ITEM, 'ITMAQ', $this->ITApriority, $extra, $this->user);
			} else {
				$productExt = array();
				foreach ($extra as $ext) {
					$productExt[$ext['category']][] = $ext;
				}
				foreach ($productExt as $category => $pext) {
					$this->queue->enqueue($this->pList[$category]['actionAdd'], 'ITMA', $this->ITApriority, $pext, $this->user);
				}
			}
		}
	}

	/**
	 * @function getUpdateReq
	 * @param type $ids
	 */
	public function getUpdateReq($ids = array()) {
		$GLOBALS['log']->fatal("User: $this->user (Inside Products Update Req)");
		if (count($ids) <= 0) {
			$timedate = new TimeDate();
			$start_date = $timedate->nowDb();//gmdate('Y-m-d H:i:s');
			$end_date = getSyncDate("sugar_last_sync_date", $this->userId);
			updateField("qbconfig", "sugar_last_sync_date", $start_date, $this->userId);
			$where = "product_templates.date_modified between '$end_date' AND '$start_date'AND IFNULL(product_template_qbfiles.qb_id,'')!='' AND IFNULL(product_template_qbfiles.qbfile_id,'')!='' AND  product_template_qbfiles.deleted=0 AND product_template_qbfiles.qbfile_id='{$this->qbFileId}'";
            $productData = getProdIDs("ProductTemplates", array('item_category' => true, 'whereRaw' => $where));
		} else {
			$productData = $ids;
		}
		if (count($productData) > 0) {
			$extra = array();
			foreach ($productData as $product) {
				$extra[] = array(
					'id' => $product['id'],
					'name' => getName($product['name'],31),
					'category' => $product['category'],
					'qb_id' => $product['qb_id'],
					'EditSequence' => isset($product['EditSequence']) ? $product['EditSequence'] : '',
				);
			}
			if (count($ids) <= 0) {
				$this->queue->enqueue(QUICKBOOKS_QUERY_ITEM, 'ITMUQ', $this->ITUpriority, $extra, $this->user);
			} else {
				$productExt = array();
				foreach ($extra as $ext) {
					$productExt[$ext['category']][] = $ext;
				}
				foreach ($productExt as $category => $pext) {
					$this->queue->enqueue($this->pList[$category]['actionMod'], 'ITMU', $this->ITUpriority, $pext, $this->user);
				}
			}
		}
	}

	/**
	 * @function getQueryReq
	 */
	public function getQueryReq() {
		$GLOBALS['log']->fatal("User: $this->user (Inside Products Query Req)");
		$this->queue->enqueue(QUICKBOOKS_QUERY_ITEM, 'ITMQ', $this->ITQpriority, null, $this->user);
	}

	/**
	 * @function getProductArray
	 * @description seperating different products in a single multi array
	 * @param type $dataArray
	 * @return type
	 */
	public function getProductArray($dataArray) {
		$data = array();
		if (isset($dataArray['ItemInventoryRet']) && isset($dataArray['ItemInventoryRet'][0])) {
			$data['Inventory'] = $dataArray['ItemInventoryRet'];
		} else if (isset($dataArray['ItemInventoryRet'])) {
			$data['Inventory'][] = $dataArray['ItemInventoryRet'];
		}
		if (isset($dataArray['ItemNonInventoryRet']) && isset($dataArray['ItemNonInventoryRet'][0])) {
			$data['Noninventory'] = $dataArray['ItemNonInventoryRet'];
		} else if (isset($dataArray['ItemNonInventoryRet'])) {
			$data['Noninventory'][] = $dataArray['ItemNonInventoryRet'];
		}
		if (isset($dataArray['ItemServiceRet']) && isset($dataArray['ItemServiceRet'][0])) {
			$data['Service'] = $dataArray['ItemServiceRet'];
		} else if (isset($dataArray['ItemServiceRet'])) {
			$data['Service'][] = $dataArray['ItemServiceRet'];
		}
		if (isset($dataArray['ItemOtherChargeRet']) && isset($dataArray['ItemOtherChargeRet'][0])) {
			$data['Other'] = $dataArray['ItemOtherChargeRet'];
		} else if (isset($dataArray['ItemOtherChargeRet'])) {
			$data['Other'][] = $dataArray['ItemOtherChargeRet'];
		}
		return $data;
	}

	/**
	 * @function getProductCount
	 * @description calculatng the count of products
	 * @param type $data
	 * @return type
	 */
	public function getProductCount($data) {
		$count = 0;
		if (count($data) > 0) {
			foreach ($data as $product) {
				$count += (int) count($product);
			}
		}
		return $count;
	}

	/**
	 * @function prepareProductXML
	 * @description Preparing XML for different products
	 * @param type $data
	 * @return string
	 */
	public function prepareProductXML($productData, $type) {
		$GLOBALS['log']->fatal("Preparing $type Items XML of " . count($productData) . " records");
		$pList = self::getPList();
		$xml = '';
		foreach ($productData as $product) {
                    $product_name=getName($product['name'],31);
			if(strlen($product_name) > 31) {
				$GLOBALS['log']->fatal("{$product_name} Product Skipped! name length is more than 31 Chars");
				continue;
			}
			if (empty($product['qb_id'])) {
				$xml .= '<' . $pList[$type]['tagAdd'] . 'Rq requestID="$requestID">';
				$xml .= '<' . $pList[$type]['tagAdd'] . '>';
			} else if (!empty($product['qb_id']) && isset($product['EditSequence']) && !empty($product['EditSequence'])) {
				$xml .= '<' . $pList[$type]['tagMod'] . 'Rq requestID="$requestID">';
				$xml .= '<' . $pList[$type]['tagMod'] . '>';
				$xml .= '<ListID >' . $product['qb_id'] . '</ListID>';
				$xml .= '<EditSequence >' . $product['EditSequence'] . '</EditSequence>';
			} else {
				continue;
			}
			$xml .= "<Name >{$product_name}</Name>";
			$xml .= "<IsActive >1</IsActive>";
			if($type == "Inventory" || $type == "Noninventory") {
				$xml .= "<ManufacturerPartNumber >{$product['mft_part_num']}</ManufacturerPartNumber>";
			}
			if($type != "Inventory") {
				if (empty($product['qb_id'])) {
					$xml .= "<SalesAndPurchase>";
				} else {
					$xml .= "<SalesAndPurchaseMod>";
				}
			}
			$xml .= "<SalesDesc >{$product['description']}</SalesDesc>";
			$xml .= "<SalesPrice >".  str_replace(",", "", format_number($product['discount_price'], 2))."</SalesPrice>";
			$xml .= "<IncomeAccountRef>
						<FullName >Sales - Support and Maintenance</FullName>
					</IncomeAccountRef>";
			$xml .= "<PurchaseCost >".  str_replace(",", "", format_number($product['cost_price'], 2))."</PurchaseCost>";
			if($type == "Inventory") {
				$xml .= "<COGSAccountRef>
							<FullName >Ask My Accountant</FullName>
						</COGSAccountRef>";
				$xml .= "";
				$xml .= "<AssetAccountRef>
							<FullName >Ask My Accountant</FullName>
						</AssetAccountRef>";
			} else {
				$xml .= "<ExpenseAccountRef>
							<FullName >Ask My Accountant</FullName>
						</ExpenseAccountRef>";
			}
			if($type != "Inventory") {
				if (empty($product['qb_id'])) {
					$xml .= "</SalesAndPurchase>";
				} else {
					$xml .= "</SalesAndPurchaseMod>";
				}
			}
			if (empty($product['qb_id'])) {
				$xml .= '</' . $pList[$type]['tagAdd'] . '>';
				$xml .= '</' . $pList[$type]['tagAdd'] . 'Rq>';
			} else if (!empty($product['qb_id']) && isset($product['EditSequence']) && !empty($product['EditSequence'])) {
				$xml .= '</' . $pList[$type]['tagMod'] . '>';
				$xml .= '</' . $pList[$type]['tagMod'] . 'Rq>';
			}
		}
		return $xml;
	}

	/**
	 * @function getPList
	 * @description PList Setter function
	 */
	function getPList() {
		return array(
			'Other' => array(
				'actionAdd' => QUICKBOOKS_ADD_OTHERCHARGEITEM, 'actionMod' => QUICKBOOKS_MOD_OTHERCHARGEITEM,
				'tagAdd' => 'ItemOtherChargeAdd', 'tagMod' => 'ItemOtherChargeMod',
			), 
			'Service' => array(
				'actionAdd' => QUICKBOOKS_ADD_SERVICEITEM, 'actionMod' => QUICKBOOKS_MOD_SERVICEITEM,
				'tagAdd' => 'ItemServiceAdd', 'tagMod' => 'ItemServiceMod',
			), 
			'Inventory' => array(
				'actionAdd' => QUICKBOOKS_ADD_INVENTORYITEM, 'actionMod' => QUICKBOOKS_MOD_INVENTORYITEM,
				'tagAdd' => 'ItemInventoryAdd', 'tagMod' => 'ItemInventoryMod',
			), 
			'Noninventory' => array(
				'actionAdd' => QUICKBOOKS_ADD_NONINVENTORYITEM, 'actionMod' => QUICKBOOKS_MOD_NONINVENTORYITEM,
				'tagAdd' => 'ItemNonInventoryAdd', 'tagMod' => 'ItemNonInventoryMod',
			),
		);
	}

}
