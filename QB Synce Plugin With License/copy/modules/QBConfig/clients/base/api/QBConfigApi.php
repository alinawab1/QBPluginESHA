<?php

require_once('include/vCard.php');
require_once('include/api/SugarApi.php');
require_once('clients/base/api/ModuleApi.php');
require_once('custom/include/QBServer/QuickBooks.php');

/**
 * Custom QBConfigApi extends from SugarApi
 */
class QBConfigApi extends ModuleApi {

	public function registerApiRest() {
		return array(
			'create' => array(
				'reqType' => 'POST',
				'path' => array('QBConfig'),
				'pathVars' => array('module'),
				'method' => 'createRecord',
				'shortHelp' => 'This method creates a new record of the specified type',
				'longHelp' => 'include/api/help/module_post_help.html',
			),
			'qbFileDownload' => array(
				'reqType' => 'GET',
				'path' => array('QBConfig', '?', 'qbFile'),
				'pathVars' => array('module', 'record', ''),
				'method' => 'qbFileDownload',
				'rawReply' => true,
				'allowDownloadCookie' => true,
				'shortHelp' => 'An API to download a contact as a vCard.',
				'longHelp' => 'include/api/help/module_vcarddownload_get_help.html',
			),
			'getConfig' => array(
				'reqType' => 'GET',
				'path' => array('QBConfig', 'getConfig', '?'),
				'pathVars' => array('module', '', 'userId'),
				'method' => 'getConfig',
				'shortHelp' => 'Generate QuickBooks Connector file',
				'longHelp' => 'modules/QBConfig/clients/base/api/help/QBConfig.html',
			),
			'update' => array(
				'reqType' => 'PUT',
				'path' => array('QBConfig', '?'),
				'pathVars' => array('module', 'record'),
				'method' => 'updateRecord',
				'shortHelp' => 'This method updates a record of the specified type',
				'longHelp' => 'include/api/help/module_record_put_help.html',
			),
			'createUser' => array(
				'reqType' => 'POST',
				'path' => array('QBConfig', 'createUser'),
				'pathVars' => array('module', ''),
				'method' => 'createUser',
				'shortHelp' => 'Creating necessary tables and user in the quickbooks database tables',
				'longHelp' => 'modules/QBConfig/clients/base/api/help/QBConfig.html',
			),
			'validateLicense' => array(
				'reqType' => 'POST',
				'path' => array('QBConfig', 'validateLicense'),
				'pathVars' => array('module', ''),
				'method' => 'validateLicense',
				'shortHelp' => 'Validating QuickBooks Plugin License',
				'longHelp' => 'modules/QBConfig/clients/base/api/help/QBConfig.html',
			),
			'clearMapping' => array(
				'reqType' => 'GET',
				'path' => array('QBConfig', 'clearMapping', '?'),
				'pathVars' => array('module', '', 'userId'),
				'method' => 'clearMapping',
				'shortHelp' => 'Clear all the mapping of synced records',
				'longHelp' => 'modules/QBConfig/clients/base/api/help/QBConfig.html',
			)
		);
	}

	public function createRecord($api, $args) {
		$GLOBALS['log']->fatal("Inside Create QB Config Record");
		global $current_user, $sugar_config;
		$dsn = "{$sugar_config['dbconfig']['db_type']}://{$sugar_config['dbconfig']['db_user_name']}:{$sugar_config['dbconfig']['db_password']}@{$sugar_config['dbconfig']['db_host_name']}/{$sugar_config['dbconfig']['db_name']}";
		if (!QuickBooks_Utilities::initialized($dsn)) {
			QuickBooks_Utilities::initialize($dsn);
		}
		QuickBooks_Utilities::createUser($dsn, $current_user->user_name, $args['connector_password']);
		$api->action = 'save';
		$this->requireArgs($args, array('module'));

		$bean = BeanFactory::newBean($args['module']);

		// TODO: When the create ACL goes in to effect, add it here.
		if (!$bean->ACLAccess('save')) {
			// No create access so we construct an error message and throw the exception
			$moduleName = null;
			if (isset($args['module'])) {
				$failed_module_strings = return_module_language($GLOBALS['current_language'], $args['module']);
				$moduleName = $failed_module_strings['LBL_MODULE_NAME'];
			}
			$args = null;
			if (!empty($moduleName)) {
				$args = array('moduleName' => $moduleName);
			}
			throw new SugarApiExceptionNotAuthorized('EXCEPTION_CREATE_MODULE_NOT_AUTHORIZED', $args);
		}

		if (!empty($args['id'])) {
			// Check if record already exists
			if (BeanFactory::getBean(
							$args['module'], $args['id'], array('strict_retrieve' => true, 'disable_row_level_security' => true)
					)) {
				throw new SugarApiExceptionInvalidParameter(
				'Record already exists: ' . $args['id'] . ' in module: ' . $args['module']
				);
			}
			// Don't create a new id if passed in
			$bean->new_with_id = true;
		}

		$id = $this->updateBean($bean, $api, $args);

		$args['record'] = $id;

		$this->processAfterCreateOperations($args, $bean);

		return $this->getLoadedAndFormattedBean($api, $args, $bean);
	}

