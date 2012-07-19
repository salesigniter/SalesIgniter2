<?php
class SystemModulesLoader
{

	/**
	 * @var array
	 */
	protected static $Modules = array();

	/**
	 * @static
	 * @return array
	 */
	private static function getClassModules() {
		if (array_key_exists(static::$classPrefix, self::$Modules)){
			$returnVal = self::$Modules[static::$classPrefix];
		}
		else {
			$returnVal = array();
		}
		return $returnVal;
	}

	/**
	 * @static
	 * @param string $moduleName
	 * @param $class
	 */
	public static function registerModule($moduleName, &$class) {
		self::$Modules[static::$classPrefix][$moduleName] =& $class;
		if (method_exists(self::$Modules[static::$classPrefix][$moduleName], 'onLoad')){
			self::$Modules[static::$classPrefix][$moduleName]->onLoad();
		}
	}

	/**
	 * @static
	 * @param string $moduleName
	 */
	public static function unregisterModule($moduleName) {
		if (method_exists(self::$Modules[static::$classPrefix][$moduleName], 'onUnload')){
			self::$Modules[static::$classPrefix][$moduleName]->onUnload();
		}
		unset(self::$Modules[static::$classPrefix][$moduleName]);
	}

	/**
	 * @static
	 * @param bool $includeDisabled
	 * @return array
	 */
	public static function getModules($includeDisabled = false) {
		if ($includeDisabled === true){
			return self::getClassModules();
		}
		else {
			$returnArr = array();
			foreach(self::getClassModules() as $ModuleName => $Module){
				if ($Module->isEnabled() === true){
					$returnArr[$ModuleName] = $Module;
				}
			}
			return $returnArr;
		}
	}

	/**
	 * @static
	 * @return int
	 */
	public static function countEnabled() {
		$enabled = 0;
		foreach(self::getClassModules() as $ModuleName => $Module){
			if ($Module->isEnabled() === true){
				$enabled++;
			}
		}
		return $enabled;
	}

	/**
	 * @static
	 * @return bool
	 */
	public static function hasModules() {
		return (self::countEnabled() > 0);
	}

	/**
	 * @static
	 * @return array
	 */
	public static function getModuleDirs() {
		global $appExtension;
		$moduleDirs = array(
			sysConfig::getDirFsCatalog() . 'includes/modules/' . static::$dir . '/'
		);
		$extensions = $appExtension->getExtensions();
		foreach($extensions as $extCls){
			if ($extCls->isEnabled()){
				if (is_dir($extCls->getExtensionDir() . static::$dir . '/')){
					$moduleDirs[] = $extCls->getExtensionDir() . static::$dir . '/';
				}
			}
		}
		return $moduleDirs;
	}

	/**
	 * @static
	 * @param string $moduleName
	 * @return bool|string
	 */
	public static function findModuleDir($moduleName) {
		$moduleDir = false;
		foreach(self::getModuleDirs() as $dirName){
			$dirObj = new DirectoryIterator($dirName);
			foreach($dirObj as $dir){
				if ($dir->isDot() || $dir->isFile()) {
					continue;
				}

				if ($dir->getBasename() == $moduleName){
					$moduleDir = $dir->getPathname() . '/';
					break 2;
				}
			}
		}
		return $moduleDir;
	}

	/**
	 * @static
	 * @param string $moduleName
	 * @param bool $loadOnFail
	 * @return bool
	 */
	public static function isLoaded($moduleName, $loadOnFail = false) {
		$isLoaded = false;
		if (array_key_exists($moduleName, self::getClassModules())){
			$isLoaded = true;
		}
		else {
			if ($loadOnFail === true){
				if (self::loadModule($moduleName) === true){
					$isLoaded = true;
				}
			}
		}
		return $isLoaded;
	}

