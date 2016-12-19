<?php

/**
 * @class Configuration
 */
class Configuration {

	public $queue;
	public $dsn;
	public $user;
	public $userId;
	public $qbFileId;
	public $qbConfig;

	function __construct($user) {
		global $sugar_config;
		$this->user = $user;
		$this->dsn = "{$sugar_config['dbconfig']['db_type']}://{$sugar_config['dbconfig']['db_user_name']}:{$sugar_config['dbconfig']['db_password']}@{$sugar_config['dbconfig']['db_host_name']}/{$sugar_config['dbconfig']['db_name']}";
		$this->queue = new QuickBooks_WebConnector_Queue($this->dsn);
	}

	/**
	 * set the user Id on the basis of user
	 * @function getUserId
	 */
	public function getUserId() {
		$this->userId = getUserId($this->user);
	}

	/**
	 * set the QB file Id on the basis of user Id
	 * @function getQBFileId
	 */
	public function getQBFileId() {
		if(empty($this->userId)) {
			$this->getUserId();
		}
		$this->qbFileId = getQBFileId($this->userId);
	}

	/**
	 * load the basic configuration against specific user
	 * @function loadConfiguration
	 * @param type $userID
	 * @return type
	 */
	public function loadConfiguration($userID) {
		$module = BeanFactory::getBean("QBConfig");
		$query = new SugarQuery();
		$query->select("{$module->table_name}.*");
		$query->from($module, array('team_security' => false));
		$query->where()->equals('assigned_user_id', $userID);
		$query->limit(1);
		$GLOBALS['log']->fatal("Configuration [$userID] SQL: " . $query->compileSql());
		$record = $query->execute();
		$this->qbConfig = isset($record[0]) ? $record[0] : '';
	}

	/**
	 * @function cleanQueue
	 * @description cleaning the quickbooks queue
	 * @global type $db
	 */
	public function cleanQueue() {
		global $db;
		$query = "DELETE FROM quickbooks_queue WHERE (quickbooks_queue.qb_status='q' OR quickbooks_queue.qb_status='i' OR quickbooks_queue.qb_status='e') AND quickbooks_queue.qb_username='$this->user'";
		$db->query($query);
	}
	
	public function validateLicense() {
		require_once 'modules/QBConfig/clients/base/api/QBConfigApi.php';
		$license = json_encode(array('key' => $this->qbConfig['license_key'], 'user' => $this->user));
		$Url = "http://108.166.122.7/nservice/webServiceQB.php?params=$license&type=JSON";
		$response = json_decode(QBConfigApi::getLicenseKey($Url));
		return $response->info->code == 1 ? true : false;
	}
}
