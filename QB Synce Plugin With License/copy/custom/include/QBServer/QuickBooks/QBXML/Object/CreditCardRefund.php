<?php

/**
 * QuickBooks CreditCardRefund object
 * 
 * @author Jayson Lindsley <jay.lindsley@gmail.com>
 * @author Keith Palmer <keith@consolibyte.com>
 *
 * @license LICENSE.txt
 * 
 * @package QuickBooks
 * @subpackage Object
 */

/**
 * Base object class
 */
QuickBooks_Loader::load('/QuickBooks/QBXML/Object.php');


/**
* QuickBooks object class
 */
 class Quickbooks_QBXML_Object_CreditCardRefund extends QuickBooks_QBXML_Object
 {
 	/**
	 * Create a new QuickBooks_Object_Customer object
	 * 
	 * @param array $arr
	 */

	public function __construct($arr = array())
	{
		parent::__construct($arr);
	}

	public function setCustomerListID($ListID)
	{
		return $this->set('CustomerRef ListID', $ListID);
	}

	public function getCustomerListID()
	{
		return $this->getD('CustomerRef ListID');
	}

	public function setCustomerFullName($name)
	{
		return $this->set('CustomerRef FullName', $name);
	}

	public function getCustomerFullName()
	{
		return $this->getD('CustomerRef FullName');
	}

	public function setAccountRefFullName($account)
	{
		return $this->set('RefundFromAccountRef FullName', $account);
	}

	public function getAccountRefFullName()
	{
		return $this->getD('RefundFromAccountRef FullName');
	}

	public function setARAccountListID($ListID)
	{
		return $this->set('ARAccountRef ListID', $ListID);
	}
	
	public function setARAccountName($name)
	{
		return $this->set('ARAccountRef FullName', $name);
	}
	
	public function getARAccountListID()
	{
		return $this->getD('ARAccountRef ListID');
	}
	
	public function getARAccountName()
	{
		return $this->getD('ARAccountRef FullName');
	}

	public function setPaymentMethodName($name)
	{
		return $this->set('PaymentMethodRef FullName', $name);
	}
	
	public function getPaymentMethodName()
	{
		return $this->getD('PaymentMethodRef FullName');
	}
	
	public function setPaymentMethodListID($ListID)
	{
		return $this->set('PaymentMethodRef ListID', $ListID);
	}
	
	public function getPaymentMethodListID()
	{
		return $this->getD('PaymentMethodRef ListID');
	}

	public function setExchangeRate($rate)
	{
		return $this->set('ExchangeRate', (float)$rate);
	}

	public function getExchangeRate()
	{
		return $this->getD('ExchangeRate');
	}

	public function setExternalGUID($guid)
	{
		return $this->set('ExternalGUID', $guid);
	}

	public function getExternalGUID()
	{
		return $this->getD('ExternalGUID');
	}

	public function setMemo($memo)
	{
		return $this->set('Memo', $memo);
	}
	
	public function getMemo()
	{
		return $this->getD('Memo');
	}
	
	public function setRefundAppliedToTxnID($ID) 
	{
		return $this->set('RefundAppliedToTxnAdd TxnID', $ID);
	}

	public function getRefundAppliedToTxnID()
	{
		return $this->getD('RefundAppliedToTxnAdd TxnID');
	}

	public function setRefundAmount($amount)
	{
		return $this->set('RefundAppliedToTxnAdd RefundAmount', $amount);
	}

	public function setRefNumber($ref)
	{
		return $this->set('RefNumber', $ref);
	}

	public function setTxnID($TxnID)
	{
		return $this->set('TxnID', $TxnID);
	}

	public function getTransactionID()
	{
		return $this->getD('TxnID');
	}

	/* The properties below are used when querying only */
	/* They are processed by QB as or statements */

	public function setFromModifiedDate($date)
	{
		return $this->set('ModifiedDateRangeFilter FromModifiedDate', $date);
	}

	public function getFromModifiedDate()
	{
		return $this->getD('ModifiedDateRangeFilter FromModifiedDate');
	}

	public function setToModifiedDate($date)
	{
		return $this->set('ModifiedDateRangeFilter ToModifiedDate', $date);
	}

	public function getToModifiedDate()
	{
		return $this->getD('ModifiedDateRangeFilter ToModifiedDate');
	}

	public function setFromTxnDate($date)
	{
		return $this->set('TxnDateRangeFilter FromTxnDate', $date);
	}

	public function getFromTxnDate()
	{
		return $this->getD('TxnDateRangeFilter FromTxnDate');
	}

	public function setToTxnDate($date)
	{
		return $this->set('TxnDateRangeFilter ToTxnDate', $date);
	}

	public function getToTxnDate()
	{
		return $this->getD('TxnDateRangeFilter ToTxnDate');
	}

	public function setDateMacro($date){
		return $this->set('TxnDateRangeFilter DateMacro', $date);
	}

	public function getDateMacro()
	{
		return $this->getD('TxnDateRangeFilter DateMacro');
	}

	public function setEntityFilterListID($ID)
	{
		return $this->set('EntityFilter ListID', $ID);
	}

	public function getEntityFilterListID($ID)
	{
		return $this->set('EntityFilter ListID');
	}

	public function setAccountFilterListID($ID)
	{
		return $this->set('AccountFilter ListID', $ID);
	}

	public function getAccountFilterListID()
	{
		return $this->getD('AccountFilter ListID');
	}

	public function setAccountFilterFullName($fullname)
	{
		return $this->set('AccountFilter FullName', $fullname);
	}

	public function getAccountFilterFullName()
	{
		return $this->getD('AccountFilter FullName');
	}

	/**
	 * Set the credit card information for this refund
	 * 
	 * @param string $cardno		The credit card number
	 * @param integer $expmonth		The expiration month (1 is January, 2 is February, etc.)
	 * @param integer $expyear		The expiration year 
	 * @param string $name			The name on the credit card
	 * @param string $address		The billing address for the credit card
	 * @param string $postalcode	The postal code for the credit card
	 * @return boolean
	 */
	public function setCreditCardInfo($cardno, $expmonth, $expyear, $name, $address, $postalcode)
	{
		// should probably do better checking here for failed sets.
		$b = FALSE;
		$b = $this->set('CreditCardInfo CreditCardNumber', $cardno);
		$b = $this->set('CreditCardInfo ExpirationMonth', $expmonth);
		$b = $this->set('CreditCardInfo ExpirationYear', $expyear);
		$b = $this->set('CreditCardInfo NameOnCard', $name);
		$b = $this->set('CreditCardInfo CreditCardAddress', $address);
		$b = $this->set('CreditCardInfo CreditCardPostalCode', $postalcode);
		
		return $b;
	}

	/**
	 * Get credit card information from the refund
	 * 
	 * @param string $part		If you just want a specific part of the card info, specify it here
	 * @param array $defaults	Defaults for the card data if you want the entire array
	 * @return mixed			If you specify a part, a string part is returned, otherwise an array of card data
	 */
	public function getCreditCardInfo($part = null, $defaults = array())
	{
		if (!is_null($part))
		{
			return $this->getD('CreditCardInfo ' . $part);
		}
		
		return $this->getArray('CreditCardInfo *', $defaults);		
	}

	/**
	 * Set the address for the refund (optional)
	 * 
	 * @param string $addr1			Address line 1
	 * @param string $addr2			Address line 2
	 * @param string $addr3			Address line 3
	 * @param string $addr4			Address line 4
	 * @param string $addr5			Address line 5
	 * @param string $city			City
	 * @param string $state			State
	 * @param string $province		Province (Canadian editions of QuickBooks only!)
	 * @param string $postalcode	Postal code
	 * @param string $country		Country
	 * @param string $note			Notes
	 * @return void
	 */
	public function setAddress($addr1, $addr2 = '', $addr3 = '', $addr4 = '', $addr5 = '', $city = '', $state = '', $province = '', $postalcode = '', $country = '', $note = '')
	{
		for ($i = 1; $i <= 5; $i++)
		{
			$this->set('Address Addr' . $i, ${'addr' . $i});
		}
		
		$this->set('Address City', $city);
		$this->set('Address State', $state);
		$this->set('Address Province', $province);
		$this->set('Address PostalCode', $postalcode);
		$this->set('Address Country', $country);
		$this->set('Address Note', $note);  
	}

	/**
	 * Get the address 
	 * 
	 * @param string $part			A specific portion of the address to get (i.e. "Addr1" or "State")
	 * @param array $defaults		Default values if a value isn't filled in
	 * @return array				The address
	 */
	public function getAddress($part = null, $defaults = array())
	{
		if (!is_null($part))
		{
			return $this->getD('Address ' . $part);
		}
		
		return $this->getArray('Address *', $defaults);
	}

	/**
	 * Set the transaction date
	 * 
	 * @param string $date
	 * @return boolean
	 */
	public function setTxnDate($date)
	{
		return $this->setDateType('TxnDate', $date);
	}

	/**
	 * Get the transaction date
	 * 
	 * @param string $format	The format you want the date in (as for {@link http://www.php.net/date})
	 * @return string
	 */
	public function getTxnDate($format = 'Y-m-d')
	{
		//return date($format, strtotime($this->getD('TxnDate')));
		return $this->getDateType('TxnDate', $format);
	}


	protected function _cleanup()
	{
		return true;
	}

	public function asArray($request, $nest = true)
	{
		$this->_cleanup();
		return parent::asArray($request, $nest);
	}
	
	/**
	 * Convert this object to a valid qbXML request
	 * 
	 * @param string $request						The type of request to convert this to (ARRefundCreditCardAddRq, or ARRefundCreditCardQuery)
	 * @param boolean $todo_for_empty_elements		A constant, one of: QUICKBOOKS_XML_XML_COMPRESS, QUICKBOOKS_XML_XML_DROP, QUICKBOOKS_XML_XML_PRESERVE
	 * @param string $indent
	 * @param string $root
	 * @return string
	 */
	public function asQBXML($request, $todo_for_empty_elements = NULL, $indent = "\t", $root = null)
	{
		$this->_cleanup();
		
		return parent::asQBXML($request, $todo_for_empty_elements, $indent, $root);
	}

	public function object()
	{
		return QUICKBOOKS_OBJECT_CREDITCARDREFUND;
	}
}
