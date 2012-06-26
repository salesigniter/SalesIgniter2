<?php
/**
 * Sales Igniter E-Commerce System
 * Version: {ses_version}
 *
 * I.T. Web Experts
 * http://www.itwebexperts.com
 *
 * Copyright (c) {ses_copyright} I.T. Web Experts
 *
 * This script and its source are not distributable without the written consent of I.T. Web Experts
 */

class Application
{

	private $env;

	private $envDir;

	private $appName;

	private $appPage;

	private $appLocation = false;

	private $appDir = array();

	private $addedStylesheetFiles = array();

	private $addedJavascriptFiles = array();

	private $addedRawJavascript = array();

	private $applicationsArr = array();

	public function __construct() {
		$this->env = APPLICATION_ENVIRONMENT;
	}

	public function loadApplication($appName, $appPage) {
		global $appExtension;
		$this->appName = $appName;
		$this->appPage = $appPage;
		$this->infoBoxId = null;

		$appExtension->onLoadApplication($this);

		$this->appLocation = false;
		foreach($this->getAppOverrideDirs() as $Path){
			if (file_exists($Path . 'app.php')){
				$this->appLocation = $Path;
				break;
			}
		}

		if ($this->appLocation === false){
			$StandAlone = Doctrine_Query::create()
				->from('TemplateManagerLayouts')
				->where('page_type = ?', 'page')
				->andWhere('layout_settings LIKE ?', '%\"appName\":\"' . $this->appName . '\"%')
				->andWhere('layout_settings LIKE ?', '%\"appPageName\":\"' . $this->appPage . '\"%')
				->execute();
			if ($StandAlone && $StandAlone->count() == 1){
				$this->appLocation = 'virtual';
				$this->appDir = 'virtual';
			}
		}else{
			$this->appDir = array(
				'relative' => $this->getAppLocation('relative'),
				'absolute' => $this->getAppLocation()
			);
		}
	}

	public function getAppOverrideDirs(){
		$Paths = array();
		/**
		 * First: Check for client data overrides
		 */
		$Paths[] = sysConfig::getDirFsCatalog() . 'clientData/' . $this->env . '/applications/' . $this->appName . '/';
		if (isset($_GET['appExt'])){
			$Paths[] = sysConfig::getDirFsCatalog() . 'clientData/extensions/' . $_GET['appExt'] . '/' . $this->env . '/base_app/' . $this->appName . '/';
		}

		/**
		 * Second: Check for template overrides
		 */
		$Paths[] = sysConfig::get('DIR_FS_TEMPLATE') . $this->env . '/applications/' . $this->appName . '/';
		if (isset($_GET['appExt'])){
			$Paths[] = sysConfig::get('DIR_FS_TEMPLATE') . 'extensions/' . $_GET['appExt'] . '/' . $this->env . '/base_app/' . $this->appName . '/';
		}

		/**
		 * Third: Check for extension files
		 */
		if (isset($_GET['appExt'])){
			$Paths[] = sysConfig::getDirFsCatalog() . 'extensions/' . $_GET['appExt'] . '/' . $this->env . '/base_app/' . $this->appName . '/';
		}

		/**
		 * Fourth: Check for core files
		 */
		if ($this->env == 'admin'){
			$Paths[] = sysConfig::getDirFsAdmin() . 'applications/' . $this->appName . '/';
		}else{
			$Paths[] = sysConfig::getDirFsCatalog() . 'applications/' . $this->appName . '/';
		}
		return $Paths;
	}

	public function isValid() {
		$return = true;
		if (file_exists($_SERVER['SCRIPT_FILENAME'])){
			return true;
		}
		if (in_array(basename(strtolower($_SERVER['PHP_SELF'])), array('runupdate.php', 'stylesheet.php', 'javascript.php'))){
			return true;
		}
		if ($this->appLocation === false){
			$return = false;
		}
		if ($this->getAppContentFile() === false){
			$return = false;
		}
		return $return;
	}

	public function isIgnoredApp(){
		$ignored = false;
		if ($this->appLocation == 'virtual'){
			$ignored = true;
		}
		if (in_array(strtolower(basename($_SERVER['PHP_SELF'])), array('runupdate', 'stylesheet.php', 'javascript.php'))){
			$ignored = true;
		}
		return $ignored;
	}

	public function getAppPage() {
		return $this->appPage;
	}