	public function updateRecord($api, $args) {
		$api->action = 'view';
		$this->requireArgs($args, array('module', 'record'));

		$bean = $this->loadBean($api, $args, 'save');
		$api->action = 'save';
		$this->oldValue = $bean->connector_password;
		if (!empty($args['license_key'])) {
		$GLOBALS['log']->fatal("Validating License");
			$user = get_user_name($bean->qb_user_list);
			$license = json_encode(array('key' => $args['license_key'], 'user' => $user));
			$response = json_decode($this->getLicenseKey("http://108.166.122.7/nservice/webServiceQB.php?params=$license&type=JSON"));
			if ($response->info->code == 1) {
				$args['key_expiration'] = $response->data->exp_date;
				$bean->key_expiration = $response->data->exp_date;
			} else {
				$bean->key_expiration = '';
				$args['key_expiration'] = '';
			}
		}
		$this->updateBean($bean, $api, $args);
		if (!empty($bean->connector_password) && $this->oldValue != $bean->connector_password) {
			$GLOBALS['log']->fatal(print_r($bean->connector_password, 1));
			$this->updateConnectorPassword(get_user_name($bean->qb_user_list), $bean->connector_password);
		}
		return $this->getLoadedAndFormattedBean($api, $args, $bean);
	}

	/**
	 * qbFileDownload
	 *
	 * @param $api  ServiceBase The API class of the request, used in cases where the API changes how the fields are pulled from the args array.
	 * @param $args array The arguments array passed in from the API
	 *
	 * @return String
	 */
	public function qbFileDownload($api, $args) {
		$this->requireArgs($args, array('record', 'module'));
		$bean = $this->loadBean($api, $args);
		if (!$bean->ACLAccess('view')) {
			throw new SugarApiExceptionNotAuthorized('No access to download file for module: ' . $args['module']);
		}
		$user = BeanFactory::getBean("Users", $bean->assigned_user_id);
		$content = $this->getFileXML($user->user_name, $user->full_name);
		$filename = str_replace(' ', '-', $user->full_name);

		return $this->fileDownload($api, $content, $filename);
	}

	public function fileDownload(ServiceBase $api, $content, $filename) {
		global $locale;
		$api->setHeader("Content-Disposition", "attachment; filename={$filename}-connector-file.qwc");
		$api->setHeader("Content-Type", "text/xml; charset=" . $locale->getExportCharset());
		$api->setHeader("Expires", "Mon, 26 Jul 1997 05:00:00 GMT");
		$api->setHeader("Last-Modified", TimeDate::httpTime());
		$api->setHeader("Cache-Control", "max-age=0");
		$api->setHeader("Pragma", "public");

		return $locale->translateCharset($content, 'UTF-8', $locale->getExportCharset());
	}

