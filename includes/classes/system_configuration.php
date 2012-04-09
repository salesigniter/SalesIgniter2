<?php
/*
 * Sales Igniter E-Commerce System
 * 
 * I.T. Web Experts
 * http://www.itwebexperts.com
 * 
 * Copyright (c) 2010 I.T. Web Experts
 *
 * This script and it's source is not redistributable
*/

/**
 * Holds all configuration settings from the configuration table
 */
class sysConfig
{

	/**
	 * @var array Holder for all configuration settings
	 */
	private static $config = array();

	/**
	 * @var array Holder for all javascript configuration settings
	 */
	private static $javascriptConfig = array();

	/**
	 * @var array Holder for all protected keys, used to check if a key is protected
	 */
	private static $protectedKeys = array();

	/**
	 * @var array Used to hold class references
	 */
	private static $classInstances = array();

	/**
	 * @var array Used to store already exploded keys to prevent doing it more than once
	 */
	private static $exploded = array();

	/**
	 * @static
	 * @param $function
	 * @param $args
	 * @return string
	 */
	public static function __callStatic($function, $args) {
		if (strstr($function, 'DirFs') || strstr($function, 'DirWs')){
			$define = '';
			for($i = 0, $n = sizeof($function); $i < $n; $i++){
				if (isset($lastLetter) && ctype_upper($function[$i])){
					$define .= '_' . $function[$i];
				}
				else {
					$define .= $function[$i];
				}
				$lastLetter = $function[$i];
			}
			return self::get(strtoupper($define));
		}
	}

	/**
	 * @static
	 *
	 */
	public static function init() {
		self::$config = array();

		$dirName = substr(dirname(__FILE__), 0, -7);

		$xmlData = simplexml_load_file(
			$dirName . 'configure.xml',
			'SimpleXMLElement',
			LIBXML_NOCDATA
		);

		foreach($xmlData->config as $cInfo){
			self::set(
				(string)$cInfo->key,
				(string)$cInfo->value,
				(isset($cInfo['protected']) && (string)$cInfo['protected'] == 'true')
			);
		}
		$xmlData = null;
		unset($xmlData);

		$httpDomainName = self::get('HTTP_DOMAIN_NAME');
		if (isset($_SERVER['HTTP_HOST'])){
			if (substr($_SERVER['HTTP_HOST'], 0, 4) == 'www.' && substr($httpDomainName, 0, 4) != 'www.'){
				$httpDomainName = 'www.' . $httpDomainName;
			}
			elseif (substr($_SERVER['HTTP_HOST'], 0, 4) != 'www.' && substr($httpDomainName, 0, 4) == 'www.') {
				$httpDomainName = substr($httpDomainName, 4);
			}
		}

		$httpsDomainName = self::get('HTTPS_DOMAIN_NAME');
		if (isset($_SERVER['HTTP_HOST'])){
			if (substr($_SERVER['HTTP_HOST'], 0, 4) == 'www.' && substr($httpsDomainName, 0, 4) != 'www.'){
				$httpsDomainName = 'www.' . $httpsDomainName;
			}
			elseif (substr($_SERVER['HTTP_HOST'], 0, 4) != 'www.' && substr($httpsDomainName, 0, 4) == 'www.') {
				$httpsDomainName = substr($httpsDomainName, 4);
			}
		}

		self::set('SERVER_NAME', $_SERVER['SERVER_NAME'], true, true);
		self::setMultiple(array(
			'HTTP_SERVER'          => 'http://' . $httpDomainName,
			'HTTP_CATALOG_SERVER'  => 'http://' . $httpDomainName,
			'HTTPS_SERVER'         => 'https://' . $httpsDomainName,
			'HTTPS_CATALOG_SERVER' => 'https://' . $httpsDomainName
		), false);

		self::setMultiple(array(
			'HTTP_COOKIE_PATH'    => self::get('DIR_WS_CATALOG'),
			'HTTPS_COOKIE_PATH'   => self::get('DIR_WS_CATALOG'),
			'HTTP_COOKIE_DOMAIN'  => $httpDomainName,
			'HTTPS_COOKIE_DOMAIN' => $httpsDomainName
		), false);

		self::set('DIR_WS_ADMIN', self::get('DIR_WS_CATALOG') . 'admin/', true, true);
		self::set('DIR_FS_ADMIN', self::get('DIR_FS_DOCUMENT_ROOT') . self::get('DIR_WS_CATALOG') . 'admin/', true, true);
		self::set('DIR_WS_HTTP_CATALOG', self::get('DIR_WS_CATALOG'), true);
		self::set('DIR_WS_HTTPS_CATALOG', self::get('DIR_WS_CATALOG'), true);
		self::set('DIR_FS_CATALOG', self::get('DIR_FS_DOCUMENT_ROOT') . self::get('DIR_WS_CATALOG'), true, true);

		self::set('DOCTRINE_CONN_STRING', 'mysql://' . self::get('DB_SERVER_USERNAME') . ':' . self::get('DB_SERVER_PASSWORD') . '@' . self::get('DB_SERVER') . '/' . self::get('DB_DATABASE'));
		self::set('REQUEST_TYPE', (getenv('HTTPS') == 'on' ? 'SSL' : 'NONSSL'), true, true);
		self::set('PRODUCT_LISTING_HIDE_NO_INVENTORY', 'False');

		if (self::get('REQUEST_TYPE') == 'NONSSL'){
			self::set('DIR_WS_CATALOG', self::get('DIR_WS_HTTP_CATALOG', false), false, true);
		}
		else {
			self::set('DIR_WS_CATALOG', self::get('DIR_WS_HTTPS_CATALOG', false), false, true);
		}

		self::set('CKEDITOR_FILEBROWSER_URL', self::get('DIR_WS_CATALOG') . 'ext/jQuery/external/filemanager/index.php', false, true);
	}

