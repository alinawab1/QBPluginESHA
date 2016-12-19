<?php

/**
 * 
 * @file utils.php
 * @description utility functions used for the quickbooks syncing process
 */

/**
 * fetching the account data using SugarQuery
 * @param type $module
 * @param type $options
 * @return type
 */
function getAccountData($module, $options = array(), $limit = 50) {
    $account = BeanFactory::newBean($module);
    $query = new SugarQuery();
    $query->from($account, array('team_security' => false));
    $email = $query->join('email_addresses_primary', array('joinType' => 'LEFT'))->joinName();
    if (isset($options['join'])) {
        $join = $query->join($options['join'], array('team_security' => false, 'joinType' => 'LEFT'))->joinName();
        $query->select(array("accounts.*", array("$join.id", "p_contact_id"), array("{$email}.email_address", "email1")));
    }
    if (isset($options['whereRaw'])) {
        $query->whereRaw($options['whereRaw']);
    }
    if (isset($options['where_ids'])) {
        $query->where()->in('id', $options['where_ids']);
    }
    $query->limit($limit);
    $GLOBALS['log']->fatal("Query $module Data SQL: " . $query->compileSql());
    return $query->execute();
}

/**
 * fetching the acc data by opp id using SugarQuery
 * @param type $module
 * @param type $options
 * @return type
 */
function getAccountDatabyOppId($oppID) {
    $acc = BeanFactory::newBean("Accounts");
    $query = new SugarQuery();
    $query->from($acc, array('team_security' => false));
    $query->select(array("accounts.*"));
    $query->joinTable('accounts_opportunities', array('joinType' => 'INNER', 'linkingTable' => true))
            ->on()
            ->equalsField('accounts_opportunities.account_id', 'accounts.id');
    $query->where()->equals('accounts_opportunities.opportunity_id', $oppID);
    $GLOBALS['log']->fatal("Query $module Data SQL: " . $query->compileSql());
    return $query->execute();
}
/**
 * fetching the opp data using SugarQuery
 * @param type $module
 * @param type $options
 * @return type
 */
function getOpportunityData($module, $options = array(), $limit = 50) {
    $opp = BeanFactory::newBean($module);
    $query = new SugarQuery();
    $query->from($opp, array('team_security' => false));
    $query->select(array("opportunities.*", array("accounts.qb_id", 'account_qb_id')));
    $query->joinTable('accounts_opportunities', array('joinType' => 'INNER', 'linkingTable' => true))
            ->on()
            ->equalsField('accounts_opportunities.opportunity_id', 'opportunities.id');

    $query->joinTable('accounts', array('joinType' => 'INNER', 'linkingTable' => true))
            ->on()
            ->equalsField('accounts.id', 'accounts_opportunities.account_id');

    $query->where()->equals('quote_id', $quoteID);

    $query->where()->in('id', $options['where_ids']);

    $query->limit($limit);
    $GLOBALS['log']->fatal("Query $module Data SQL: " . $query->compileSql());    
    return $query->execute();
}

/**
 * fetching the opp name using SugarQuery
 * @param type $module
 * @param type $options
 * @return type
 */
function getQuoteOppName($quoteID) {
    $opp = BeanFactory::newBean("Opportunities");
    $query = new SugarQuery();
    $query->from($opp, array('team_security' => false));
    $query->select(array("opportunities.name"));
    $query->joinTable('quotes_opportunities', array('joinType' => 'INNER', 'linkingTable' => true))
            ->on()
            ->equalsField('quotes_opportunities.opportunity_id', 'opportunities.id');
    $query->where()->equals('quotes_opportunities.quote_id', $quoteID);
    $query->where()->notEquals('opportunities.qb_id', '');
    $GLOBALS['log']->fatal("Query $module Data SQL: " . $query->compileSql());
    return $query->execute();
}
/**
 * fetching the opp qb id using SugarQuery
 * @param type $module
 * @param type $options
 * @return type
 */
function getQuoteOppQbId($quoteID) {
    $opp = BeanFactory::newBean("Opportunities");
    $query = new SugarQuery();
    $query->from($opp, array('team_security' => false));
    $query->select(array("opportunities.qb_id"));
    $query->joinTable('quotes_opportunities', array('joinType' => 'INNER', 'linkingTable' => true))
            ->on()
            ->equalsField('quotes_opportunities.opportunity_id', 'opportunities.id');
    $query->where()->equals('quotes_opportunities.quote_id', $quoteID);
    $query->where()->notEquals('opportunities.qb_id', '');
    $GLOBALS['log']->fatal("Query $module Data SQL: " . $query->compileSql());
    return $query->execute();
}

/**
 * fetching account IDs usign SugarQuery
 * @param type $module
 * @param type $options
 * @return type
 */
