<?php

require_once 'utils.php';

/**
 * @class Pre Requests
 */
class PreRequests {

    public $user;
    public $userId;

    function __construct($user) {
        global $sugar_config;
        $this->user = $user;
    }

    /**
     * create Shipping Product against current logged in user
     */
    public function createShippingProduct() {
        global $db;
        $shippingProductName = "Shipping";
        $userId = getUserId($this->user);
        $query = "SELECT qbfiles_id FROM qbfiles_users WHERE users_id='$userId'";
        $result = $db->query($query);
        $qbfile = $db->fetchByAssoc($result);
        $qbfile_id = $qbfile['qbfiles_id'];
        $prod_template = getProductTemplates($qbfile_id, $shippingProductName);
        if ($prod_template[0]['qb_product_name'] != $shippingProductName) {
            createProductTemplate($qbfile_id, $shippingProductName);
            $GLOBALS['log']->fatal("Shippping product Template was created successfully");
        }
    }

    public function createShortNameForProductTemplates() {
        global $db;
        $query = "update product_templates set qb_product_name  = IF (CHAR_LENGTH(name)>31,CONCAT(SUBSTRING(name, 1, 15),'...',SUBSTRING(name, -5)), name )";
        $db->query($query);
    }


    public function duplicateFix() {
        $GLOBALS['log']->fatal("Running Duplicate Fix Script");
        global $db;
        $query = "SELECT id, name FROM accounts WHERE deleted =0 and name!='' GROUP BY name HAVING COUNT( * ) >1";
        $results=$db->query($query);
        $GLOBALS['log']->fatal(print_r($query2,true));
        $GLOBALS['log']->fatal("Duplicates");
        while($row=$db->fetchByAssoc($results)){
            $GLOBALS['log']->fatal($row);
            $name=$row['name'];
            $query1 = "SELECT id, name FROM accounts WHERE deleted =0 and Name ='$name'";
            $results1=$db->query($query1);
            $i=1;
            while($row1=$db->fetchByAssoc($results1)){
                $id=$row1['id'];
                $name1=$name.' '.$i;
                $query2 = "UPDATE accounts set name = $name1 WHERE id ='$id'";
                $db->query($query2);
                $GLOBALS['log']->fatal(print_r($query2,true));
                $i++;
            }
        }
    }

}