	/**
	 * saving the configuration for quickbooks and sugarcrm
	 * 
	 * @param type $username
	 * @param type $fullname
	 * @return boolean
	 */
	protected function getFileXML($username, $fullname) {

		$GLOBALS['log']->fatal("Inside getFileXML");
		global $sugar_config;
		require_once 'custom/include/QBServer/QuickBooks.php';

		$name = "$fullname Web Connector App"; // A name for your server (make it whatever you want)
		$descrip = 'An example QuickBooks SOAP Server';  // A description of your server 
		$appurl = "{$sugar_config['site_url']}/index.php?entryPoint=QBServer";  // This *must* be httpS:// (path to your QuickBooks SOAP server)
		$appsupport = "{$sugar_config['site_url']}/index.php?entryPoint=QBHelp";   // This *must* be httpS:// and the domain name must match the domain name above
		$fileid = create_guid();  // Just make this up, but make sure it keeps that format
		$ownerid = create_guid();  // Just make this up, but make sure it keeps that format
		$qbtype = QUICKBOOKS_TYPE_QBFS; // You can leave this as-is unless you're using QuickBooks POS
		$readonly = false; // No, we want to write data to QuickBooks
		$run_every_n_seconds = 600; // Run every 600 seconds (10 minutes)
		// Generate the XML file
		$QWC = new QuickBooks_WebConnector_QWC($name, $descrip, $appurl, $appsupport, $username, $fileid, $ownerid, $qbtype, $readonly, $run_every_n_seconds);
		$xml = $QWC->generate();

		return $xml;
	}

	/**
	 * @function getConfig
	 * @description retrieving configuration on userId base
	 * @global type $current_user
	 * @param type $api
	 * @param type $args
	 * @return type
	 */
	public function getConfig($api, $args) {
		global $current_user;
		$userId = isset($args['userId']) ? $args['userId'] : $current_user->id;
		$qbconfig = BeanFactory::getBean("QBConfig");
		$qbconfig->retrieve_by_string_fields(array('assigned_user_id' => $userId));
		if (!empty($qbconfig->id)) {
			$qbconfig->retrieve($qbconfig->id);
		}
		return $qbconfig;
	}

	/**
	 * @function createUser
	 * @description creating quickbooks user in QuickBooks user table
	 * @global type $sugar_config
	 * @global type $current_user
	 * @param type $api
	 * @param type $args
	 */
	public function createUser($api, $args) {
		global $sugar_config, $current_user;
		$dsn = "{$sugar_config['dbconfig']['db_type']}://{$sugar_config['dbconfig']['db_user_name']}:{$sugar_config['dbconfig']['db_password']}@{$sugar_config['dbconfig']['db_host_name']}/{$sugar_config['dbconfig']['db_name']}";

		if (!QuickBooks_Utilities::initialized($dsn)) {
			// Initialize creates the neccessary database schema for queueing up requests and logging
			QuickBooks_Utilities::initialize($dsn);
		}
		// This creates a username and password which is used by the Web Connector to authenticate
		QuickBooks_Utilities::createUser($dsn, $current_user->user_name, $args['password']);
	}
	/**
	 * @function clearMapping
	 * @description removing mapping of all the synced records
	 * @global type $current_user
	 * @param type $api
	 * @param type $args
	 * @return boolean
	 */
	public function clearMapping($api, $args) {
		global $current_user, $db;
		$userId = isset($args['userId']) ? $args['userId'] : $current_user->id;
		$qbFileID = $this->getQBFileID($userId);

		$user = BeanFactory::getBean("Users",$userId);

		//return $userId.'-'.$user->user_name;
		if (!empty($qbFileID) && !empty($user->user_name)) {
			// Accounts Mapping
			$this->clearMappingRecords("Accounts", $qbFileID);

			// Accounts Mapping
			$this->clearMappingRecords("Opportunities", $qbFileID);

			// Quotes Mapping
			$this->clearMappingRecords("Quotes", $qbFileID);
			// Clearing Company Path
			// Products Catalog Mapping. Here first we check if there is one to many relationship exist then remove it otherwise set sync flag in PtroductTemplate
			$ProductTemplate = BeanFactory::getBean("ProductTemplates");

			/*
			if (@$ProductTemplate->load_relationship('product_template_qbfiles')) {
				$this->deleteProductQbfileRelationship("product_template_qbfiles", $qbFileID);
			} else {
			}*/

			$this->clearMappingRecords("ProductTemplates", $qbFileID);
			$query = "UPDATE quickbooks_user SET quickbooks_user.qb_company_file='' WHERE quickbooks_user.qb_username='$user->user_name'";
			$db->query($query);
			// Clearing Sync Dates
			$query = "UPDATE qbconfig SET qbconfig.qb_last_sync_date=null, qbconfig.sugar_last_sync_date=null WHERE qbconfig.qb_user_list='$userId'";
			$db->query($query);
		}
		return true;
	}