function getIDs($module, $options = array(), $limit = 50) {
    $bean = BeanFactory::newBean($module);
    $query = new SugarQuery();
    $query->from($bean, array('team_security' => false));
    if (isset($options['email'])) {
        $email = $query->join('email_addresses_primary', array('joinType' => 'LEFT'))->joinName();
        $query->select("{$bean->table_name}.id", "{$bean->table_name}.name", "{$bean->table_name}.qb_id", array("{$email}.email_address", "email1"));
    } else if (isset($options['item_category'])) {
        //$query->select("{$bean->table_name}.id", "{$bean->table_name}.name", "{$bean->table_name}.qb_id", array("{$bean->table_name}.qb_item_category_c", 'category'));
        //qb_product_name
        $query->select("{$bean->table_name}.id", array("{$bean->table_name}.qb_product_name", 'name'), "{$bean->table_name}.qb_id", array("{$bean->table_name}.qb_item_category_c", 'category'));
    } else if (isset($options['category'])) {
        //qb_product_name
        $query->select("{$bean->table_name}.id", array("{$bean->table_name}.qb_product_name", 'name'), "{$bean->table_name}.qb_id", array("{$bean->table_name}.qb_item_category_c", 'category'));
        //$query->select("{$bean->table_name}.id", "{$bean->table_name}.name", "{$bean->table_name}.qb_id", array("{$bean->table_name}.qb_item_category_c", 'category'));
//        $category = $query->join('category_link', array('joinType' => 'INNER'))->joinName();
//        $query->select("{$bean->table_name}.id", "{$bean->table_name}.name", "{$bean->table_name}.qb_id", array("$category.name", 'category'));
    } else if (isset($options['billing_accounts'])) {
        $account = $query->join('billing_accounts', array('alias' => 'QuoteCustomer', 'team_security' => false))->joinName();
        $query->whereRaw(" IFNULL($account.qb_id, '') != ''");
        $query->select("{$bean->table_name}.id", array("$account.name", "CustomerName"), "{$bean->table_name}.qb_id");
    } else {
        $query->select("{$bean->table_name}.id", "{$bean->table_name}.name", "{$bean->table_name}.qb_id");
    }
    if (isset($options['whereRaw'])) {
        $query->whereRaw($options['whereRaw']);
    }
    $query->limit($limit);
    $GLOBALS['log']->fatal("Query $module IDs SQL: " . $query->compileSql());
    return $query->execute();
}
/**
 * fetching account IDs usign SugarQuery
 * @param type $module
 * @param type $options
 * @return type
 */
function getProdIDs($module, $options = array(), $limit = 50) {
    $bean = BeanFactory::newBean($module);
    $query = new SugarQuery();
    $query->from($bean, array('team_security' => false));
    $query->joinTable('product_template_qbfiles', array('joinType' => 'INNER', 'linkingTable' => true))
        ->on()
        ->equalsField("{$bean->table_name}.id", 'product_template_qbfiles.product_template_id');
    if (isset($options['whereRaw'])) {
        $query->whereRaw($options['whereRaw']);
    }
    $query->select("{$bean->table_name}.id", array("{$bean->table_name}.qb_product_name", 'name'), "product_template_qbfiles.qb_id", array("{$bean->table_name}.qb_item_category_c", 'category'));
    $query->limit($limit);
    $GLOBALS['log']->fatal("Query $module IDs SQL: " . $query->compileSql());
    return $query->execute();
}

/**
 * Updating QB Ids in Sugar using Bean Objects
 * @param type $module
 * @param type $sugarID
 * @param type $qbID
 */
function updateProdQBId($module, $sugarID, $qbID, $qbfile_id) {
    global $db;
    $query = "update product_template_qbfiles set qb_synced  = 1, qb_id = '$qbID' where product_template_id = '$sugarID' and qbfile_id='$qbfile_id'";
    $db->query($query);
}


/**
 * fetching Account Data by email using SugarQuery
 * @param type $module
 * @param type $options
 * @return type
 */
function getAccountByEmail($module, $options = array(), $limit = 50) {
    $account = BeanFactory::newBean($module);
    $query = new SugarQuery();
    $query->from($account, array('team_security' => false));
    $email = $query->join('email_addresses_primary')->joinName();
    $query->select("{$account->table_name}.id", "{$account->table_name}.name", array("{$email}.email_address", "email1"));
    $query->where()->equals("{$email}.email_address", $options['email']);
    if (isset($options['whereRaw'])) {
        $query->whereRaw($options['whereRaw']);
    }
    $query->limit($limit);
    $GLOBALS['log']->fatal("Query $module  by Email({$options['email']}) SQL: " . $query->compileSql());
    return $query->execute();
}

/**
 * fetching Contact Data using SugarQuery
 * @param type $module
 * @param type $options
 * @return type
 */
function getContactData($module, $options = array()) {
    $contact = BeanFactory::newBean($module);
    $query = new SugarQuery();
    $query->from($contact, array('team_security' => false));
//	$email = $query->join('email_addresses_primary')->joinName();
//	$query->select(array("{$contact->table_name}.*", array("{$email}.email_address", "email1")));
    $query->select(array("{$contact->table_name}.*"));
    if (isset($options['whereRaw'])) {
        $query->whereRaw($options['whereRaw']);
    }
    $query->limit(1);
    $GLOBALS['log']->fatal("Query $module Data SQL: " . $query->compileSql());
    return $query->execute();
}