	/**
	 * @static
	 * @return array
	 */
	public static function getAll(){
		return self::$config;
	}

	/**
	 * Returns the correct relative catalog path based on the request type
	 *
	 * @static
	 * @param bool|string $forceType [optional] Used to force the request type, possible values ( SSL or NONSSL )
	 * @return string
	 */
	public static function getDirWsCatalog($forceType = false) {
		if ($forceType == 'NONSSL' || ($forceType === false && getenv('HTTPS') != 'on')){
			if (self::exists('DIR_WS_HTTP_CATALOG', false) === true){
				$returnDir = self::get('DIR_WS_HTTP_CATALOG', false);
			}
			else {
				$returnDir = self::get('DIR_WS_CATALOG', false);
			}
		}
		elseif ($forceType == 'SSL' || ($forceType === false && getenv('HTTPS') == 'on')) {
			if (self::exists('DIR_WS_HTTPS_CATALOG', false) === true){
				$returnDir = self::get('DIR_WS_HTTPS_CATALOG', false);
			}
			else {
				$returnDir = self::get('DIR_WS_CATALOG', false);
			}
		}
		else {
			die('ERROR: Unable to determine connection type (' . __FILE__ . '::' . __LINE__ . ')');
		}
		return $returnDir;
	}

	/**
	 * Returns the correct relative admin path based on the request type
	 *
	 * @static
	 * @param bool|string $forceType [optional] Used to force the request type, possible values ( SSL or NONSSL )
	 * @return string
	 */
	public static function getDirWsAdmin($forceType = false) {
		if ($forceType == 'NONSSL' || ($forceType === false && getenv('HTTPS') != 'on')){
			if (self::exists('DIR_WS_HTTP_ADMIN', false) === true){
				$returnDir = self::get('DIR_WS_HTTP_ADMIN', false);
			}
			else {
				$returnDir = self::get('DIR_WS_ADMIN', false);
			}
		}
		elseif ($forceType == 'SSL' || ($forceType === false && getenv('HTTPS') == 'on')) {
			if (self::exists('DIR_WS_HTTPS_ADMIN', false) === true){
				$returnDir = self::get('DIR_WS_HTTPS_ADMIN', false);
			}
			else {
				$returnDir = self::get('DIR_WS_ADMIN', false);
			}
		}
		else {
			die('ERROR: Unable to determine connection type (' . __FILE__ . '::' . __LINE__ . ')');
		}
		return $returnDir;
	}

