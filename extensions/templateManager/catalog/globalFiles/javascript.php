<?php
chdir('../../../../');

if (isset($_GET['env'])){
	$env = $_GET['env'];
	$layoutId = (isset($_GET['layout_id']) ? $_GET['layout_id'] : '9999');
	$templateDir = $_GET['tplDir'];
}
elseif (isset($_GET['layout_id'])) {
	$env = 'catalog';
	$layoutId = $_GET['layout_id'];
	$templateDir = isset($_GET['tplDir']) ? $_GET['tplDir'] : '';
}
else {
	$env = 'admin';
	$layoutId = '9999';
	$templateDir = 'administration';
}
$import = '';
if (isset($_GET['import']) && !empty($_GET['import'])){
	$import = $_GET['import'];
}
$cacheKey = $templateDir . '-' . md5($_SERVER['HTTP_USER_AGENT'] . '-' . $layoutId . '-' . $import);
$noCache = isset($_GET['noCache']);
$noMin = isset($_GET['noMin']);

require('includes/classes/system_cache.php');
$JavascriptCache = new SystemCache($cacheKey, 'cache/' . $env . '/javascript/');
if ($noCache === false && $JavascriptCache->loadData() === true){
	$JavascriptCache->output(false, true);
	exit;
}
else {
	include('includes/application_top.php');

	$sources = array(
		sysConfig::getDirFsCatalog() . 'ext/jQuery/jQuery-min.js',
		sysConfig::getDirFsCatalog() . 'ext/jQuery/ui/minified/jquery.ui.core.min.js',
		sysConfig::getDirFsCatalog() . 'ext/jQuery/ui/minified/jquery.ui.widget.min.js',
		//sysConfig::getDirFsCatalog() . 'ext/jQuery/ui/minified/jquery.ui.mouse.min.js',
		sysConfig::getDirFsCatalog() . 'ext/jQuery/ui/jquery.ui.mouse.js',
		sysConfig::getDirFsCatalog() . 'ext/jQuery/ui/minified/jquery.ui.position.min.js',
		sysConfig::getDirFsCatalog() . 'ext/jQuery/ui/minified/jquery.ui.draggable.min.js',
		sysConfig::getDirFsCatalog() . 'ext/jQuery/ui/minified/jquery.ui.droppable.min.js',
		sysConfig::getDirFsCatalog() . 'ext/jQuery/ui/minified/jquery.ui.sortable.min.js',
		sysConfig::getDirFsCatalog() . 'ext/jQuery/ui/minified/jquery.ui.resizable.min.js',
		sysConfig::getDirFsCatalog() . 'ext/jQuery/ui/minified/jquery.ui.tabs.min.js',
		sysConfig::getDirFsCatalog() . 'ext/jQuery/ui/jquery.ui.button.js',
		sysConfig::getDirFsCatalog() . 'ext/jQuery/ui/jquery.ui.dialog.js',
		sysConfig::getDirFsCatalog() . 'ext/jQuery/ui/jquery.ui.datepicker.js',
		sysConfig::getDirFsCatalog() . 'ext/jQuery/ui/jquery.ui.accordion.js',
		sysConfig::getDirFsCatalog() . 'ext/jQuery/ui/jquery.ui.stars.js',
		sysConfig::getDirFsCatalog() . 'ext/jQuery/ui/jquery.ui.progressbar.js',
		sysConfig::getDirFsCatalog() . 'ext/jQuery/ui/jquery.ui.newGrid.js',
		sysConfig::getDirFsCatalog() . 'ext/jQuery/ui/minified/jquery.effects.core.min.js',
		sysConfig::getDirFsCatalog() . 'ext/jQuery/ui/minified/jquery.effects.blind.min.js',
		sysConfig::getDirFsCatalog() . 'ext/jQuery/ui/minified/jquery.effects.bounce.min.js',
		sysConfig::getDirFsCatalog() . 'ext/jQuery/ui/minified/jquery.effects.clip.min.js',
		sysConfig::getDirFsCatalog() . 'ext/jQuery/ui/minified/jquery.effects.core.min.js',
		sysConfig::getDirFsCatalog() . 'ext/jQuery/ui/minified/jquery.effects.drop.min.js',
		sysConfig::getDirFsCatalog() . 'ext/jQuery/ui/minified/jquery.effects.explode.min.js',
		sysConfig::getDirFsCatalog() . 'ext/jQuery/ui/minified/jquery.effects.fade.min.js',
		sysConfig::getDirFsCatalog() . 'ext/jQuery/ui/minified/jquery.effects.fold.min.js',
		sysConfig::getDirFsCatalog() . 'ext/jQuery/ui/minified/jquery.effects.highlight.min.js',
		sysConfig::getDirFsCatalog() . 'ext/jQuery/ui/minified/jquery.effects.pulsate.min.js',
		sysConfig::getDirFsCatalog() . 'ext/jQuery/ui/minified/jquery.effects.scale.min.js',
		sysConfig::getDirFsCatalog() . 'ext/jQuery/ui/minified/jquery.effects.shake.min.js',
		sysConfig::getDirFsCatalog() . 'ext/jQuery/ui/minified/jquery.effects.slide.min.js',
		sysConfig::getDirFsCatalog() . 'ext/jQuery/ui/minified/jquery.effects.transfer.min.js'
	);

	if ($env == 'admin'){
		$sources[] = sysConfig::getDirFsCatalog() . 'ext/jQuery/external/filemanager/jquery.filemanager.js';
		$sources[] = sysConfig::getDirFsCatalog() . 'ext/jQuery/external/fileuploader/jquery.fileuploader.js';
		$sources[] = sysConfig::getDirFsAdmin() . 'includes/javascript/main.js';
		$sources[] = sysConfig::getDirFsAdmin() . 'includes/general.js';
	}
	else {
		$sources[] = sysConfig::getDirFsCatalog() . 'includes/javascript/functions.js';
		$sources[] = sysConfig::getDirFsCatalog() . 'includes/javascript/general.js';
	}

	if (isset($_GET['import']) && !empty($_GET['import'])){
		foreach(explode(',', $_GET['import']) as $filePath){
			if (substr($filePath, -3) != '.js'){
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

	if (file_exists(sysConfig::getDirFsCatalog() . 'ext/jQuery/ui/i18n/' . Session::get('languages_code') . '.js')){
		$sources[] = sysConfig::getDirFsCatalog() . 'ext/jQuery/ui/i18n/' . Session::get('languages_code') . '.js';
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

	if ($env == 'catalog'){
		$TemplateManager = $appExtension->getExtension('templateManager');
		$TemplateManager->loadWidgets($templateDir);
		$boxJavascriptsEntered = array();
		$boxJavascriptSourcesEntered = array();
		$infoBoxSources = array();
		function parseContainer($Container) {
			global $TemplateManager, $boxJavascriptsEntered, $boxJavascriptSourcesEntered, $infoBoxSources;

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

					$WidgetClass = $TemplateManager->getWidget($Container['identifier']);
					if ($WidgetClass !== false){
						if (isset($WidgetSettings->id) && !empty($WidgetSettings->id)){
							$WidgetClass->setBoxId($WidgetSettings->id);
						}
						$WidgetClass->setWidgetProperties($WidgetSettings);
						if (method_exists($WidgetClass, 'buildJavascript')){
							if ($WidgetClass->buildJavascriptMultiple === true || !in_array($WidgetClass->getBoxCode(), $boxJavascriptsEntered)){
								echo $WidgetClass->buildJavascript();

								$boxJavascriptsEntered[] = $WidgetClass->getBoxCode();
							}
						}
						if (method_exists($WidgetClass, 'getJavascriptSources')){
							if (!in_array($WidgetClass->getBoxCode(), $boxJavascriptSourcesEntered)){
								$infoBoxJsFiles = $WidgetClass->getJavascriptSources();
								foreach($infoBoxJsFiles as $infoBoxJsFile){
									if (file_exists($infoBoxJsFile)){
										$infoBoxSources[] = $infoBoxJsFile;
									}
								}

								$boxJavascriptSourcesEntered[] = $WidgetClass->getBoxCode();
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
			echo '/*' . "\n" .
				' * Layout Manager Generated Javascript' . "\n" .
				' * --BEGIN--' . "\n" .
				' */' . "\n";
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
			echo '/*' . "\n" .
				' * Layout Manager Generated Javascript' . "\n" .
				' * --END--' . "\n" .
				' */' . "\n";
		}

		echo file_get_contents(sysConfig::getDirFsCatalog() . 'ext/jQuery/external/reflection/reflection.js');
		if (sizeof($infoBoxSources) > 0){
			foreach($infoBoxSources as $filePath){
				if (file_exists($filePath)){
					echo '/*' . "\n" .
						' * Required Infobox File' . "\n" .
						' * Path: ' . $filePath . "\n" .
						' * --BEGIN--' . "\n" .
						' */' . "\n";
					require($filePath);
					echo '/*' . "\n" .
						' * Required Infobox File' . "\n" .
						' * Path: ' . $filePath . "\n" .
						' * --END--' . "\n" .
						' */' . "\n";
				}
			}
		}
	}
	$fileContent = ob_get_contents();
	ob_end_clean();

	function src1_fetch() {
		global $fileContent;
		return $fileContent;
	}

	$nowTime = time() + 60;
	$maxAge = (60 * 60 * 24 * 2);
	$expiresTime = $nowTime + $maxAge;

	if ($noMin === true || sysConfig::get('TEMPLATE_JAVASCRIPT_COMPRESSION') == 'none'){
		$Result = array(
			'headers' => array(
				'Content-Type'	 => 'application/x-javascript'
			),
			'content' => src1_fetch()
		);
		if ($noCache === false && sysConfig::get('TEMPLATE_JAVASCRIPT_CACHE') == 1){
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

		/**
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
			'id'			 => 'source1',
			'getContentFunc' => 'src1_fetch',
			'contentType'	=> Minify::TYPE_JS,
			'lastModified'   => $nowTime
		));

		// handle request
		$serveArr = array(
			'files'              => $sources,
			'maxAge'             => $maxAge,
			'debug'              => true,
			'quiet'              => true,
			'encodeMethod'       => '',
			'contentTypeCharset' => 'utf-8'
		);

		switch(sysConfig::get('TEMPLATE_JAVASCRIPT_COMPRESSION')){
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

	$JavascriptCache->setContent($Result['content']);
	$JavascriptCache->setAddedHeaders($Result['headers']);
	if ($noCache === false && sysConfig::get('TEMPLATE_JAVASCRIPT_CACHE') == 1){
		$JavascriptCache->store();
	}

	$JavascriptCache->output(false, true);

	include('includes/application_bottom.php');
}
