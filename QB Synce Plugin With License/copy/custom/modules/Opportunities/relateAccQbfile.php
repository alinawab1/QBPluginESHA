<?php

if (!defined('sugarEntry') || !sugarEntry)
    die('Not A Valid Entry Point');

class RelateAccQbfile {
    //relateAccQbFileAction
    function relateAccQbFileAction($bean, $event, $arguments) {
        $account = new Account();
        $account->retrieve($bean->account_id);
        if (empty($bean->qbfile_id) && !empty($account->qbfile_id)) {
                $bean->qbfile_id = $account->qbfile_id;
        }else if (!empty($bean->qbfile_id) && empty($account->qbfile_id)) {
            $GLOBALS['db']->query("UPDATE accounts set qbfile_id='$bean->qbfile_id' where id='$account->id'");
        }
    }

}