/**
 * fetching Products Data using SugarQuery
 * @param type $module
 * @param type $options
 * @return type
 */
function getProductData($module, $options = array()) {
    $product = BeanFactory::newBean($module);
    $query = new SugarQuery();
    $query->from($product, array('team_security' => false));
//	$category = $query->join('category_link')->joinName();
//	$query->select(array("{$product->table_name}.*", array("{$category}.name", "categoryName")));
    //qb_product_name
    $query->select(array("{$product->table_name}.*",array("{$product->table_name}.qb_product_name", "name"), array("{$product->table_name}.qb_item_category_c", "categoryName")));
    if (isset($options['whereRaw'])) {
        $query->whereRaw($options['whereRaw']);
    }
    if (isset($options['where_ids'])) {
        $query->where()->in('id', $options['where_ids']);
    }
    $query->limit(50);
    $GLOBALS['log']->fatal("Query $module Data SQL: " . $query->compileSql());
    return $query->execute();
}

/**
 * fetching Quotes Data using SugarQuery
 * @param type $module
 * @param type $options
 * @return type
 */
function getQuotesData($module, $options = array()) {
    $quote = BeanFactory::newBean($module);
    $query = new SugarQuery();
    $query->from($quote, array('team_security' => false));
    $account = $query->join('billing_accounts', array('alias' => 'QuoteCustomer', 'team_security' => false))->joinName();
    $query->joinTable('taxrates', array('alias' => 'tax', 'joinType' => 'LEFT', 'linkingTable' => true))
            ->on()
            ->equalsField("{$quote->table_name}.taxrate_id", 'tax.id')
            ->equals('tax.deleted', 0);
    $query->whereRaw(" IFNULL($account.qb_id, '') != ''");
    $query->select(array("{$quote->table_name}.*", array("$account.name", 'CustomerName'), array("$account.qb_id", 'CustomerID'), array("tax.name", 'TaxName')));
    if (isset($options['whereRaw'])) {
        $query->whereRaw($options['whereRaw']);
    }
    if (isset($options['where_ids'])) {
        $query->where()->in('id', $options['where_ids']);
    }
    $query->limit(1);
    $GLOBALS['log']->fatal("Query $module Data SQL: " . $query->compileSql());
    return $query->execute();
}

/**
 * createProductTemplate
 * @param type $module
 * @param type $options
 * @return type
 */
function getProductTemplates($qbfile_id,$shippingProductName) {
    $ProductTemplate = BeanFactory::newBean("ProductTemplates");
    $query = new SugarQuery();
    $query->from($ProductTemplate, array('team_security' => false));
    $query->joinTable('product_template_qbfiles', array('joinType' => 'INNER', 'linkingTable' => true))
        ->on()
        ->equalsField("product_templates.id", 'product_template_qbfiles.product_template_id');
    $query->select("product_templates.qb_product_name");
    $query->where()->equals('product_templates.qb_product_name', $shippingProductName);
    $query->where()->equals('product_template_qbfiles.qbfile_id', $qbfile_id);
    $GLOBALS['log']->fatal("Query Quote Products Data SQL: " . $query->compileSql());
    return $query->execute();
}
/**
 * fetching Quote Product Template Data using qbfile_id
 * @param type $module
 * @param type $options
 * @return type
 */
function createProductTemplate($qbfile_id,$shippingProductName) {
    $ProductTemplate = BeanFactory::getBean("ProductTemplates");
    $ProductTemplate->disable_row_level_security = true;
    $ProductTemplate->name=$shippingProductName;
    $ProductTemplate->qb_product_name=$shippingProductName;
    $ProductTemplate->cost_price=0;
    $ProductTemplate->discount_price=0;
    $ProductTemplate->list_price=0;
    $ProductTemplate->description="This product is used to send Quote Shipping amount as shipping line item in Quickbook. Please do not delete this product item.";
    //$ProductTemplate->qbfile_id=$qbfile_id;
    $ProductTemplate->qb_item_category_c="Service";
    $ProductTemplate->save();
    $ProductTemplate->load_relationship('product_template_qbfiles');
    $ProductTemplate->product_template_qbfiles->add($qbfile_id);
}

/**
 * fetching Quote Products Data using SugarQuery
 * @param type $module
 * @param type $options
 * @return type
 */
