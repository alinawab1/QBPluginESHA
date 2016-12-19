<?php

/**
 * File/class loader for QuickBooks packages 
 * 
 * Copyright (c) 2010 Keith Palmer / ConsoliBYTE, LLC.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.opensource.org/licenses/eclipse-1.0.php
 * 
 * @package QuickBooks
 * @subpackage Loader
 */

//  
if (!defined('QUICKBOOKS_LOADER_REQUIREONCE'))
{
	define('QUICKBOOKS_LOADER_REQUIREONCE', true);
}

if (!defined('QUICKBOOKS_LOADER_AUTOLOADER'))
{
	define('QUICKBOOKS_LOADER_AUTOLOADER', true);
}

/**
 * 
 */
class QuickBooks_Loader
{
	/**
	 * 
	 */
	static public function load($file, $autoload = true)
	{
		//print('loading file [' . $file . ']' . "\n");
		
		if ($autoload and 
			QuickBooks_Loader::_autoload())
		{
			return true;
		}
		
		static $loaded = array();
		
		if (isset($loaded[$file]))
		{
			return true;
		}
		
		$loaded[$file] = true;
		
		if (QUICKBOOKS_LOADER_REQUIREONCE)
		{
//			if(SugarAutoLoader::fileExists(QUICKBOOKS_BASEDIR.$file)) {
				require_once QUICKBOOKS_BASEDIR . $file;
//			}
		}
		else
		{
//			if(SugarAutoLoader::fileExists(QUICKBOOKS_BASEDIR.$file)) {
				require QUICKBOOKS_BASEDIR . $file;
//			}
		}
		
		return true;
	}
	
	/**
	 * 
	 */
	static protected function _autoload()
	{
		if (!QUICKBOOKS_LOADER_AUTOLOADER)
		{
			return false;
		}
		
		static $done = false;
		static $auto = false;
		
		if (!$done)
		{
			$done = true;
			
			/*if (function_exists('spl_autoload_register'))
			{
				// Register the autoloader, and return TRUE
//				spl_autoload_register(array( 'QuickBooks_Loader', '__autoload' ));
				
				$auto = true;
				return true;
			}*/
		}
		
		return $auto;
	}
	
	/**
	 * 
	 */
	static public function __autoload($name)
	{
		if (substr($name, 0, 10) == 'QuickBooks')
		{
			$file = '/' . str_replace('_', DIRECTORY_SEPARATOR, $name) . '.php';
			QuickBooks_Loader::load($file, false);
		}
	}
	
