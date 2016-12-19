<?php

/**
 * @class ACLogic
 */
class ACLogic {

    public $queue;
    public $user;
    public $qbConfig;
    public $Qpriority;
    public $Upriority;
    public $Apriority;

    /**
     * 
     * @param type $user
     */
    function __construct($user) {
        $this->user = $user;
        $this->config = new Configuration($user);
        $this->config->getUserId();
        $this->config->getQBFileId();
        //loading the configuration
        $this->config->loadConfiguration($this->config->userId);
        $this->queue = $this->config->queue;
        $this->qbConfig = $this->config->qbConfig;
        // Setting the Priorities on the basis of Master System
        if ($this->qbConfig['master_system'] == 'QuickBooks') {
            $this->Qpriority = 20;
        } else {
            $this->Qpriority = 5;
        }
        $this->AQpriority = 15;
        $this->Apriority = 14;
        $this->Upriority = 13;
    }

    /**
     * @function getAddReq
     * @param type $ids
     */
    public function getAddReq($ids = array()) {
        $GLOBALS['log']->fatal("User: $this->user (Inside Customer Add Logic)");
        if (count($ids) <= 0) {
            $accountData = getIDs("Accounts", array('email' => true, 'whereRaw' => "IFNULL(accounts.qb_id,'') = '' AND IFNULL(accounts.qbfile_id,'')!='' AND accounts.qbfile_id='{$this->config->qbFileId}'"));
        } else {
            $accountData = $ids;
        }
        $extra = array();
        $add_in_queue = false;
        if (count($accountData) > 0) {
            $add_in_queue = true;
            foreach ($accountData as $account) {
                $name = getName($account['name'], 41);
                $extra[] = array(
                    'id' => $account['id'],
                    'email1' => $account['email1'],
                    'name' => $name,
                );
            }
        }
        if($this->qbConfig['opp_job_sync']=='1'){
        $opportunityData = getIDs("Opportunities", array('whereRaw' => "IFNULL(opportunities.qb_id,'') = '' AND IFNULL(opportunities.qbfile_id,'')!='' AND opportunities.qbfile_id='{$this->config->qbFileId}'"));
        if (count($opportunityData) > 0) {
            $add_in_queue = true;
            foreach ($opportunityData as $opportunity) {
                $name = getName($opportunity['name'], 41);
                $extra[] = array(
                    'id' => $opportunity['id'],
                    'name' => $name,
                );
            }
        }
        }
        if ($add_in_queue) {
            if (count($ids) <= 0) {
                $this->queue->enqueue(QUICKBOOKS_QUERY_CUSTOMER, 'CSMAQ', $this->AQpriority, $extra, $this->user);
            } else {
                $this->queue->enqueue(QUICKBOOKS_ADD_CUSTOMER, 'CSMA', $this->Apriority, $extra, $this->user);
            }
        }
    }

    /**
     * @function getUpdateReq
     * @param type $ids
     */
    public function getUpdateReq($ids = array()) {
        $GLOBALS['log']->fatal("User: $this->user (Inside Update Logic)");
        if (count($ids) <= 0) {
            $timedate = new TimeDate();
            $start_date = $timedate->nowDb(); //gmdate('Y-m-d H:i:s');
            $end_date = getSyncDate("sugar_last_sync_date", $this->config->userId);
            if ($this->qbConfig['product_item'] != '1') {
                updateField("qbconfig", "sugar_last_sync_date", $start_date, $this->config->userId);
            }
            $where = "accounts.date_modified between '$end_date' AND '$start_date'AND IFNULL(accounts.qb_id,'')!='' AND IFNULL(accounts.qbfile_id,'')!='' AND accounts.qbfile_id='{$this->config->qbFileId}'";
            $accountData = getIDs("Accounts", array('email' => true, 'whereRaw' => $where));
        } else {
            $accountData = $ids;
        }
        $extra = array();
        $add_in_queue = false;

        if (count($accountData) > 0) {
            $add_in_queue = true;
            foreach ($accountData as $account) {
                $extra[] = array(
                    'id' => $account['id'],
                    'email1' => $account['email1'],
                    'name' => $account['name'],
                    'qb_id' => $account['qb_id'],
                    'EditSequence' => isset($account['EditSequence']) ? $account['EditSequence'] : '',
                );
            }
        }
        if($this->qbConfig['opp_job_sync']=='1'){
        $where = "opportunities.date_modified between '$end_date' AND '$start_date'AND IFNULL(opportunities.qb_id,'')!='' AND IFNULL(opportunities.qbfile_id,'')!='' AND opportunities.qbfile_id='{$this->config->qbFileId}'";
        $opportunityData = getIDs("Opportunities", array('whereRaw' => $where));
        if (count($opportunityData) > 0) {
            $add_in_queue = true;
            foreach ($opportunityData as $opportunity) {
                $extra[] = array(
                    'id' => $opportunity['id'],
                    'name' => $opportunity['name'],
                    'qb_id' => $opportunity['qb_id'],
                    'EditSequence' => isset($opportunity['EditSequence']) ? $opportunity['EditSequence'] : '',
                );
            }
        }
        }
        if ($add_in_queue) {
            if (count($ids) <= 0) {
                $this->queue->enqueue(QUICKBOOKS_QUERY_CUSTOMER, 'CSMUQ', $this->Upriority, $extra, $this->user);
            } else {
                $this->queue->enqueue(QUICKBOOKS_MOD_CUSTOMER, 'CSMU', $this->Upriority, $extra, $this->user);
            }
        }
    }