function getQuoteProducts($quoteID) {
    $product = BeanFactory::newBean("Products");
    $ProductTemplate = BeanFactory::getBean("ProductTemplates");
    $query = new SugarQuery();
    $query->from($product, array('team_security' => false));
    $query->joinTable('product_bundle_product', array('joinType' => 'INNER', 'linkingTable' => true))
            ->on()
            ->equalsField('products.id', 'product_bundle_product.product_id');

    $query->joinTable('product_bundle_quote', array('joinType' => 'INNER', 'linkingTable' => true))
            ->on()
            ->equalsField('product_bundle_quote.bundle_id', 'product_bundle_product.bundle_id');

    $query->joinTable('product_templates', array('joinType' => 'INNER', 'linkingTable' => true))
            ->on()
            ->equalsField('product_templates.id', 'products.product_template_id');
    //$query->orderBy('product_bundle_product.product_index', 'ASC');
    $query->orderBy('product_bundle_quote.bundle_index', 'ASC');
    $query->orderBy('product_bundle_product.product_index', 'ASC');

    $query->where()->equals('quote_id', $quoteID);
    $query->select(array("products.*", array("product_templates.qb_product_name", 'name')));

    $GLOBALS['log']->fatal("Query Quote Products Data SQL: " . $query->compileSql());
    return $query->execute();
}

/**
 * @function getStreetAddXML
 * @description diving street address to multiple <Addr> tags
 * @param type $streetAdd
 * @return string
 */
function getStreetAddXML($streetAdd) {
    $address = array();
    $streetAdd = str_replace(array("\n", "\r"), '', $streetAdd);
    if (strlen($streetAdd) > 40) {
        $add = wordwrap($streetAdd, 40, '\n');
        $address = explode('\n', $add);
    }
    $streetXML = '';
    if (count($address) > 0 && count($address) <= 5) {
        foreach ($address as $key => $addr) {
            $streetXML .= '<Addr' . ($key + 1) . '>' . $address[$key] . '</Addr' . ($key + 1) . '>';
        }
    } else if (strlen($streetAdd) < 40) {
        $streetXML = '<Addr1>' . $streetAdd . '</Addr1>';
    }
    return $streetXML;
}

/**
 * @function getName
 * @description triming name on the bases of length
 * @param type $name
 * @param type $len
 * @return type
 */
function getName($name, $len) {
    $name = html_entity_decode_utf8($name);
    if (strpos($name, '&amp;')) {
        $len += 4;
    }
    if (strpos($name, '&quot;')) {
        $len += 5;
    }
    if (strpos($name, '&#039;')) {
        //$name = str_replace('&#039;',"'",$name);
        $len += 5;
    }
    if (strpos($name, '&lt;')) {
        $len += 3;
    }
    if (strpos($name, '&gt;')) {
        $len += 3;
    }
    $name = str_replace(':','-',$name);
    $name = str_replace('&','and',$name);
    return substr($name, 0, $len);
}

/**
 * fetching Ids by QBIds using SugarQuery
 * @param type $module
 * @param type $qb_id
 * @return type
 */
function getIdByQBId($module, $qb_id, $name = '') {
    
    $bean = BeanFactory::newBean($module);
    $query = new SugarQuery();
    $query->from($bean, array('team_security' => false));
    $query->select("{$bean->table_name}.id");
    if (!empty($name)) {
        $name=explode(":",$name);
        $name=$name[0];
        //$query->where()->queryOr()->equals('qb_id', $qb_id)->equals("name", $name);
        if($module=="ProductTemplates"){
        $query->where()->equals("qb_product_name", $name);      
        }else{
        $query->where()->equals("name", $name);        
        }  
    } else {
        $query->where()->equals('qb_id', $qb_id);
    }
    $GLOBALS['log']->fatal("Query $module Id By QBId($qb_id): " . $query->compileSql());
    $query->limit(1);
    $record = $query->execute();
    return isset($record[0]['id']) ? $record[0]['id'] : '';
}

/**
 * fetching User Id by user_name using SugarQuery
 * @param type $user_name
 * @return type
 */
function getUserId($user_name) {
    $user = BeanFactory::newBean("Users");
    $query = new SugarQuery();
    $query->from($user, array('team_security' => false));
    $query->select("{$user->table_name}.id");
    $query->where()->equals("user_name", $user_name);
    $GLOBALS['log']->fatal("Query User Id  by ($user_name) SQL: " . $query->compileSql());
    $query->limit(1);
    $record = $query->execute();
    return isset($record[0]['id']) ? $record[0]['id'] : '';
}

/**
 * fetching QBFile Id by user Id
 * @param type $userID
 * @return type
 */
function getQBFileId($userID) {
    $qbfile = BeanFactory::newBean("QBFiles");
    $query = new SugarQuery();
    $query->from($qbfile, array('team_security' => false));
    $query->select("{$qbfile->table_name}.id");
    $user = $query->join('qbusers')->joinName();
    $query->where()->equals("$user.id", $userID);
    $GLOBALS['log']->fatal("Query QBFile Id by ($userID) SQL: " . $query->compileSql());
    $query->limit(1);
    $record = $query->execute();
    return isset($record[0]['id']) ? $record[0]['id'] : '';
}

/**
 * creating Account and Contact record in Sugar using Bean Objects
 * @global type $current_user
 * @param type $data
 * @param type $config
 */