	/**
	 * Returns the correct absolute admin path based on the request type
	 *
	 * @static
	 * @param bool|string $forceType [optional] Used to force the request type, possible values ( SSL or NONSSL )
	 * @return string
	 */
	public static function getDirFsAdmin($forceType = false) {
		if ($forceType == 'NONSSL' || ($forceType === false && getenv('HTTPS') != 'on')){
			if (self::exists('DIR_FS_HTTP_ADMIN', false) === true){
				$returnDir = self::get('DIR_FS_HTTP_ADMIN', false);
			}
			else {
				$returnDir = self::get('DIR_FS_ADMIN', false);
			}
		}
		elseif ($forceType == 'SSL' || ($forceType === false && getenv('HTTPS') == 'on')) {
			if (self::exists('DIR_FS_HTTPS_ADMIN', false) === true){
				$returnDir = self::get('DIR_FS_HTTPS_ADMIN', false);
			}
			else {
				$returnDir = self::get('DIR_FS_ADMIN', false);
			}
		}
		else {
			die('ERROR: Unable to determine connection type (' . __FILE__ . '::' . __LINE__ . ')');
		}
		return $returnDir;
	}

	/**
	 * Returns the correct absolute catalog path based on the request type
	 *
	 * @static
	 * @param bool|string $forceType [optional] Used to force the request type, possible values ( SSL or NONSSL )
	 * @return string
	 */
	public static function getDirFsCatalog($forceType = false) {
		if ($forceType == 'NONSSL' || ($forceType === false && getenv('HTTPS') != 'on')){
			if (self::exists('DIR_FS_HTTP_CATALOG', false) === true){
				$returnDir = self::get('DIR_FS_HTTP_CATALOG', false);
			}
			else {
				$returnDir = self::get('DIR_FS_CATALOG', false);
			}
		}
		elseif ($forceType == 'SSL' || ($forceType === false && getenv('HTTPS') == 'on')) {
			if (self::exists('DIR_FS_HTTPS_CATALOG', false) === true){
				$returnDir = self::get('DIR_FS_HTTPS_CATALOG', false);
			}
			else {
				$returnDir = self::get('DIR_FS_CATALOG', false);
			}
		}
		else {
			die('ERROR: Unable to determine connection type (' . __FILE__ . '::' . __LINE__ . ')');
		}
		return $returnDir;
	}

	/**
	 * Loads all the configuration settings from the configuration table
	 *
	 * @static
	 * @return void
	 */
	public static function load() {
		$Directory = new DirectoryIterator(self::getDirFsCatalog() . 'includes/configs/');
		foreach($Directory as $ConfigFile){
			if ($ConfigFile->isDot() || $ConfigFile->isDir()) {
				continue;
			}

			$Configuration = new MainConfigReader($ConfigFile->getBasename('.xml'));
			$Configuration->loadToSystem();
		}
	}

	/**
	 * Sets a configuration value
	 *
	 * @static
	 * @param string $k The key to be used when setting the configuration
	 * @param string $v The value to be used when setting the configuration
	 * @param bool $protected [optional] Sets the value to be protected
	 * @param bool $addToJavascript [optional] Sets the value in javascript too
	 * @return void
	 */
	public static function set($k, $v, $protected = false, $addToJavascript = false) {
		if (isset(self::$protectedKeys[$k])){
			trigger_error('Key Already Defined As Protected. (' . $k . ')', E_USER_ERROR);
			//				throw new Exception('Key Already Defined As Protected. (' . $k . ')');
			return;
		}

		if ($protected === true){
			self::$protectedKeys[$k] = true;
		}

		self::$config[$k] = $v;
		if ($addToJavascript === true){
			self::$javascriptConfig[$k] = $v;
		}
	}