	/* To replace function above */

	public function getPageName() {
		return $this->appPage;
	}

	public function setAppPage($pageName) {
		$this->appPage = $pageName;
	}

	public function getAppName() {
		return $this->appName;
	}

	public function getAppLocation($type = 'absolute') {
		if ($type == 'relative'){
			if ($this->env == 'admin'){
				return str_replace(array(sysConfig::getDirFsAdmin(), sysConfig::getDirFsCatalog()), array(sysConfig::getDirWsAdmin(), sysConfig::getDirWsCatalog()), $this->appLocation);
			}
			else {
				return str_replace(array(sysConfig::getDirFsAdmin(), sysConfig::getDirFsCatalog()), '', $this->appLocation);
			}
		}
		else {
			return $this->appLocation;
		}
	}

	public function getAppFile() {
		if ($this->isIgnoredApp()){
			return '';
		}
		return $this->getAppLocation() . 'app.php';
	}

	public function getEnv() {
		return $this->env;
	}

	public function getAppContentFile($useFile = false) {
		if ($this->isIgnoredApp()){
			return false;
		}

		if ($useFile === false){
			$useFile = $this->getAppPage() . '.php';
		}

		$requireFile = false;
		foreach($this->getAppOverrideDirs() as $Dir){
			if (file_exists($Dir . 'pages/' . $useFile)){
				$requireFile = $Dir . 'pages/' . $useFile;
				break;
			}
		}

		return (file_exists($requireFile) ? $requireFile : false);
	}

	public function loadLanguageDefines() {
		global $appExtension;
		if ($this->isIgnoredApp()){
			return '';
		}

		/*
		 * Load application fallback file
		 */
		$languageFiles = array(
			$this->appDir['absolute'] . 'language_defines/global.xml'
		);

		if (file_exists($this->appDir['absolute'] . 'language_defines/' . $this->getAppPage() . '.xml')){
			$languageFiles[] = $this->appDir['absolute'] . 'language_defines/' . $this->getAppPage() . '.xml';
		}

		/*
		 * Load extension files for application
		 */
		$appExtension->getLanguageFiles(array(
			'env'     => $this->env,
			'appName' => $this->getAppName()
		), $languageFiles);

		/*
		 * Application definitions overwrite file path
		 */
		if (file_exists(sysConfig::getDirFsCatalog() . 'includes/languages/' . Session::get('language') . '/' . $this->env . '/applications/' . $this->getAppPage() . '/global.xml')){
			$languageFiles[] = sysConfig::getDirFsCatalog() . 'includes/languages/' . Session::get('language') . '/' . $this->env . '/applications/' . $this->getAppPage() . '/global.xml';
		}

		/*
		 * Application extension definitions overwrite file path
		 */
		$appExtension->getOverwriteLanguageFiles(array(
			'env'     => $this->env,
			'appName' => $this->getAppName()
		), $languageFiles);

		/*
		 * Load all definition files and overwrite definitions
		 */
		foreach($languageFiles as $filePath){
			sysLanguage::loadDefinitions($filePath);
		}
	}

	public function getAppBaseJsFiles() {
		global $appExtension;
		if ($this->isIgnoredApp()){
			return '';
		}

		$javascriptFiles = array();

		$appExtension->getGlobalFiles('javascript', array(
			'env'    => $this->env,
			'format' => 'relative'
		), $javascriptFiles);

		$pageJsFile = $this->getAppPage() . '.js';
		foreach($this->getAppOverrideDirs() as $Path){
			if (file_exists($Path . 'javascript/' . $pageJsFile)){
				$javascriptFiles[] = $Path . 'javascript/' . $pageJsFile;
				break;
			}
		}

		$appExtension->getAppFiles('javascript', array(
			'env'     => $this->env,
			'appName' => $this->getAppName(),
			'appFile' => $pageJsFile,
			'format'  => 'relative'
		), $javascriptFiles);

		if (!empty($this->addedJavascriptFiles)){
			foreach($this->addedJavascriptFiles as $file){
				if (substr($file, 0, 7) != 'http://'){
					$javascriptFiles[] = sysConfig::getDirWsCatalog() . $file;
				}
				else {
					$javascriptFiles[] = $file;
				}
			}
		}

		return $javascriptFiles;
	}

