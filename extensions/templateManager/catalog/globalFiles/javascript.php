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
	$templateDir = 'fallback';
}
$import = '';
if (isset($_GET['import']) && !empty($_GET['import'])){
	$import = implode(',', $_GET['import']);
}
$cacheKey = $templateDir . '-' . md5($_SERVER['HTTP_USER_AGENT'] . '-' . $layoutId . '-' . $import);
$noCache = /*isset($_GET['noCache'])*/true;
$noMin = isset($_GET['noMin']);

require('includes/classes/system_cache.php');
$JavascriptCache = new SystemCache($cacheKey, 'cache/' . $env . '/javascript/');
if ($noCache === false && $JavascriptCache->loadData() === true){
	$JavascriptCache->output(false, true);
	exit;
}
else {
	include('includes/application_top.php');
	require(sysConfig::getDirFsCatalog() . 'ext/jsMin/jsMin.php');

	$sourceInfo = '';
	function getSourceFile($fileName, $filePath){
		global $sourceInfo;
		if (isset($_GET['noMin']) || $fileName == 'ckeditor.js'){
			return $filePath . $fileName;
		}
		if (file_exists($filePath . $fileName)){
			$cacheFileName = md5($filePath . $fileName);
			$sourceInfo .= '/**' . "\n";
			$sourceInfo .= 'Real Filename: ' . $filePath . $fileName . "\n";
			$sourceInfo .= 'Cache Filename: ' . sysConfig::getDirFsCatalog() . 'cache/preminified/' . $cacheFileName . '.cache' . "\n";
			if (file_exists(sysConfig::getDirFsCatalog() . 'cache/preminified/' . $cacheFileName . '.cache')){
				$sourceInfo .= filemtime($filePath . $fileName) . ' > ' . filemtime(sysConfig::getDirFsCatalog() . 'cache/preminified/' . $cacheFileName . '.cache') . ' = ' . (int)(filemtime($filePath . $fileName) > filemtime(sysConfig::getDirFsCatalog() . 'cache/preminified/' . $cacheFileName . '.cache')) . "\n";
			}
			$sourceInfo .= '*/' . "\n\n";
			if (
				file_exists(sysConfig::getDirFsCatalog() . 'cache/preminified/' . $cacheFileName . '.cache') === false ||
				(filemtime($filePath . $fileName) > filemtime(sysConfig::getDirFsCatalog() . 'cache/preminified/' . $cacheFileName . '.cache'))
			){
				$minifiedJs = JSMin::minify(file_get_contents($filePath . $fileName));

				$cachedFile = new SystemCache($cacheFileName, 'cache/preminified/');
				$cachedFile->setContent($minifiedJs);
				$cachedFile->store(false);
			}
		}
		return sysConfig::getDirFsCatalog() . 'cache/preminified/' . $cacheFileName . '.cache';
	}

	$addToFileName = '';
	$jQueryPath = sysConfig::getDirFsCatalog() . 'ext/jQuery/';
	$jQueryFile = $jQueryPath . 'jQuery';
	$jqueryUiPath = $jQueryPath . 'ui/';
	if ($noMin === false){
		//$addToFileName = '.min';
		//$jQueryFile .= '-min';
		//$jqueryUiPath .= 'minified/';
	}
	$sources = array(
		getSourceFile('jQuery.js', $jQueryPath),
		getSourceFile('jquery.ui.core.js', $jqueryUiPath),
		getSourceFile('jquery.ui.widget.js', $jqueryUiPath),
		getSourceFile('jquery.ui.mouse.js', $jqueryUiPath),
		getSourceFile('jquery.ui.position.js', $jqueryUiPath),
		getSourceFile('jquery.ui.draggable.js', $jqueryUiPath),
		getSourceFile('jquery.ui.droppable.js', $jqueryUiPath),
		getSourceFile('jquery.ui.selectable.js', $jqueryUiPath),
		getSourceFile('jquery.ui.sortable.js', $jqueryUiPath),
		getSourceFile('jquery.ui.resizable.js', $jqueryUiPath),
		getSourceFile('jquery.ui.tabs.js', $jqueryUiPath),
		getSourceFile('jquery.ui.button.js', $jqueryUiPath),
		getSourceFile('jquery.ui.dialog.js', $jqueryUiPath),
		getSourceFile('jquery.ui.datepicker.js', $jqueryUiPath),
		getSourceFile('jquery.ui.accordion.js', $jqueryUiPath),
		getSourceFile('jquery.ui.stars.js', $jqueryUiPath),
		getSourceFile('jquery.ui.progressbar.js', $jqueryUiPath),
		getSourceFile('jquery.ui.autocomplete.js', $jqueryUiPath),
		//getSourceFile('jquery.ui.menu.js', $jqueryUiPath),
		getSourceFile('jquery.ui.slider.js', $jqueryUiPath),
		getSourceFile('jquery.ui.tooltip.js', $jqueryUiPath),
		getSourceFile('jquery.ui.newGrid.js', $jqueryUiPath),
		getSourceFile('jquery.effects.core.js', $jqueryUiPath),
		getSourceFile('jquery.effects.blind.js', $jqueryUiPath),
		getSourceFile('jquery.effects.bounce.js', $jqueryUiPath),
		getSourceFile('jquery.effects.clip.js', $jqueryUiPath),
		getSourceFile('jquery.effects.core.js', $jqueryUiPath),
		getSourceFile('jquery.effects.drop.js', $jqueryUiPath),
		getSourceFile('jquery.effects.explode.js', $jqueryUiPath),
		getSourceFile('jquery.effects.fade.js', $jqueryUiPath),
		getSourceFile('jquery.effects.fold.js', $jqueryUiPath),
		getSourceFile('jquery.effects.highlight.js', $jqueryUiPath),
		getSourceFile('jquery.effects.pulsate.js', $jqueryUiPath),
		getSourceFile('jquery.effects.scale.js', $jqueryUiPath),
		getSourceFile('jquery.effects.shake.js', $jqueryUiPath),
		getSourceFile('jquery.effects.slide.js', $jqueryUiPath),
		getSourceFile('jquery.effects.transfer.js', $jqueryUiPath)
	);
	$sources[] = getSourceFile('functions.js', sysConfig::getDirFsCatalog() . 'includes/javascript/');

	if ($env == 'admin'){
		$sources[] = getSourceFile('jquery.filemanager.js', sysConfig::getDirFsCatalog() . 'ext/jQuery/external/filemanager/');
		$sources[] = getSourceFile('jquery.fileuploader.js', sysConfig::getDirFsCatalog() . 'ext/jQuery/external/fileuploader/');
		$sources[] = getSourceFile('main.js', sysConfig::getDirFsAdmin() . 'includes/javascript/');
		$sources[] = getSourceFile('general.js', sysConfig::getDirFsAdmin() . 'includes/');
	}
	else {
		$sources[] = getSourceFile('general.js', sysConfig::getDirFsCatalog() . 'includes/javascript/');
	}

	if (!empty($import)){
		foreach(explode(',', $import) as $filePath){
			if (substr($filePath, -3) != '.js'){
				continue;
			}

			if (file_exists($filePath)){
				$sources[] = getSourceFile(basename($filePath), dirname($filePath) . '/');
			}
			elseif (file_exists(sysConfig::get('DIR_FS_DOCUMENT_ROOT') . $filePath)) {
				$sources[] = getSourceFile(basename($filePath), sysConfig::get('DIR_FS_DOCUMENT_ROOT') . dirname($filePath) . '/');
			}
			elseif (file_exists(sysConfig::getDirFsCatalog() . $filePath)) {
				$sources[] = getSourceFile(basename($filePath), sysConfig::getDirFsCatalog() . dirname($filePath) . '/');
			}
			elseif (file_exists(sysConfig::getDirFsAdmin() . $filePath)) {
				$sources[] = getSourceFile(basename($filePath), sysConfig::getDirFsAdmin() . dirname($filePath) . '/');
			}
		}
	}

	if (file_exists(sysConfig::getDirFsCatalog() . 'ext/jQuery/ui/i18n/' . Session::get('languages_code') . '.js')){
		$sources[] = getSourceFile(Session::get('languages_code') . '.js', sysConfig::getDirFsCatalog() . 'ext/jQuery/ui/i18n/');
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
		$LayoutBuilder = $TemplateManager->getLayoutBuilder();
		$LayoutBuilder->setLayoutId($layoutId);
		$LayoutBuilder->loadWidgets();

		$boxJavascriptsEntered = array();
		$boxJavascriptSourcesEntered = array();
		$infoBoxSources = array();
		function parseContainer($Container) {
			global $LayoutBuilder, $boxJavascriptsEntered, $boxJavascriptSourcesEntered, $infoBoxSources;

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

			if ($type == 'container' && (($Containers = $LayoutBuilder->getContainerChildren($typeId)) !== false)){
				foreach($Containers as $ChildObj){
					parseContainer($ChildObj);
				}
			}
			elseif ($type == 'container' && (($Columns = $LayoutBuilder->getContainerColumns($typeId)) !== false)) {
				foreach($Columns as $ChildObj){
					parseContainer($ChildObj);
				}
			}
			elseif ($type == 'column' && (($Columns = $LayoutBuilder->getColumnChildren($typeId)) !== false)) {
				foreach($Columns as $ChildObj){
					parseContainer($ChildObj);
				}
			}
			elseif ($type == 'column' && (($Widgets = $LayoutBuilder->getColumnWidgets($typeId)) !== false)) {
				foreach($Widgets as $wInfo){
					parseContainer($wInfo);
				}
			}
			elseif ($type == 'widget') {
				if (($Configuration = $LayoutBuilder->getConfigInfo($type, $typeId)) !== false){
					foreach($Configuration as $config){
						if ($config['configuration_key'] == 'widget_settings'){
							$WidgetSettings = json_decode($config['configuration_value']);
							break;
						}
					}

					$WidgetClass = $LayoutBuilder->getWidget($Container['identifier']);
					if ($WidgetClass !== false){
						if (isset($WidgetSettings->id) && !empty($WidgetSettings->id)){
							$WidgetClass->setBoxId($WidgetSettings->id);
						}
						$WidgetClass->setWidgetProperties($WidgetSettings);
						if (method_exists($WidgetClass, 'buildJavascript')){
							if ($WidgetClass->buildJavascriptMultiple === true || !in_array($WidgetClass->getCode(), $boxJavascriptsEntered)){
								echo $WidgetClass->buildJavascript();

								$boxJavascriptsEntered[] = $WidgetClass->getCode();
							}
						}
						if (method_exists($WidgetClass, 'getJavascriptSources')){
							if (!in_array($WidgetClass->getCode(), $boxJavascriptSourcesEntered)){
								$infoBoxJsFiles = $WidgetClass->getJavascriptSources();
								foreach($infoBoxJsFiles as $infoBoxJsFile){
									if (file_exists($infoBoxJsFile)){
										$infoBoxSources[] = $infoBoxJsFile;
									}
								}

								$boxJavascriptSourcesEntered[] = $WidgetClass->getCode();
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

	$nowTime = time() + 60;
	$maxAge = (60 * 60 * 24 * 2);
	$expiresTime = $nowTime + $maxAge;

	$Result = array(
		'headers' => array(
			'Content-Type'     => 'application/x-javascript; charset=utf-8'
		)
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

	if ($noMin === true || sysConfig::get('TEMPLATE_JAVASCRIPT_COMPRESSION') == 'none'){
		$Result['content'] = $fileContent;
	}
	else {
		$Result['content'] = JSMin::minify($fileContent);
	}

	$JavascriptCache->setContent($sourceInfo . $preMinified . $Result['content']);
	$JavascriptCache->setAddedHeaders($Result['headers']);
	if ($noCache === false && sysConfig::get('TEMPLATE_JAVASCRIPT_CACHE') == 1){
		$JavascriptCache->store();
	}

	$JavascriptCache->output(false, true);

	include('includes/application_bottom.php');
}
