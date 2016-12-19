<?php
 if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
$viewdefs['QBFiles']['base']['layout']['subpanels'] = array (
  'components' => array (
      array(
          'layout' => 'subpanel',
          'label' => 'LBL_ACCOUNT_SUBPANEL_TITLE',
          'context' => array(
              'link' => 'accounts',
          ),
      ),
      array(
          'layout' => 'subpanel',
          'label' => 'LBL_PRODUCT_CATALOG_SUBPANEL_TITLE',
          'context' => array(
              'link' => 'product_template_qbfiles',
          ),
      ),
      array(
          'layout' => 'subpanel',
          'label' => 'LBL_QUOTES_SUBPANEL_TITLE',
          'context' => array(
              'link' => 'quotes',
          ),
      ),
      array(
          'layout' => 'subpanel',
          'label' => 'LBL_SALES_HISTORY_SUBPANEL_TITLE',
          'context' => array(
              'link' => 'saleshistory',
          ),
      ),
  ),
  'type' => 'subpanels',
  'span' => 12,
);