	public function getAppBaseStylesheetFiles() {
		global $appExtension;
		if ($this->isIgnoredApp()){
			return '';
		}

		$stylesheetFiles = array();

		$appExtension->getGlobalFiles('stylesheets', array(
			'env'    => $this->env,
			'format' => 'relative'
		), $stylesheetFiles);

		$pageCssFile = $this->getAppPage() . '.css';
		foreach($this->getAppOverrideDirs() as $Path){
			if (file_exists($Path . 'stylesheets/' . $pageCssFile)){
				$stylesheetFiles[] = $Path . 'stylesheets/' . $pageCssFile;
				break;
			}
		}

		$appExtension->getAppFiles('stylesheets', array(
			'env'     => $this->env,
			'appName' => $this->getAppName(),
			'appFile' => $pageCssFile,
			'format'  => 'relative'
		), $stylesheetFiles);

		if (!empty($this->addedStylesheetFiles)){
			foreach($this->addedStylesheetFiles as $file){
				$stylesheetFiles[] = sysConfig::getDirWsCatalog() . $file;
			}
		}

		return $stylesheetFiles;
	}

	function addJavascriptFile($file) {
		$this->addedJavascriptFiles[] = $file;
	}

	function hasJavascriptFiles() {
		$files = $this->getAppBaseJsFiles();
		return (!empty($files));
	}

	function getJavascriptFiles() {
		$files = $this->getAppBaseJsFiles();
		return $files;
	}

	function addStylesheetFile($file) {
		$this->addedStylesheetFiles[] = $file;
	}

	function hasStylesheetFiles() {
		$files = $this->getAppBaseStylesheetFiles();
		return (!empty($files));
	}

	function getStylesheetFiles() {
		$files = $this->getAppBaseStylesheetFiles();
		return $files;
	}

	public function getActionFiles($action) {
		global $appExtension;
		if ($this->isIgnoredApp()){
			return '';
		}

		$useFile = $action . '.php';
		$actionFiles = array();
		
		foreach($this->getAppOverrideDirs() as $Path){
			if (file_exists($Path . 'actions/' . $useFile)){
				$actionFiles[] = $Path . 'actions/' . $useFile;
				break;
			}
		}

		$appExtension->getAppFiles('actions', array(
			'env'     => $this->env,
			'appName' => $this->getAppName(),
			'appFile' => $action . '.php'
		), $actionFiles);

		return $actionFiles;
	}

	public function getFunctionFiles() {
		global $appExtension;
		if ($this->isIgnoredApp()){
			return array();
		}

		$functionFiles = array();
		$pageFunctionFile = $this->getAppPage() . '.php';

		foreach($this->getAppOverrideDirs() as $Path){
			if (file_exists($Path . 'pages_functions/' . $pageFunctionFile)){
				$functionFiles[] = $Path . 'pages_functions/' . $pageFunctionFile;
				break;
			}
		}

		$appExtension->getAppFiles('pages_functions', array(
			'env'     => $this->env,
			'appName' => $this->getAppName(),
			'appFile' => $pageFunctionFile
		), $functionFiles);

		return $functionFiles;
	}

	public function hasAddedJavascript($id) {
		return isset($this->addedRawJavascript[$id]);
	}

	public function addJavascript($id, $js) {
		$this->addedRawJavascript[$id] = $js;
	}

	public function appendAddedJavascript($id, $js) {
		$this->addedRawJavascript[$id] .= $js;
	}

	public function getAddedJavascript() {
		$source = '';
		foreach($this->addedRawJavascript as $id => $src){
			$source .= $src . "\n";
		}
		return $source;
	}

	private function addCategoriesToAppArray($selApps, &$AppArray, $parentId = 0){
		$Qcategories = Doctrine_Query::create()
			->from('Categories c')
			->leftJoin('c.CategoriesDescription cd')
			->where('parent_id = ?', $parentId)
			->andWhere('language_id = ?', Session::get('languages_id'))
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
		$appName = 'index';
		foreach($Qcategories as $category){
			$pageName = $category['CategoriesDescription'][0]['categories_name'];
			if (!empty($pageName)){
				$AppArray[$appName][$pageName] = (isset($selApps[$appName][$pageName]) ? $selApps[$appName][$pageName] : false);
			}
			$this->addCategoriesToAppArray($selApps, &$AppArray, $category['categories_id']);
		}
	}