	/**
	 * Sets an array of configuration keys/values
	 *
	 * @static
	 * @param array $array Associative array of keys/values to be set
	 * @param bool $protected [optional] Sets the values to be protected
	 * @param bool $addToJavascript [optional] Sets the value in javascript too
	 * @return void
	 */
	public static function setMultiple(array $array, $protected = false, $addToJavascript = false) {
		foreach($array as $k => $v){
			self::set($k, $v, $protected, $addToJavascript);
		}
	}

	/**
	 * Gets the configuration value based on the configuration key
	 *
	 * @static
	 * @param string $k The key to use to find the configuration value
	 * @param bool $load [optional] Try To Load the configuration from the database if it doesn't exist in internal array
	 * @return string
	 */
	public static function get($k, $load = true) {
		$return = '';
		if (isset(self::$config[$k])){
			$return = self::$config[$k];
		}
		elseif ($load === true){
			$ResultSet = Doctrine_Manager::getInstance()
				->getCurrentConnection()
				->fetchAssoc('select configuration_value from configuration where configuration_key = "' . $k . '"');
			if ($ResultSet && sizeof($ResultSet > 0)){
				self::set($k, $ResultSet[0]['configuration_value']);
			}
		}
		return $return;
	}

	/**
	 * Gets all the configurations set to be displayed in javascript
	 *
	 * @static
	 * @return array
	 */
	public static function getJavascriptConfigs(){
		return self::$javascriptConfig;
	}

	/**
	 * Determines if the configuration key has been set
	 *
	 * @static
	 * @param string $k The key to use to find the configuration value
	 * @param bool $load [optional] Try To Load the configuration from the database if it doesn't exist in internal array
	 * @return bool
	 */
	public static function exists($k, $load = true) {
		$exists = isset(self::$config[$k]);
		if ($exists === false && $load === true){
			self::get($k, true);
			$exists = isset(self::$config[$k]);
		}
		return $exists;
	}

	/**
	 * Determines if the configuration value is not empty
	 *
	 * @static
	 * @param string $k The key to use to find the configuration value
	 * @return bool
	 */
	public static function isNotEmpty($k) {
		$val = self::get($k);
		return !empty($val);
	}

	/**
	 * Determines if the passed value is in the set
	 *
	 * @static
	 * @param string $v The value to look for
	 * @param string $set The unexploded string of configuration values
	 * @param string $glue [optional] The glue used between the values
	 * @return bool
	 */
	public static function inSet($v, $set, $glue = ',') {
		$setArr = self::explode($set, $glue);
		return in_array($v, $setArr);
	}

	/**
	 * Explodes the values based on the $glue setting
	 *
	 * @static
	 * @param string $k The key to look for
	 * @param string $glue [optional] The glue used between the values
	 * @return array
	 */
	public static function explode($k, $glue = ',') {
		if (!isset(self::$exploded[$k])){
			self::$exploded[$k] = explode($glue, self::get($k));
		}
		return self::$exploded[$k];
	}

	/**
	 * Adds a class instance to the factory to be pulled later
	 *
	 * @static
	 * @param string $name The name to use when storing the object
	 * @param string $id The id to use when storing the object
	 * @param object $obj The class object to store
	 */
	public static function addClassInstance($name, $id, &$obj) {
		self::$classInstances[$name][$id] = $obj;
	}

	/**
	 * Returns a stored class instance
	 *
	 * @static
	 * @param string $name The name used to store the object
	 * @param string $id The id used to store the object
	 * @return object
	 * @return bool
	 */
	public static function getClassInstance($name, $id) {
		if (isset(self::$classInstances[$name][$id])){
			return self::$classInstances[$name][$id];
		}
		return false;
	}
}

?>