function createAccount($data, $config = array()) {
    global $current_user;
    $current_user->id = $config->userId;
    $GLOBALS['log']->fatal("($config->userId) Inside Create Account processing " . count($data) . " records");
    if (empty($config->qbFileId)) {
        $GLOBALS['log']->fatal("Error! No QBFile record exists in SugarCRM against user($config->userId)");
        return '';
    }
    foreach ($data as $record) {
        /*
         * Skipping jobs: check if (Sublevel is 1) the customer is child record the do nothing.
         */
        if (isset($record['Sublevel']) && (int) $record['Sublevel'] > 0) {
            $job_name = $record['Name'];
            $GLOBALS['log']->fatal("Skipping jobs $job_name: check if (Sublevel > 0) the customer is child record the do nothing.");
            continue;
        }
        $start = microtime();
        $GLOBALS['log']->fatal("{$record['Name']} Time Start: " . $start);
        $acct = BeanFactory::getBean("Accounts");
        $acct->disable_row_level_security = true;
        $acctID = getIdByQBId("Accounts", $record['ListID']);
        if (!empty($acctID)) {
            $acct->retrieve($acctID);
        }
        if ($config->qbConfig['sugar_account_create'] != '1' && empty($acct->id)) {
            $GLOBALS['log']->fatal("{$record['Name']} Skipped! Can't create New Account in Sugar");
            continue;
        }
        $acct->created_by = $config->userId;
        $acct->modified_by = $config->userId;
        $acct->assigned_user_id = $config->userId;
        $acct->team_id = $config->qbConfig['team_id'];
        $acct->team_set_id = $config->qbConfig['team_set_id'];
        $acct->name = getValue($record, 'Name');
        if (isset($record['BillAddress'])) {
            $acct->billing_address_street = getStreetAddress($record['BillAddress']);
            $acct->billing_address_city = getValue($record['BillAddress'], 'City');
            $acct->billing_address_state = getValue($record['BillAddress'], 'State');
            $acct->billing_address_postalcode = getValue($record['BillAddress'], 'PostalCode');
            $acct->billing_address_country = getValue($record['BillAddress'], 'Country');
        }
        if (isset($record['ShipAddress'])) {
            $acct->shipping_address_street = getStreetAddress($record['ShipAddress']);
            $acct->shipping_address_city = getValue($record['ShipAddress'], 'City');
            $acct->shipping_address_state = getValue($record['ShipAddress'], 'State');
            $acct->shipping_address_postalcode = getValue($record['ShipAddress'], 'PostalCode');
            $acct->shipping_address_country = getValue($record['ShipAddress'], 'Country');
        }
        $acct->phone_office = getValue($record, 'Phone');
        $acct->phone_alternate = getValue($record, 'AltPhone');
        $acct->phone_fax = getValue($record, 'Fax');
        $acct->email1 = getValue($record, 'Email');
        $GLOBALS['log']->fatal("ID: $acct->id, Name: $acct->name, Email: $acct->email1, Contact ID: $acct->primary_contact_id");
        $acct->description = getValue($record, 'Notes');
        $acct->qb_synced = true;
        $acct->qb_id = getValue($record, 'ListID');
        $acct->opening_balance = getValue($record, 'Balance');
        $acct->qbfile_id = $config->qbFileId;
        $iscontact = false;
        if (!empty($record['FirstName']) || !empty($record['LastName'])) {
            $cont = BeanFactory::getBean("Contacts", $acct->primary_contact_id, array('disable_row_level_security' => true));
            $cont->first_name = getValue($record, 'FirstName');
            $cont->last_name = getValue($record, 'LastName');
            $cont->salutation = getValue($record, 'Salutation');
            $cont->email1 = getValue($record, 'Email');
            //$cont->phone_mobile = getValue($record, 'AltPhone');

            foreach ($record['AdditionalContactRef'] as $key => $value) {
                if ($value['ContactName'] == 'Mobile') {
                    $cont->phone_mobile = $value['ContactValue'];
                }
            }

            $cont->phone_work = getValue($record, 'Phone');
            $cont->created_by = $config->userId;
            $cont->modified_by = $config->userId;
            $cont->assigned_user_id = $config->userId;
            $cont->team_id = $config->qbConfig['team_id'];
            $cont->team_set_id = $config->qbConfig['team_set_id'];
            $cont->save();
            $iscontact = true;
            $acct->primary_contact_id = $cont->id;
        }
        $acct->save();
        if ($iscontact) {
            $cont->primary_account_id = $acct->id;
            $cont->save();
            $cont->load_relationship('paccounts');
            $cont->paccounts->add($acct->id);
            $acct->load_relationship('pcontacts');
            $acct->pcontacts->add($cont->id);
            $acct->load_relationship('contacts');
            $acct->contacts->add($cont->id);
        }

        $end = microtime();
        $GLOBALS['log']->fatal("Time End: " . $end);
        $GLOBALS['log']->fatal("Time Diff: " . microtime_diff($start, $end));
    }
}

/**
 * creating Product Templates in Sugar using Bean Objects
 * @global type $current_user
 * @param type $data
 * @param type $config
 */