	/**
	 * @static
	 * @param string $moduleName
	 * @param bool $loadOnFail
	 * @return bool
	 */
	public static function isEnabled($moduleName, $loadOnFail = false) {
		$isEnabled = false;
		if (self::isLoaded($moduleName, $loadOnFail) === true){
			$classModules = self::getClassModules();
			if (array_key_exists($moduleName, $classModules)){
				$isEnabled = $classModules[$moduleName]->isEnabled();
			}
		}
		return $isEnabled;
	}

	/**
	 * @static
	 * @param string $moduleCode
	 * @param string|bool $dir
	 * @param bool $reloadModule
	 * @return bool
	 */
	public static function loadModule($moduleCode, $dir = false, $reloadModule = false) {
		if ($dir === false){
			$dir = self::findModuleDir($moduleCode);
		}

		$isLoaded = false;
		if ($dir !== false){
			$className = static::$classPrefix . ucfirst($moduleCode);
			if (class_exists($className) === false){
				require($dir . 'module.php');
			}

			$register = false;
			if (self::isLoaded($moduleCode) === true){
				if ($reloadModule === true || (isset(static::$alwaysLoadFresh) && static::$alwaysLoadFresh === true)){
					$classObj = new $className;
					$register = true;
				}
			}
			else {
				if (class_exists($className) === false){
					echo '<pre>';
					debug_print_backtrace();
					die('Class Not Included! :: ' . $dir . ' :: ' . $className);
				}
				$classObj = new $className;
				$register = true;
				if (is_dir($dir . 'Doctrine')){
					if ($classObj->isInstalled() === true && $classObj->isEnabled() === true){
						Doctrine_Core::loadModels($dir . 'Doctrine', Doctrine_Core::MODEL_LOADING_AGGRESSIVE);
					}
				}
			}

			if (isset($classObj) && is_object($classObj) && method_exists($classObj, 'updateStatus')){
				$classObj->updateStatus();
			}

			if ($register === true){
				self::registerModule($moduleCode, $classObj);
			}

			$isLoaded = true;
		}
		return $isLoaded;
	}

	/**
	 * @static
	 * @param string $moduleName
	 * @return bool
	 */
	public static function unloadModule($moduleName) {
		$unloaded = false;
		if (self::isLoaded($moduleName) === true){
			self::unregisterModule($moduleName);
			$unloaded = true;
		}
		return $unloaded;
	}

	/**
	 * @static
	 * @param bool $reloadAll
	 * @return bool
	 */
	public static function loadModules($reloadAll = false) {
		$modulesLoaded = false;
		foreach(self::getModuleDirs() as $dirName){
			$dirObj = new DirectoryIterator($dirName);
			foreach($dirObj as $dir){
				if ($dir->isDot() || $dir->isFile()) {
					continue;
				}

				if (self::loadModule($dir->getBasename(), $dir->getPathname() . '/', $reloadAll) === true){
					$modulesLoaded = true;
				}
			}
		}
		return $modulesLoaded;
	}

	/**
	 * @static
	 * @param $moduleName
	 * @param bool $ignoreStatus
	 * @return bool|ModuleBase
	 */
	public static function getModule($moduleName, $ignoreStatus = false) {
		$Module = false;
		if (self::isLoaded($moduleName) === true){
			if (isset(static::$alwaysLoadFresh) && static::$alwaysLoadFresh === true){
				if (self::loadModule($moduleName) === true){
					$Module = self::$Modules[static::$classPrefix][$moduleName];
				}
			}else{
				$Module = self::$Modules[static::$classPrefix][$moduleName];
			}
		}else{
			if (self::loadModule($moduleName) === true){
				$Module = self::$Modules[static::$classPrefix][$moduleName];
			}
		}

		if ($ignoreStatus === false){
			if (is_object($Module) === false){
				return false;
			}
			if ($Module->isEnabled() === false){
				//echo '<pre>';var_dump($Module);
				$Module = false;
			}
		}
		return $Module;
	}
}

?>