	public function addProductsToAppArray($selApps, &$AppArray){
		$QProducts = Doctrine_Query::create()
			->from('Products p')
			->leftJoin('p.ProductsDescription pd')
			->where('pd.language_id = ?', Session::get('languages_id'));

		EventManager::notify('AdminProductListingTemplateQueryBeforeExecute', $QProducts);

		$QProducts = $QProducts->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

		$appName = 'product';
		foreach($QProducts as $prod){
			$pageName = $prod['ProductsDescription'][0]['products_name'];
			if (!empty($pageName)){
				$AppArray[$appName][$pageName] = (isset($selApps[$appName][$pageName]) ? $selApps[$appName][$pageName] : false);
			}
		}
	}

	public function getApplications($selApps = array(), $includeStandalone = true, $excluded = array()) {
		global $appExtension;

		if (empty($this->applicationsArr)){
			$Applications = new DirectoryIterator(sysConfig::getDirFsCatalog() . 'applications/');
			$AppArray = array();
			foreach($Applications as $AppDir){
				if ($AppDir->isDot() || $AppDir->isFile()){
					continue;
				}
				$appName = $AppDir->getBasename();

				$AppArray[$appName] = array();

				if (is_dir($AppDir->getPathname() . '/pages/')){
					$Pages = new DirectoryIterator($AppDir->getPathname() . '/pages/');
					foreach($Pages as $Page){
						if ($Page->isDot() || $Page->isDir()){
							continue;
						}
						$pageName = $Page->getBasename('.php');

						$AppArray[$appName][$pageName] = (isset($selApps[$appName][$pageName]) ? $selApps[$appName][$pageName] : false);
					}
				}

				if ($appName == 'index'){
					$this->addCategoriesToAppArray($selApps, &$AppArray, 0);
				}

				if ($appName == 'product' && isset($associativeUrl)){
					$this->addProductsToAppArray($selApps, &$AppArray);
				}

				ksort($AppArray[$appName]);
			}

			if ($includeStandalone === true){
				$StandAlone = Doctrine_Query::create()
					->from('TemplateManagerLayouts')
					->where('page_type = ?', 'page')
					->execute();
				foreach($StandAlone as $PageInfo){
					$layoutSettings = json_decode($PageInfo->layout_settings);
					$AppArray[$layoutSettings->appName][$layoutSettings->appPageName] = (isset($selApps[$layoutSettings->appName][$layoutSettings->appPageName]) ? $selApps[$layoutSettings->appName][$layoutSettings->appPageName] : false);
				}
			}

			$Extensions = new DirectoryIterator(sysConfig::getDirFsCatalog() . 'extensions/');
			foreach($Extensions as $Extension){
				if ($Extension->isDot() || $Extension->isFile()){
					continue;
				}

				$ExtCls = $appExtension->getExtension($Extension->getBasename());
				if ($ExtCls && $ExtCls->isEnabled() && is_dir($Extension->getPathName() . '/catalog/base_app/')){
					$extName = $Extension->getBasename();

					$AppArray['ext'][$extName] = array();

					$ExtApplications = new DirectoryIterator($Extension->getPathname() . '/catalog/base_app/');
					foreach($ExtApplications as $ExtApplication){
						if ($ExtApplication->isDot() || $ExtApplication->isFile()){
							continue;
						}
						$appName = $ExtApplication->getBasename();

						$AppArray['ext'][$extName][$appName] = array();

						if ($Extension->getBasename() == 'infoPages'){
							$Qpages = Doctrine_Query::create()
								->select('page_key')
								->from('Pages')
								->where('page_type = ?', 'page')
								->orderBy('page_key asc')
								->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
							if ($Qpages){
								foreach($Qpages as $pInfo){
									$pageName = $pInfo['page_key'];

									$AppArray['ext'][$extName][$appName][$pageName] = (isset($selApps['ext'][$extName][$appName][$pageName]) ? $selApps['ext'][$extName][$appName][$pageName] : false);
								}
							}
						}
						elseif ($Extension->getBasename() == 'categoriesPages') {
							$Qpages = Doctrine_Query::create()
								->select('page_key')
								->from('CategoriesPages')
								->orderBy('page_key asc')
								->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
							if ($Qpages){
								foreach($Qpages as $pInfo){
									$pageName = $pInfo['page_key'];

									$AppArray['ext'][$extName][$appName][$pageName] = (isset($selApps['ext'][$extName][$appName][$pageName]) ? $selApps['ext'][$extName][$appName][$pageName] : false);
								}
							}
						}
						elseif (is_dir($ExtApplication->getPathname() . '/pages/')) {
							$ExtPages = new DirectoryIterator($ExtApplication->getPathname() . '/pages/');
							foreach($ExtPages as $ExtPage){
								if ($ExtPage->isDot() || $ExtPage->isDir()){
									continue;
								}
								$pageName = $ExtPage->getBasename('.php');

								$AppArray['ext'][$extName][$appName][$pageName] = (isset($selApps['ext'][$extName][$appName][$pageName]) ? $selApps['ext'][$extName][$appName][$pageName] : false);
							}
						}
						ksort($AppArray['ext'][$extName][$appName]);
					}
					ksort($AppArray['ext']);
				}
			}

			$Extensions = new DirectoryIterator(sysConfig::getDirFsCatalog() . 'extensions/');
			foreach($Extensions as $Extension){
				if ($Extension->isDot() || $Extension->isFile()){
					continue;
				}

				if (is_dir($Extension->getPathName() . '/catalog/ext_app/')){
					$ExtCheck = new DirectoryIterator($Extension->getPathname() . '/catalog/ext_app/');
					foreach($ExtCheck as $eInfo){
						if ($eInfo->isDot() || $eInfo->isFile()){
							continue;
						}

						if (is_dir($eInfo->getPathName() . '/pages')){
							$appName = $eInfo->getBasename();

							$Pages = new DirectoryIterator($eInfo->getPathname() . '/pages/');
							foreach($Pages as $Page){
								if ($Page->isDot() || $Page->isDir()){
									continue;
								}
								$pageName = $Page->getBasename('.php');

								if (!isset($AppArray[$appName][$pageName])){
									$AppArray[$appName][$pageName] = (isset($selApps[$appName][$pageName]) ? $selApps[$appName][$pageName] : false);
								}
							}
						}
						elseif (isset($AppArray['ext'][$eInfo->getBasename()])) {
							$Apps = new DirectoryIterator($eInfo->getPathName());
							$extName = $eInfo->getBasename();

							foreach($Apps as $App){
								if ($App->isDot() || $App->isFile()){
									continue;
								}
								$appName = $App->getBasename();

								if (is_dir($App->getPathname() . '/pages')){
									$Pages = new DirectoryIterator($App->getPathname() . '/pages/');
									foreach($Pages as $Page){
										if ($Page->isDot() || $Page->isDir()){
											continue;
										}
										$pageName = $Page->getBasename('.php');

										if (!isset($AppArray['ext'][$extName][$App->getBasename()])){
											$AppArray['ext'][$extName][$App->getBasename()] = array();
										}

										$AppArray['ext'][$extName][$appName][$pageName] = (isset($selApps['ext'][$extName][$appName][$pageName]) ? $selApps['ext'][$extName][$appName][$pageName] : false);
									}
								}
							}
						}
					}
				}
			}

			$Dir = new DirectoryIterator(sysConfig::get('DIR_FS_CATALOG_TEMPLATES'));
			foreach($Dir as $dInfo){
				if ($dInfo->isDot() || $dInfo->isFile()){
					continue;
				}

				if (is_dir($dInfo->getPathname() . '/catalog/applications')){
					$TemplateApps = new DirectoryIterator($dInfo->getPathname() . '/catalog/applications');
					foreach($TemplateApps as $Application){
						if ($Application->isDot() || $Application->isFile()){
							continue;
						}

						$appName = $Application->getBasename();
						if (!isset($AppArray[$appName])){
							$AppArray[$appName] = array();
						}

						if (is_dir($Application->getPathname() . '/pages/')){
							$Pages = new DirectoryIterator($Application->getPathname() . '/pages/');
							foreach($Pages as $Page){
								if ($Page->isDot() || $Page->isDir()){
									continue;
								}
								$pageName = $Page->getBasename('.php');

								$AppArray[$appName][$pageName] = (isset($selApps[$appName][$pageName]) ? $selApps[$appName][$pageName] : false);
							}
						}
						ksort($AppArray[$appName]);
					}
				}
			}
			ksort($AppArray);
			$this->applicationsArr = $AppArray;
		}
		return $this->applicationsArr;
	}
}

?>
