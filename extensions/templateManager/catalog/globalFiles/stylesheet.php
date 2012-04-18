<?php
chdir('../../../../');
if (isset($_GET['layout_id'])){
	$env = 'catalog';
	$layoutId = $_GET['layout_id'];
	$templateDir = isset($_GET['tplDir']) ? $_GET['tplDir'] : '';
}
else {
	$env = 'admin';
	$layoutId = '9999';
	$templateDir = 'fallback';
}
$import = 'noimport';
if (isset($_GET['import']) && !empty($_GET['import'])){
	$import = $_GET['import'];
}
$cacheKey = $templateDir . '-' . md5($_SERVER['HTTP_USER_AGENT'] . '-' . $layoutId . '-' . $import);
$noCache = isset($_GET['noCache']);
$noMin = isset($_GET['noMin']);

require('includes/classes/system_cache.php');
$StylesheetCache = new SystemCache($cacheKey, 'cache/' . $env . '/stylesheet/');
if ($noCache === false && $StylesheetCache->loadData() === true){
	$StylesheetCache->output(false, true);
	exit;
}
else {
	include('includes/application_top.php');
	require(sysConfig::getDirFsCatalog() . 'ext/cssMin/CssMin.php');

	$sourceInfo = '';
	function getSourceFile($fileName, $filePath){
		global $sourceInfo;
		$cacheFileName = md5($filePath . $fileName);
		$sourceInfo .= '/**' . "\n";
		$sourceInfo .= 'Real Filename: ' . $filePath . $fileName . "\n";
		$sourceInfo .= 'Cache Filename: ' . sysConfig::getDirFsCatalog() . 'cache/preminified/' . $cacheFileName . '.cache' . "\n";
		$sourceInfo .= filemtime($filePath . $fileName) . ' > ' . filemtime(sysConfig::getDirFsCatalog() . 'cache/preminified/' . $cacheFileName . '.cache') . ' = ' . (int)(filemtime($filePath . $fileName) > filemtime(sysConfig::getDirFsCatalog() . 'cache/preminified/' . $cacheFileName . '.cache')) . "\n";
		$sourceInfo .= '*/' . "\n\n";
		if (
			file_exists(sysConfig::getDirFsCatalog() . 'cache/preminified/' . $cacheFileName . '.cache') === false ||
			(filemtime($filePath . $fileName) > filemtime(sysConfig::getDirFsCatalog() . 'cache/preminified/' . $cacheFileName . '.cache'))
		){
			$minifiedCss = CssMin::minify(file_get_contents($filePath . $fileName));

			$cachedFile = new SystemCache($cacheFileName, 'cache/preminified/');
			$cachedFile->setContent($minifiedCss);
			$cachedFile->store(false);
		}
		return sysConfig::getDirFsCatalog() . 'cache/preminified/' . $cacheFileName . '.cache';
	}

	$themeFolder = sysConfig::getDirFsCatalog() . 'ext/jQuery/themes/smoothness/';

	$sources = array(
		getSourceFile('jquery.ui.core.css', $themeFolder),
		getSourceFile('jquery.ui.theme.css', $themeFolder),
		getSourceFile('jquery.ui.accordion.css', $themeFolder),
		getSourceFile('jquery.ui.datepicker.css', $themeFolder),
		getSourceFile('jquery.ui.dialog.css', $themeFolder),
		getSourceFile('jquery.ui.progressbar.css', $themeFolder),
		getSourceFile('jquery.ui.resizable.css', $themeFolder),
		getSourceFile('jquery.ui.slider.css', $themeFolder),
		getSourceFile('jquery.ui.tabs.css', $themeFolder),
		getSourceFile('jquery.ui.tooltip.css', $themeFolder),
		getSourceFile('jquery.ui.autocomplete.css', $themeFolder),
		getSourceFile('jquery.ui.button.css', $themeFolder),
		getSourceFile('jquery.ui.stars.css', $themeFolder),
		getSourceFile('ses_core.css', sysConfig::getDirFsCatalog() . 'extensions/templateManager/mainFiles/')
	);

	if ($App->getEnv() == 'admin'){
		$sources[] = getSourceFile('jquery.filemanager.css', sysConfig::getDirFsCatalog() . 'ext/jQuery/external/filemanager/');
		$sources[] = getSourceFile('jquery.fileuploader.css', sysConfig::getDirFsCatalog() . 'ext/jQuery/external/fileuploader/');
	}

	if (isset($_GET['import']) && !empty($_GET['import'])){
		foreach(explode(',', $_GET['import']) as $filePath){
			if (substr($filePath, -4) != '.css'){
				continue;
			}

			if (file_exists($filePath)){
				$sources[] = $filePath;
			}
			elseif (file_exists(sysConfig::get('DIR_FS_DOCUMENT_ROOT') . $filePath)) {
				$sources[] = sysConfig::get('DIR_FS_DOCUMENT_ROOT') . $filePath;
			}
			elseif (file_exists(sysConfig::getDirFsCatalog() . $filePath)) {
				$sources[] = sysConfig::getDirFsCatalog() . $filePath;
			}
			elseif (file_exists(sysConfig::getDirFsAdmin() . $filePath)) {
				$sources[] = sysConfig::getDirFsAdmin() . $filePath;
			}
		}
	}

	ob_start();
	foreach($sources as $filePath){
		if (file_exists($filePath)){
			echo '/*' . "\n" .
				' * Required File' . "\n" .
				' * Path: ' . $filePath . "\n" .
				' * --BEGIN--' . "\n" .
				' */' . "\n";
			require($filePath);
			echo '/*' . "\n" .
				' * Required File' . "\n" .
				' * Path: ' . $filePath . "\n" .
				' * --END--' . "\n" .
				' */' . "\n";
		}
	}
	$preMinified = ob_get_contents();
	ob_end_clean();

	ob_start();
	if ($env == 'catalog'){
		$TemplateManager = $appExtension->getExtension('templateManager');
		$TemplateManager->loadWidgets($templateDir);
		$boxStylesEntered = array();
		$infoBoxSources = array();
		$boxStylesheetSourcesEntered = array();
		$addCss = '';

		function getElementId($dataArr) {
			if (isset($dataArr['widget_id'])){
				$idCol = 'widget_id';
				$idVal = $dataArr['widget_id'];
				$configTable = 'template_manager_layouts_widgets_configuration';
			}
			elseif (isset($dataArr['column_id'])) {
				$idCol = 'column_id';
				$idVal = $dataArr['column_id'];
				$configTable = 'template_manager_layouts_columns_configuration';
			}
			elseif (isset($dataArr['container_id'])) {
				$idCol = 'container_id';
				$idVal = $dataArr['container_id'];
				$configTable = 'template_manager_layouts_containers_configuration';
			}

			if (!isset($idCol)){
				print_r($dataArr);
			}
			$QconfigId = Doctrine_Manager::getInstance()
				->getCurrentConnection()
				->fetchAssoc('select configuration_value from ' . $configTable . ' where configuration_key = "id" and ' . $idCol . ' = "' . $idVal . '"');
			return $QconfigId[0]['configuration_value'];
		}

		function parseContainer($Container) {
			global $TemplateManager, $boxStylesEntered, $infoBoxSources, $boxStylesheetSourcesEntered, $addCss;

			if (isset($Container['widget_id'])){
				$typeId = $Container['widget_id'];
				$type = 'widget';
			}
			elseif (isset($Container['column_id'])) {
				$typeId = $Container['column_id'];
				$type = 'column';
			}
			elseif (isset($Container['container_id'])) {
				$typeId = $Container['container_id'];
				$type = 'container';
			}

			if (($ElementId = getElementId($Container)) != ''){
				if (($Styles = $TemplateManager->getStyleInfo($type, $typeId)) !== false){
					$Style = new StyleBuilder();
					$Style->setSelector('#' . $ElementId);
					foreach($Styles as $sInfo){
						$Style->addRule($sInfo['definition_key'], $sInfo['definition_value']);
					}
					$addCss .= $Style->outputCss();
				}
			}

			if ($type == 'container' && (($Containers = $TemplateManager->getContainerChildren($typeId)) !== false)){
				foreach($Containers as $ChildObj){
					parseContainer($ChildObj);
				}
			}
			elseif ($type == 'container' && (($Columns = $TemplateManager->getContainerColumns($typeId)) !== false)) {
				foreach($Columns as $ChildObj){
					parseContainer($ChildObj);
				}
			}
			elseif ($type == 'column' && (($Columns = $TemplateManager->getColumnChildren($typeId)) !== false)) {
				foreach($Columns as $ChildObj){
					parseContainer($ChildObj);
				}
			}
			elseif ($type == 'column' && (($Widgets = $TemplateManager->getColumnWidgets($typeId)) !== false)) {
				foreach($Widgets as $wInfo){
					parseContainer($wInfo);
				}
			}
			elseif ($type == 'widget') {
				if (($Configuration = $TemplateManager->getConfigInfo($type, $typeId)) !== false){
					foreach($Configuration as $config){
						if ($config['configuration_key'] == 'widget_settings'){
							$WidgetSettings = json_decode($config['configuration_value']);
							break;
						}
					}

					if (($Styles = $TemplateManager->getStyleInfo($type, $typeId)) !== false){
						$Style = new StyleBuilder();
						$Style->setSelector('#widget_' . $typeId);
						foreach($Styles as $sInfo){
							$Style->addRule($sInfo['definition_key'], $sInfo['definition_value']);
						}
						$addCss .= $Style->outputCss();
					}

					$WidgetClass = $TemplateManager->getWidget($Container['identifier']);
					if ($WidgetClass !== false){
						if (isset($WidgetSettings->id) && !empty($WidgetSettings->id)){
							$WidgetClass->setBoxId($WidgetSettings->id);
						}
						$WidgetClass->setWidgetProperties($WidgetSettings);
						if (method_exists($WidgetClass, 'buildStylesheet')){
							if ($WidgetClass->buildStylesheetMultiple === true || !in_array($WidgetClass->getBoxCode(), $boxStylesEntered)){
								echo $WidgetClass->buildStylesheet();

								$boxStylesEntered[] = $WidgetClass->getBoxCode();
							}
						}
						if (method_exists($WidgetClass, 'getStylesheetSources')){
							if (!in_array($WidgetClass->getBoxCode(), $boxStylesheetSourcesEntered)){
								$infoBoxCssFiles = $WidgetClass->getStylesheetSources();
								foreach($infoBoxCssFiles as $infoBoxCssFile){
									if (file_exists($infoBoxCssFile)){
										$infoBoxSources[] = $infoBoxCssFile;
									}
								}

								$boxStylesheetSourcesEntered[] = $WidgetClass->getBoxCode();
							}
						}
					}
				}
			}
		}

		$Layout = Doctrine_Manager::getInstance()
			->getCurrentConnection()
			->fetchAssoc('select * from template_manager_layouts where layout_id = "' . (int)$_GET['layout_id'] . '"');
		if ($Layout){
			if (($LayoutStyles = $TemplateManager->getStyleInfo('layout', $Layout[0]['layout_id'])) !== false){
				$StyleBuilder = new StyleBuilder();
				$StyleBuilder->setSelector('body');
				$rules = array();
				foreach($LayoutStyles as $sInfo){
					$StyleBuilder->addRule($sInfo['definition_key'], $sInfo['definition_value']);
				}
				$addCss .= $StyleBuilder->outputCss();
			}

			$Containers = Doctrine_Manager::getInstance()
				->getCurrentConnection()
				->fetchAssoc('select * from template_manager_layouts_containers where layout_id = "' . $Layout[0]['layout_id'] . '" and parent_id = 0 order by sort_order');
			if (sizeof($Containers) > 0){
				foreach($Containers as $cInfo){
					if ($cInfo['link_id'] > 0){
						$Link = Doctrine_Manager::getInstance()
							->getCurrentConnection()
							->fetchAssoc('select c.* from template_manager_container_links l left join template_manager_layouts_containers c using(container_id) where l.link_id = "' . $cInfo['link_id'] . '"');
						parseContainer($Link[0]);
					}
					else {
						parseContainer($cInfo);
					}
				}
			}

			foreach($infoBoxSources as $filePath){
				if (file_exists($filePath)){
					echo '/*' . "\n" .
						' * Template Widget Required File' . "\n" .
						' * Path: ' . $filePath . "\n" .
						' * --BEGIN--' . "\n" .
						' */' . "\n";
					require($filePath);
					echo '/*' . "\n" .
						' * Template Widget Required File' . "\n" .
						' * Path: ' . $filePath . "\n" .
						' * --END--' . "\n" .
						' */' . "\n";
				}
			}

			echo '/*' . "\n" .
				' * Layout Manager Generated Styles' . "\n" .
				' * --BEGIN--' . "\n" .
				' */' . "\n";
			echo $addCss;
			echo '/*' . "\n" .
				' * Layout Manager Generated Styles' . "\n" .
				' * --END--' . "\n" .
				' */' . "\n";
		}

		echo '/*' . "\n" .
			' * Template Stylesheet' . "\n" .
			' * Path: ' . sysConfig::get('DIR_FS_TEMPLATE') . 'stylesheet.css' . "\n" .
			' * --BEGIN--' . "\n" .
			' */' . "\n";
		require(sysConfig::get('DIR_FS_TEMPLATE') . 'stylesheet.css');
		echo '/*' . "\n" .
			' * Template Stylesheet' . "\n" .
			' * Path: ' . sysConfig::get('DIR_FS_TEMPLATE') . 'stylesheet.css' . "\n" .
			' * --END--' . "\n" .
			' */' . "\n";
	}
	else {
		echo '/*' . "\n" .
			' * Template Stylesheet' . "\n" .
			' * Path: ' . sysConfig::get('DIR_FS_TEMPLATE') . 'stylesheet.css' . "\n" .
			' * --BEGIN--' . "\n" .
			' */' . "\n";
		require(sysConfig::get('DIR_FS_TEMPLATE') . 'stylesheet.css');
		echo '/*' . "\n" .
			' * Template Stylesheet' . "\n" .
			' * Path: ' . sysConfig::get('DIR_FS_TEMPLATE') . 'stylesheet.css' . "\n" .
			' * --END--' . "\n" .
			' */' . "\n";
	}

	$fileContent = ob_get_contents();
	ob_end_clean();

	function src1_fetch() {
		global $fileContent;
		return $fileContent;
	}

	$nowTime = time();
	$maxAge = (60 * 60 * 24 * 2);
	$expiresTime = $nowTime + $maxAge;

	if ($noMin === true || sysConfig::get('TEMPLATE_STYLESHEET_COMPRESSION') == 'none'){
		$Result = array(
			'headers' => array(
				'Content-Type'	 => 'text/css'
			),
			'content' => src1_fetch()
		);
		if ($noCache === false && sysConfig::get('TEMPLATE_STYLESHEET_CACHE') == 1){
			$Result['headers']['Expires'] = gmdate('D, d M Y H:i:s \G\M\T', $expiresTime);
			$Result['headers']['Last-Modified'] = gmdate('D, d M Y H:i:s \G\M\T', $nowTime);
			$Result['headers']['Cache-Control'] = 'max-age=' . $maxAge;
		}
		else {
			$Result['headers']['Expires'] = 'Sat, 26 Jul 1997 05:00:00 GMT';
			$Result['headers']['Last-Modified'] = gmdate('D, d M Y H:i:s \G\M\T', $nowTime);
			$Result['headers']['Cache-Control'] = 'no-cache, must-revalidate';
		}
	}
	else {
		define('MINIFY_MIN_DIR', sysConfig::getDirFsCatalog() . 'min');

		/*
		 * This script implements a Minify server for a single set of sources.
		 * If you don't want '.php' in the URL, use mod_rewrite...
		 */

		// setup Minify
		set_include_path(MINIFY_MIN_DIR . '/lib' . PATH_SEPARATOR . get_include_path());
		require 'Minify.php';
		require 'Minify/Cache/File.php';
		Minify::setCache(new Minify_Cache_File()); // guesses a temp directory

		// setup sources
		$sources = new Minify_Source(array(
			'id'              => 'source1',
			'getContentFunc'  => 'src1_fetch',
			'contentType'	 => Minify::TYPE_CSS,
			'lastModified'    => $nowTime
		));

		// handle request
		$serveArr = array(
			'files'              => $sources,
			'maxAge'             => $maxAge,
			'quiet'              => true,
			'debug'              => true,
			'encodeMethod'       => '',
			'contentTypeCharset' => false
		);

		switch(sysConfig::get('TEMPLATE_STYLESHEET_COMPRESSION')){
			case 'gzip':
				//ob_start("ob_gzhandler");
				break;
			case 'min':
				$serveArr['debug'] = false;
				break;
			case 'min_gzip':
				//ob_start("ob_gzhandler");
				$serveArr['debug'] = false;
				break;
		}
		$Result = Minify::serve('Files', $serveArr);
	}

	$StylesheetCache->setContent($sourceInfo . $preMinified . $Result['content']);
	$StylesheetCache->setAddedHeaders($Result['headers']);
	if ($noCache === false && sysConfig::get('TEMPLATE_STYLESHEET_CACHE') == 1){
		$StylesheetCache->store();
	}

	$StylesheetCache->output(false, true);

	include('includes/application_bottom.php');
}