	function clearMappingRecords($moduleName, $qbFileID) {
		global $db;
		$module = BeanFactory::getBean($moduleName);
		$tablename=$module->table_name;
		$query = "UPDATE $tablename SET qb_id = '',qbfile_id = '',qb_synced = 0  WHERE qbfile_id='$qbFileID'";
		$db->query($query);
		//$where = "IFNULL({$module->table_name}.qb_id, '') != '' AND {$module->table_name}.qbfile_id = '$qbFileID'";
		/* $where = "{$module->table_name}.qbfile_id = '$qbFileID'";
       $fullList = $module->get_full_list('', $where);
       if (count($fullList) > 0) {
                       $GLOBALS['log']->fatal("Clearing $moduleName Data: ");
           foreach ($fullList as $account) {
                           $GLOBALS['log']->fatal("id: $account->id");
                           $GLOBALS['log']->fatal("Qb File ID: $account->qbfile_id");
                           $GLOBALS['log']->fatal("QB ID: $account->qb_id");
                           $GLOBALS['log']->fatal("qb synced: $account->qb_synced");
               $account->qb_id = '';
                               $account->qbfile_id = '';
               $account->qb_synced = 0;
               $db->update($account);
           }
           $GLOBALS['log']->fatal(count($fullList) . " $moduleName records updated");
       }*/
	}

	/**
	 * @function getQBFileID
	 * @description getting QB File Id against current User
	 * @return type
	 */
	function getQBFileID($userId) {
		$qbfile = BeanFactory::newBean("QBFiles");
		$query = new SugarQuery();
		$query->from($qbfile, array('team_security' => false));
		$query->select("{$qbfile->table_name}.id");
		$user = $query->join('qbusers')->joinName();
		$query->where()->equals("$user.id", $userId);
		$GLOBALS['log']->fatal("QB File Id SQL: " . $query->compileSql());
		$query->limit(1);
		$record = $query->execute();
		return isset($record[0]['id']) ? $record[0]['id'] : '';
	}

	/**
	 * @function updateConnectorPassword
	 * @description updating connector passowrd in QuickBooks user table
	 * @global type $db
	 * @param type $user
	 * @param type $pass
	 * @return string
	 */
	function updateConnectorPassword($user, $pass) {
		global $db;
		if (empty($user) || empty($pass)) {
			return '';
		}
		$temp['func'] = QUICKBOOKS_HASH;
		$hash = $temp['func']($pass . QUICKBOOKS_SALT);
		$query = "UPDATE quickbooks_user SET quickbooks_user.qb_password='$hash' WHERE quickbooks_user.qb_username='$user'";
		if ($db->query($query)) {
			$GLOBALS['log']->fatal("Connector Password Update for User($user) Password: $pass");
		}
	}

	/**
	 * @function validateLicense
	 * @global type $current_user
	 * @param type $api
	 * @param type $args
	 * @return type
	 */
	function validateLicense($api, $args) {

		global $current_user;
		if (isset($args['license']) && !empty($args['license'])) {
			$user = (isset($args['userId']) && !empty($args['userId'])) ? get_user_name($args['userId']) : $current_user->user_name;
			$license = json_encode(array('key' => $args['license'], 'user' => $user));
			$response = json_decode($this->getLicenseKey("http://108.166.122.7/nservice/webServiceQB.php?params=$license&type=JSON"));
		}
		return $response->info->code == 1 ? true : false;
	}

	/**
	 * @function getLicenseKey
	 * @description fetching license key data from other webservice using CURL
	 * @param type $Url
	 * @return type
	 */
	public function getLicenseKey($Url) {
		// is cURL installed yet?
		if (!function_exists('curl_init')) {
			$GLOBALS['log']->fatal("Sorry CURL is not installed!");
			return false;
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $Url);
		// Set a referer
		//	curl_setopt($ch, CURLOPT_REFERER, "http://www.example.org/yay.htm");
		// User agent
		//	curl_setopt($ch, CURLOPT_USERAGENT, "MozillaXYZ/1.0");
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		$output = curl_exec($ch);
		curl_close($ch);

		return $output;
	}

}