function createProduct($data, $config = array()) {
    global $current_user,$db;
    $current_user->id = $config->userId;
    $GLOBALS['log']->fatal("($config->userId) Inside Create Product");
    if (empty($config->qbFileId)) {
        $GLOBALS['log']->fatal("Error! No QBFile record exists in SugarCRM against user($config->userId)");
        return '';
    }
    $countA = 0;
    $countU = 0;
    foreach ($data as $categoryName => $products) {
        $category = BeanFactory::getBean("ProductCategories");
        $category->disable_row_level_security = true;
        $category->retrieve_by_string_fields(array('name' => $categoryName));
        if (empty($category->id)) {
            $category->name = $categoryName;
            $category->save();
        }
        foreach ($products as $record) {
            $start = microtime();
            $GLOBALS['log']->fatal("Category: $categoryName Product:{$record['Name']} Time Start: " . $start);
            $product = BeanFactory::getBean("ProductTemplates");
            $product->disable_row_level_security = true;
            $productID = getIdByQBId("ProductTemplates", $record['ListID'], $record['Name']);
            //Check if this is new product added in QB becoz we do not want to updatre product from QB to CRM. In case of two way sync remove this condition first
            if (empty($productID)) {
                if (!empty($productID)) {
                    $product->retrieve($productID);
                    $countU++;
                } else {
                    $countA++;
                }
                $product->category_id = $category->id;
                $product->created_by = $config->userId;
                $product->modified_by = $config->userId;
                $product->assigned_user_id = $config->userId;
                $product->team_id = $config->qbConfig['team_id'];
                $product->team_set_id = $config->qbConfig['team_set_id'];
                if (empty($productID)) {
                    $product->name = getValue($record, 'Name');
                }
                $product->qb_product_name = getValue($record, 'Name');

                $product->cost_price = isset($record['SalesAndPurchase']) ? getValue($record['SalesAndPurchase'], 'PurchaseCost') : (isset($record['PurchaseCost']) ? getValue($record, 'PurchaseCost') : getValue($record, 'Price'));
                $product->discount_price = isset($record['SalesAndPurchase']) ? getValue($record['SalesAndPurchase'], 'SalesPrice') : (isset($record['SalesPrice']) ? getValue($record, 'SalesPrice') : getValue($record, 'Price'));
                $product->list_price = isset($record['SalesAndPurchase']) ? getValue($record['SalesAndPurchase'], 'SalesPrice') : (isset($record['SalesPrice']) ? getValue($record, 'SalesPrice') : getValue($record, 'Price'));
                $product->mft_part_num = getValue($record, 'ManufacturerPartNumber');
                $product->description = isset($record['SalesAndPurchase']) ? getValue($record['SalesAndPurchase'], 'SalesDesc') : (isset($record['SalesDesc']) ? getValue($record, 'SalesDesc') : getValue($record, 'Desc'));
                //$product->qb_synced = true;
                //$product->qb_id = getValue($record, 'ListID');
                //$product->qbfile_id = $config->qbFileId;
                $product->save();
                $qbID = getValue($record, 'ListID');
                if (empty($productID)) {
                    $product->load_relationship('product_template_qbfiles');
                    $product->product_template_qbfiles->add($config->qbFileId);
                }
                $db->query("update product_template_qbfiles set qb_synced  = 1, qb_id = '$qbID' where product_template_id = '$product->id' and qbfile_id='$config->qbFileId'");
                $end = microtime();
                $GLOBALS['log']->fatal("Category: $categoryName Product:{$record['Name']} Time End: " . $end);
                $GLOBALS['log']->fatal("Time Diff: " . microtime_diff($start, $end));
            }
        }
    }
    logMe("Products Added and Created", "$countA products added and $countU products Update in Sugar", $config->userId);
}

/**
 * creating Sales History in Sugar using Bean Object
 * @global type $current_user
 * @param type $data
 * @param type $config
 */