	/** 
	 * Import (require_once) a bunch of PHP files from a particular PHP directory
	 * 
	 * @param string $dir
	 * @return boolean
	 */
	static public function import($dir, $autoload = true)
	{
		/* Due to Blacklisted function (opendir, readdir, glob) on On-Demand instance doing this Code */
		$masterArr = array();
		$masterArr['/QuickBooks/WebConnector/Server'][0] = 'SQL.php';
		$masterArr['/QuickBooks/XML/Backend'][0] = 'Builtin.php';
		$masterArr['/QuickBooks/XML/Backend'][1] = 'Simplexml.php';
		$masterArr['/QuickBooks/IPP/Service'][0] ='Account.php';
		$masterArr['/QuickBooks/IPP/Service'][1] = 'Bill.php';
		$masterArr['/QuickBooks/IPP/Service'][2] = 'BillPayment.php';
		$masterArr['/QuickBooks/IPP/Service'][3] = 'BillPaymentCreditCard.php';
		$masterArr['/QuickBooks/IPP/Service'][4] = 'ChangeDataCapture.php';
		$masterArr['/QuickBooks/IPP/Service'][5] = 'ChangeDataDeleted.php';
		$masterArr['/QuickBooks/IPP/Service'][6] = 'Check.php';
		$masterArr['/QuickBooks/IPP/Service'][7] = 'Class.php';
		$masterArr['/QuickBooks/IPP/Service'][8] = 'Company.php';
		$masterArr['/QuickBooks/IPP/Service'][9] = 'CompanyInfo.php';
		$masterArr['/QuickBooks/IPP/Service'][10] = 'CompanyMetaData.php';
		$masterArr['/QuickBooks/IPP/Service'][11] = 'CreditMemo.php';
		$masterArr['/QuickBooks/IPP/Service'][12] = 'Customer.php';
		$masterArr['/QuickBooks/IPP/Service'][13] = 'Discount.php';
		$masterArr['/QuickBooks/IPP/Service'][14] = 'Employee.php';
		$masterArr['/QuickBooks/IPP/Service'][15] = 'Estimate.php';
		$masterArr['/QuickBooks/IPP/Service'][16] = 'Factory.php';
		$masterArr['/QuickBooks/IPP/Service'][17] = 'Invoice.php';
		$masterArr['/QuickBooks/IPP/Service'][18] = 'Item.php';
		$masterArr['/QuickBooks/IPP/Service'][19] = 'ItemConsolidated.php';
		$masterArr['/QuickBooks/IPP/Service'][20] = 'ItemReceipt.php';
		$masterArr['/QuickBooks/IPP/Service'][21] = 'Job.php';
		$masterArr['/QuickBooks/IPP/Service'][22] = 'JournalEntry.php';
		$masterArr['/QuickBooks/IPP/Service'][23] = 'Payment.php';
		$masterArr['/QuickBooks/IPP/Service'][24] = 'PaymentMethod.php';
		$masterArr['/QuickBooks/IPP/Service'][25] = 'PayrollItem.php';
		$masterArr['/QuickBooks/IPP/Service'][26] = 'Preferences.php';
		$masterArr['/QuickBooks/IPP/Service'][27] = 'Purchase.php';
		$masterArr['/QuickBooks/IPP/Service'][28] = 'PurchaseOrder.php';
		$masterArr['/QuickBooks/IPP/Service'][29] = 'Report';
		$masterArr['/QuickBooks/IPP/Service'][30] = 'Report.php';
		$masterArr['/QuickBooks/IPP/Service'][31] = 'SalesOrder.php';
		$masterArr['/QuickBooks/IPP/Service'][32] = 'SalesReceipt.php';
		$masterArr['/QuickBooks/IPP/Service'][33] = 'SalesRep.php';
		$masterArr['/QuickBooks/IPP/Service'][34] = 'SalesTax.php';
		$masterArr['/QuickBooks/IPP/Service'][35] = 'SalesTaxCode.php';
		$masterArr['/QuickBooks/IPP/Service'][36] = 'SalesTerm.php';
		$masterArr['/QuickBooks/IPP/Service'][37] = 'ShipMethod.php';
		$masterArr['/QuickBooks/IPP/Service'][38] = 'SyncStatus.php';
		$masterArr['/QuickBooks/IPP/Service'][39] = 'TaxCode.php';
		$masterArr['/QuickBooks/IPP/Service'][40] = 'Term.php';
		$masterArr['/QuickBooks/IPP/Service'][41] = 'TimeActivity.php';
		$masterArr['/QuickBooks/IPP/Service'][42] = 'UOM.php';
		$masterArr['/QuickBooks/IPP/Service'][43] = 'Vendor.php';
		$masterArr['/QuickBooks/IPP/Service'][44] = 'VendorCredit.php';
		$masterArr['/QuickBooks/QBXML/Object'][0] = 'Account.php';
		$masterArr['/QuickBooks/QBXML/Object'][1] = 'Bill';
		$masterArr['/QuickBooks/QBXML/Object'][2] = 'Bill.php';
		$masterArr['/QuickBooks/QBXML/Object'][3] = 'BillPaymentCheck';
		$masterArr['/QuickBooks/QBXML/Object'][4] = 'BillPaymentCheck.php';
		$masterArr['/QuickBooks/QBXML/Object'][5] = 'Check';
		$masterArr['/QuickBooks/QBXML/Object'][6] = 'Check.php';
		$masterArr['/QuickBooks/QBXML/Object'][7] = 'Class.php';
		$masterArr['/QuickBooks/QBXML/Object'][8] = 'CreditCardRefund.php';
		$masterArr['/QuickBooks/QBXML/Object'][9] = 'CreditMemo';
		$masterArr['/QuickBooks/QBXML/Object'][10] = 'CreditMemo.php';
		$masterArr['/QuickBooks/QBXML/Object'][11] = 'Customer.php';
		$masterArr['/QuickBooks/QBXML/Object'][12] = 'CustomerMsg.php';
		$masterArr['/QuickBooks/QBXML/Object'][13] = 'CustomerType.php';
		$masterArr['/QuickBooks/QBXML/Object'][14] = 'DataExt.php';
		$masterArr['/QuickBooks/QBXML/Object'][15] = 'Deposit';
		$masterArr['/QuickBooks/QBXML/Object'][16] = 'Deposit.php';
		$masterArr['/QuickBooks/QBXML/Object'][17] = 'DiscountItem.php';
		$masterArr['/QuickBooks/QBXML/Object'][18] = 'Employee.php';
		$masterArr['/QuickBooks/QBXML/Object'][19] = 'Estimate';
		$masterArr['/QuickBooks/QBXML/Object'][20] = 'Estimate.php';
		$masterArr['/QuickBooks/QBXML/Object'][21] = 'FixedAssetItem.php';
		$masterArr['/QuickBooks/QBXML/Object'][22] = 'Generic.php';
		$masterArr['/QuickBooks/QBXML/Object'][23] = 'GroupItem.php';
		$masterArr['/QuickBooks/QBXML/Object'][24] = 'InventoryAdjustment';
		$masterArr['/QuickBooks/QBXML/Object'][25] = 'InventoryAdjustment.php';
		$masterArr['/QuickBooks/QBXML/Object'][26] = 'InventoryAssemblyItem.php';
		$masterArr['/QuickBooks/QBXML/Object'][27] = 'InventoryItem.php';
		$masterArr['/QuickBooks/QBXML/Object'][28] = 'Invoice';
		$masterArr['/QuickBooks/QBXML/Object'][29] = 'Invoice.php';
		$masterArr['/QuickBooks/QBXML/Object'][30] = 'Item.php';
		$masterArr['/QuickBooks/QBXML/Object'][31] = 'ItemReceipt';
		$masterArr['/QuickBooks/QBXML/Object'][32] = 'ItemReceipt.php';
		$masterArr['/QuickBooks/QBXML/Object'][33] = 'JournalEntry';
		$masterArr['/QuickBooks/QBXML/Object'][34] = 'JournalEntry.php';
		$masterArr['/QuickBooks/QBXML/Object'][35] = 'NonInventoryItem.php';
		$masterArr['/QuickBooks/QBXML/Object'][36] = 'OtherChargeItem.php';
		$masterArr['/QuickBooks/QBXML/Object'][37] = 'PaymentItem.php';
		$masterArr['/QuickBooks/QBXML/Object'][38] = 'PaymentMethod.php';
		$masterArr['/QuickBooks/QBXML/Object'][39] = 'ReceivePayment';
		$masterArr['/QuickBooks/QBXML/Object'][40] = 'ReceivePayment.php';
		$masterArr['/QuickBooks/QBXML/Object'][41] = 'SalesOrder';
		$masterArr['/QuickBooks/QBXML/Object'][42] = 'SalesOrder.php';
		$masterArr['/QuickBooks/QBXML/Object'][43] = 'SalesReceipt';
		$masterArr['/QuickBooks/QBXML/Object'][44] = 'SalesReceipt.php';
		$masterArr['/QuickBooks/QBXML/Object'][45] = 'SalesRep.php';
		$masterArr['/QuickBooks/QBXML/Object'][46] = 'SalesTaxCode.php';
		$masterArr['/QuickBooks/QBXML/Object'][47] = 'SalesTaxGroupItem';
		$masterArr['/QuickBooks/QBXML/Object'][48] = 'SalesTaxGroupItem.php';
		$masterArr['/QuickBooks/QBXML/Object'][49] = 'SalesTaxItem.php';
		$masterArr['/QuickBooks/QBXML/Object'][50] = 'ServiceItem.php';
		$masterArr['/QuickBooks/QBXML/Object'][51] = 'ShipMethod.php';
		$masterArr['/QuickBooks/QBXML/Object'][52] = 'StandardTerms.php';
		$masterArr['/QuickBooks/QBXML/Object'][53] = 'SubtotalItem.php';
		$masterArr['/QuickBooks/QBXML/Object'][54] = 'UnitOfMeasureSet';
		$masterArr['/QuickBooks/QBXML/Object'][55] = 'UnitOfMeasureSet.php';
		$masterArr['/QuickBooks/QBXML/Object'][56] = 'Vendor.php';
		$files = $masterArr[$dir];
//		$dh = opendir(QUICKBOOKS_BASEDIR . $dir);
		if ($files)
		{
//			while (false !== ($file = readdir($dh)))
			foreach ($files as $file)
			{
				$tmp = explode('.', $file);
				if (end($tmp) == 'php' and 
					!sugar_is_dir(QUICKBOOKS_BASEDIR . $dir . DIRECTORY_SEPARATOR . $file))
				{
					QuickBooks_Loader::load($dir . DIRECTORY_SEPARATOR . $file, $autoload);
					//require_once $dir . '/' . $file;
				}
			}
			
			return true; 
		}
		
		return false;
	}	
	}
