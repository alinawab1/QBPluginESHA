<?php 

/**
 * @file QBConfig.php
 * @author Rolustech
 * @description Quick Books Configuration icons in the Admin Panel
 */

global $sugar_version;

$admin_option_defs=array();

$admin_option_defs['Administration']['qbconfig']= array('Import','LBL_QB_SETUP_WIZARD_TITLE','LBL_QB_SETUP_WIZARD','javascript:parent.SUGAR.App.router.navigate("QBConfig/layout/qbconfig", {trigger: true});');

$admin_group_header[]= array('LBL_QB_CONFIG','',false,$admin_option_defs, '');