function createSalesHistory($data, $config = array()) {
    global $current_user, $timedate;
    $current_user->id = $config->userId;
    $GLOBALS['log']->fatal("($config->userId) Inside Create Sales History");
    if (empty($config->qbFileId)) {
        $GLOBALS['log']->fatal("Error! No QB File exists in SugarCRM against user($config->userId)");
        return '';
    }
    foreach ($data as $record) {
        $quoteID = '';
        if (isset($record['LinkedTxn']) && !empty($record['LinkedTxn']['TxnID']) && ($record['LinkedTxn']['TxnType'] == "Estimate" || $record['LinkedTxn']['TxnType'] == "Invoice" || $record['LinkedTxn']['TxnType'] == "SalesOrder")) {
            $quoteID = getIdByQBId("Quotes", $record['LinkedTxn']['TxnID']);
            $quoteType = $record['LinkedTxn']['TxnType'];
        } else if (!empty($record['TxnID']) && $config->qbConfig['quote_maps_to'] == "Invoice") {
            $quoteID = getIdByQBId("Quotes", $record['TxnID']);
            $quoteType = "Invoice";
        }
        if (empty($quoteID)) {
            continue;
        }
        $quote = new Quote();
        $quote->retrieve($quoteID);
        $start = microtime();
        $salesHistory = BeanFactory::getBean("SalesHistory");
        $salesHistory->disable_row_level_security = true;
        $salesHistoryID = getIdByQBId("SalesHistory", $record['TxnID']);
        $accountID = getIdByQBId("Accounts", $record['CustomerRef']['ListID'], $record['CustomerRef']['FullName']);
        if (empty($accountID)) {
            continue;
        }
        if (!empty($salesHistoryID)) {
            $salesHistory->retrieve($salesHistoryID);
        }
        //$invName = "{$record['TemplateRef']['FullName']} - {$record['TxnNumber']}";
        $invName = "QuickBooks Invoice - {$record['TxnNumber']}";
        $GLOBALS['log']->fatal("Invoice:{$invName} Time Start: " . $start);
        $salesHistory->name = $invName;
        $salesHistory->account_id = $accountID;
        $salesHistory->quote_id = $quoteID;
        $salesHistory->invoice_number = $quote->qb_item_number;
        $salesHistory->quote_number = $quote->quote_num;
        $salesHistory->quote_type = $quoteType;
        $salesHistory->line_items = json_encode(ISHLogic::prepareProductsArray($record));
        $salesHistory->created_by = $config->userId;
        $salesHistory->modified_by = $config->userId;
        $salesHistory->assigned_user_id = $config->userId;
        $salesHistory->team_id = $config->qbConfig['team_id'];
        $salesHistory->team_set_id = $config->qbConfig['team_set_id'];
        if (isset($record['BillAddress'])) {
            $salesHistory->billing_address_street = getStreetAddress($record['BillAddress']);
            $salesHistory->billing_address_city = getValue($record['BillAddress'], 'City');
            $salesHistory->billing_address_state = getValue($record['BillAddress'], 'State');
            $salesHistory->billing_address_postalcode = getValue($record['BillAddress'], 'PostalCode');
            $salesHistory->billing_address_country = getValue($record['BillAddress'], 'Country');
        }
        if (isset($record['ShipAddress'])) {
            $salesHistory->shipping_address_street = getStreetAddress($record['ShipAddress']);
            $salesHistory->shipping_address_city = getValue($record['ShipAddress'], 'City');
            $salesHistory->shipping_address_state = getValue($record['ShipAddress'], 'State');
            $salesHistory->shipping_address_postalcode = getValue($record['ShipAddress'], 'PostalCode');
            $salesHistory->shipping_address_country = getValue($record['ShipAddress'], 'Country');
        }
        $salesHistory->duedate = getValue($record, 'DueDate');
        $salesHistory->ship_date = getValue($record, 'ShipDate');
        $salesHistory->subtotal = getValue($record, 'Subtotal');
        $salesHistory->qb_synced = true;
        $salesHistory->qb_id = getValue($record, 'TxnID');
        $salesHistory->qbfile_id = $config->qbFileId;
        $salesHistory->save();

        /*
         * Creating payment records  Start
         */

          if (isset($record['LinkedTxn'][0])) {
          $num=1;
          foreach ($record['LinkedTxn'] as $key => $value) {
          $payment_date=$timedate->to_display_date($value['TxnDate'], true, true, $current_user);
          $amount=(double)$value['Amount']*(-1);
          $QB_Payments = BeanFactory::getBean("QB_Payments");
          $QB_Payments->disable_row_level_security = true;
          $QB_Payments->name="Payment - $num";
          $QB_Payments->amount="$".$amount;
          $QB_Payments->payment_method=$value['TxnType'];
          $QB_Payments->reference_number=$value['RefNumber'];
          $QB_Payments->payment_date=$payment_date;
          $QB_Payments->saleshistory_id=$salesHistory->id;
          $QB_Payments->save();
          $num++;
          }
          } else {
          $payment_date=$timedate->to_display_date($record['LinkedTxn']['TxnDate'], true, true, $current_user);
          $amount=(double)$record['LinkedTxn']['Amount']*(-1);
          $QB_Payments = BeanFactory::getBean("QB_Payments");
          $QB_Payments->disable_row_level_security = true;
          $QB_Payments->name="Payment - 1";
          $QB_Payments->amount="$".$amount;
          $QB_Payments->payment_method=$record['LinkedTxn']['TxnType'];
          $QB_Payments->reference_number='';
          $QB_Payments->payment_date=$payment_date;
          $QB_Payments->saleshistory_id=$salesHistory->id;
          $QB_Payments->save();
          }

         /*
         * Creating payment records  End
         */
        $end = microtime();
        $GLOBALS['log']->fatal("Invoice: $invName Time End: " . $end);
        $GLOBALS['log']->fatal("Time Diff: " . microtime_diff($start, $end));
    }
}

function getStreetAddress($streetAdd) {
    $street = trim(
            getValue($streetAdd, 'Addr1') . ' '
            . getValue($streetAdd, 'Addr2') . ' '
            . getValue($streetAdd, 'Addr3') . ' '
            . getValue($streetAdd, 'Addr4') . ' '
            . getValue($streetAdd, 'Addr5')
    );
    return $street;
}