    /**
     * @function getQueryReq
     */
    public function getQueryReq() {
        $GLOBALS['log']->fatal("User: $this->user (Inside Query Logic)");
        $this->queue->enqueue(QUICKBOOKS_QUERY_CUSTOMER, 'CSMQ', $this->Qpriority, null, $this->user);
    }

    /**
     * @function prepareCustomerXML
     * @param type $data
     * @return string
     */
    public function prepareCustomerXML($data) {
        $GLOBALS['log']->fatal("Preparing XML for " . count($data) . " records");
        $qbxml = '';
        foreach ($data as $value) {
            if (empty($value['qb_id'])) {
                $qbxml.='<CustomerAddRq requestID="$requestID">';
                $qbxml.='<CustomerAdd>';
            } else if (!empty($value['qb_id']) && isset($value['EditSequence']) && !empty($value['EditSequence'])) {
                $qbxml.='<CustomerModRq requestID="$requestID">';
                $qbxml .= '<CustomerMod>';
                $qbxml .= '<ListID >' . $value['qb_id'] . '</ListID>';
                $qbxml .= '<EditSequence >' . $value['EditSequence'] . '</EditSequence>';
            } else {
                continue;
            }
            $opening_balance = (int) $value['opening_balance'];
            $name = getName($value['name'], 41);
            $qbxml .= '<Name >' . $name . '</Name>';

            if (isset($value['job'])) {
                $data = getAccountDatabyOppId($value['id']);
                $acc = $data[0];
                $parent_name = getName($acc['name'], 41);
                $value['billing_address_street'] = $acc['billing_address_street'];
                $value['billing_address_city'] = $acc['billing_address_city'];
                $value['billing_address_state'] = $acc['billing_address_state'];
                $value['billing_address_postalcode'] = $acc['billing_address_postalcode'];
                $value['billing_address_country'] = $acc['billing_address_country'];
                $value['shipping_address_street'] = $acc['shipping_address_street'];
                $value['shipping_address_city'] = $acc['shipping_address_city'];
                $value['shipping_address_state'] = $acc['shipping_address_state'];
                $value['shipping_address_postalcode'] = $acc['shipping_address_postalcode'];
                $value['shipping_address_country'] = $acc['shipping_address_country'];
                $value['phone_office'] = $acc['phone_office'];
                $value['phone_fax'] = $acc['phone_fax'];
                $value['phone_alternate'] = $acc['phone_alternate'];
                $value['email1'] = $acc['email1'];
                $parent_name = getName($acc['name'], 41);
                $qbxml .= '<ParentRef><FullName >' . $parent_name . '</FullName></ParentRef>';
            } else {
                $qbxml .= '<IsActive >1</IsActive>';
                $qbxml .= '<CompanyName >' . $name . '</CompanyName>';
            }
            if (isset($value['Contacts']) && $value['Contacts'] != '') {
                $qbxml .= '<Salutation >' . $value['Contacts'][0]['salutation'] . '</Salutation>';
                $qbxml .= '<FirstName >' . $value['Contacts'][0]['first_name'] . '</FirstName>';
                $qbxml .= '<LastName >' . $value['Contacts'][0]['last_name'] . '</LastName>';
            }
            $qbxml .= '<BillAddress>';
            $qbxml .= getStreetAddXML($value['billing_address_street']);
            $qbxml .= '<City >' . $value['billing_address_city'] . '</City>';
            $qbxml .= '<State >' . $value['billing_address_state'] . '</State>';
            $qbxml .= '<PostalCode >' . $value['billing_address_postalcode'] . '</PostalCode>';
            $qbxml .= '<Country >' . $value['billing_address_country'] . '</Country>';
            $qbxml .= '</BillAddress>';
            $qbxml .= '<ShipAddress>';
            $qbxml .= getStreetAddXML($value['shipping_address_street']);
            $qbxml .= '<City >' . $value['shipping_address_city'] . '</City>';
            $qbxml .= '<State >' . $value['shipping_address_state'] . '</State>';
            $qbxml .= '<PostalCode >' . $value['shipping_address_postalcode'] . '</PostalCode>';
            $qbxml .= '<Country >' . $value['shipping_address_country'] . '</Country>';
            $qbxml .= '</ShipAddress>';
            $qbxml .= '<Phone >' . $value['phone_office'] . '</Phone>';
            $qbxml .= '<AltPhone >' . $value['phone_alternate'] . '</AltPhone>';
            $qbxml .= '<Fax >' . $value['phone_fax'] . '</Fax>';
            $qbxml .= '<Email >' . $value['email1'] . '</Email>';

            if (empty($value['qb_id'])) {
//                            $qbxml .= '<Balance >' .$opening_balance. '</Balance>';
//                            $qbxml .= '<OpenBalance >' .$opening_balance. '</OpenBalance>';
            }

            if (isset($value['Contacts']) && $value['Contacts'] != '') {
                $qbxml .= '<Contact >' . $value['Contacts'][0]['first_name'] . ' ' . $value['Contacts'][0]['last_name'] . '</Contact>';
            }
            if (!empty($value['description']))
                $qbxml .= '<Notes >' . $value['description'] . '</Notes>'; // Cannot be empty
            if (!empty($value['qb_id']) && isset($value['EditSequence']) && !empty($value['EditSequence'])) {
                $qbxml .= '</CustomerMod>';
                $qbxml .= '</CustomerModRq>';
            } else {
                $qbxml .= '</CustomerAdd>';
                $qbxml .= '</CustomerAddRq>';
            }
        }
        return $qbxml;
    }

    /**
     * It handles the Customer Duplication on both Sugar and Quick Book side
     * @function handleCustomerDuplication
     * @param type $user
     * @param type $dataArray
     * @param type $ext
     */
    public function handleCustomerDuplication($user, $dataArray, $ext) {
        
    }

}
