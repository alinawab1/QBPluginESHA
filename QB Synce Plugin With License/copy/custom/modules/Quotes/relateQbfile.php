<?php

if (!defined('sugarEntry') || !sugarEntry)
    die('Not A Valid Entry Point');

class RelateQbfile {

    function relateQbFileAction($bean, $event, $arguments) {
        $account = new Account();
        $account->retrieve($bean->billing_account_id);

        if (empty($account->qbfile_id)) {
            $GLOBALS['db']->query("UPDATE accounts set qbfile_id='$bean->qbfile_id' where id='$account->id'");
        }
        if (!empty($bean->opportunity_id)) {
            $opportunity = new Opportunity();
            $opportunity->retrieve($bean->opportunity_id);
            if (empty($opportunity->qbfile_id)) {
                $GLOBALS['db']->query("UPDATE opportunities set qbfile_id='$bean->qbfile_id' where id='$opportunity->id'");
            }
        }
        if (!empty($bean->qbfile_id)) {
            $sql = "select product_template_id from products where deleted =0 and quote_id = '$bean->id'";
            $result = $GLOBALS['db']->query($sql);
            while ($product = $GLOBALS["db"]->fetchByAssoc($result)) {
                $ProductTemplate = BeanFactory::getBean("ProductTemplates", $product['product_template_id'], array('disable_row_level_security' => true));
                $ProductTemplate->load_relationship('product_template_qbfiles');
                $ProductTemplate->product_template_qbfiles->add($bean->qbfile_id);
            }
        }
    }

    //beforesave
    function relateQbFileActionBeforeSave($bean, $event, $arguments) {
        $account = new Account();
        $account->retrieve($bean->billing_account_id);

        if (!empty($account->qbfile_id) && empty($bean->qbfile_id)) {
            $bean->qbfile_id = $account->qbfile_id;
        }
        $bean->name = str_replace(':', '-', $bean->name);
    }

}