function removeLines($text) {
    return str_replace(array("\n", "\r"), "", $text);
}

/**
 * @function logMe
 * @description logging the errors in seperate module
 * @global type $current_user
 * @param type $title
 * @param type $desc
 * @param type $userId
 */
function logMe($title, $desc, $userId) {
    global $current_user;
    $current_user->id = $userId;
    $qblog = BeanFactory::getBean("QBLogs");
    $qblog->name = $title;
    $qblog->description = $desc;
    $qblog->assigned_user_id = $userId;
    $qblog->save();
}

/**
 * Updating QB Ids in Sugar using Bean Objects
 * @param type $module
 * @param type $sugarID
 * @param type $qbID
 */
function updateQBId($module, $sugarID, $qbID, $invoice_id = '') {
    $bean = BeanFactory::getBean($module, $sugarID, array('disable_row_level_security' => true));
    $bean->qb_id = $qbID;
    if ($invoice_id != '' && $module == "Quotes") {
        $bean->qb_item_number = $invoice_id;
    }
    $bean->qb_synced = true;
    $bean->save();
}

/**
 * Updating field in module using Query
 * @param type $module
 * @param type $sugarID
 * @param type $qbID
 */
function updateField($module, $field, $value, $userID = '') {
    global $db;
    $query = "UPDATE $module SET $field='$value' WHERE assigned_user_id='$userID'";
    $db->query($query);
}

/**
 * Updating field in module using Query
 * @param type $module
 * @param type $sugarID
 * @param type $qbID
 */
function updateCompanyPath($user, $value) {
    global $db;
    $query = "UPDATE quickbooks_user SET quickbooks_user.qb_company_file='" . addslashes($value) . "' WHERE quickbooks_user.qb_username='$user' AND IFNULL(quickbooks_user.qb_company_file, '') = ''";
    if ($db->query($query)) {
        $GLOBALS['log']->fatal("Updating Company file Path for User ($user) Path: $value");
    }
}

/**
 * fetching Last Sync Date using Sugar Query
 * @param type $module
 * @param type $sugarID
 * @param type $qbID
 */
function getSyncDate($field, $userID) {
    $qbconfig = BeanFactory::newBean("QBConfig");
    $query = new SugarQuery();
    $query->from($qbconfig, array('team_security' => false));
    $query->select("{$qbconfig->table_name}.$field");
    $query->where()->equals("assigned_user_id", $userID);
    $GLOBALS['log']->fatal("Sync Date($field): " . $query->compileSql());
    $query->limit(1);
    $record = $query->execute();
    return (isset($record[0][$field]) && !empty($record[0][$field])) ? $record[0][$field] : '1970-01-01 00:00:00';
}

/**
 * checking value exists are not
 * @param type $row
 * @param type $field_name
 * @return type
 */
function getValue($row, $field_name) {
    return isset($row[$field_name]) ? $row[$field_name] : '';
}

/**
 * converting XML string to Array
 * @param type $xmlstr
 * @return type
 */
function xmlstr_to_array($xmlstr) {
    $doc = new DOMDocument();
    $doc->loadXML($xmlstr);
    return domnode_to_array($doc->documentElement);
}

/**
 * 
 * @param type $node
 * @return type
 */
function domnode_to_array($node) {
    $output = array();
    switch ($node->nodeType) {
        case XML_CDATA_SECTION_NODE:
        case XML_TEXT_NODE:
            $output = trim($node->textContent);
            break;
        case XML_ELEMENT_NODE:
            for ($i = 0, $m = $node->childNodes->length; $i < $m; $i++) {
                $child = $node->childNodes->item($i);
                $v = domnode_to_array($child);
                if (isset($child->tagName)) {
                    $t = $child->tagName;
                    if (!isset($output[$t])) {
                        $output[$t] = array();
                    }
                    $output[$t][] = $v;
                } elseif ($v) {
                    $output = (string) $v;
                }
            }
            if (is_array($output)) {
                if ($node->attributes->length) {
                    $a = array();
                    foreach ($node->attributes as $attrName => $attrNode) {
                        $a[$attrName] = (string) $attrNode->value;
                    }
                    $output['@attributes'] = $a;
                }
                foreach ($output as $t => $v) {
                    if (is_array($v) && count($v) == 1 && $t != '@attributes') {
                        $output[$t] = $v[0];
                    }
                }
            }
            break;
    }
    return $output;
}

function shipping_product_exist() {
    $ProductTemplate = BeanFactory::getBean("ProductTemplates");
    $ProductTemplate->disable_row_level_security = true;
    $ProductTemplate->retrieve_by_string_fields(array('name' => 'Shipping'));
    if (empty($ProductTemplate->qb_id)) {
        return false;
    } else {
        return true;
    }
}
function getQbconfigByUser($user){
        require_once 'Configuration.php';
        $config = new Configuration($user);
        $config->getUserId();
        $config->getQBFileId();
        $config->loadConfiguration($config->userId);
        return $config;
}
