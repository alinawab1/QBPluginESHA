<?php

if (!defined('sugarEntry') || !sugarEntry)
    die('Not A Valid Entry Point');

class ValidateName{
    function ValidateNameAction($bean, $event, $arguments){
        $bean->qb_product_name = str_replace(':','-',$bean->qb_product_name);
    }
}