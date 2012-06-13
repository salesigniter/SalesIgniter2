<?php
if (!class_exists('phpQuery')){
	require(sysConfig::getDirFsCatalog() . '/includes/classes/html/dom/phpQuery.php');
}

if (!class_exists('CurlRequest')){
	require(sysConfig::getDirFsCatalog() . '/includes/classes/curl/Request.php');
}

if (!class_exists('CurlResponse')){
	require(sysConfig::getDirFsCatalog() . '/includes/classes/curl/Response.php');
}

if (!class_exists('CurlDownload')){
	require(sysConfig::getDirFsCatalog() . '/includes/classes/curl/Download.php');
}

if (!class_exists('Template')){
	require(sysConfig::getDirFsCatalog() . '/includes/classes/template.php');
}

if (!class_exists('TemplateManagerLayoutTypeModules')){
	require(sysConfig::getDirFsCatalog() . '/extensions/templateManager/layoutTypeModules/moduleBase.php');
	require(sysConfig::getDirFsCatalog() . '/extensions/templateManager/layoutTypeModules/modules.php');
}

$appContent = $App->getAppContentFile();
$TemplateManager = $appExtension->getExtension('templateManager');

if ($App->getPageName() == 'editLayout'){
	$Layout = Doctrine_Core::getTable('TemplateManagerLayouts')->find($_GET['layout_id']);
	$templateName = $Layout->Template->Configuration['DIRECTORY']->configuration_value;

	$LayoutBuilder = $TemplateManager->getLayoutBuilder();
	$LayoutBuilder->setLayoutId($Layout->layout_id);

	$App->addJavascriptFile('admin/rental_wysiwyg/ckeditor.js');
    $App->addJavascriptFile('admin/rental_wysiwyg/adapters/jquery.js');
	$App->addJavascriptFile('extensions/templateManager/admin/base_app/layout_manager/javascript/backgroundBuilder.js');

	$Imports = new DirectoryIterator(sysConfig::getDirFsCatalog() . 'extensions/templateManager/admin/base_app/layout_manager/javascript/background/');
	foreach($Imports as $fInfo){
		if ($fInfo->isDot() === true || $fInfo->isDir() === true) {
			continue;
		}

		$App->addJavascriptFile(str_replace(sysConfig::getDirFsCatalog(), '', $fInfo->getPathName()));

		$dirName = $fInfo->getBasename('.js');
		if (is_dir($fInfo->getPath() . '/' . $dirName)){
			$subImport = new DirectoryIterator($fInfo->getPath() . '/' . $dirName);
			foreach($subImport as $sfInfo){
				if ($sfInfo->isDot() === true || $sfInfo->isDir() === true) {
					continue;
				}

				$App->addJavascriptFile(str_replace(sysConfig::getDirFsCatalog(), '', $sfInfo->getPathName()));
			}
		}
	}

	$Imports = new DirectoryIterator(sysConfig::getDirFsCatalog() . 'extensions/templateManager/admin/base_app/layout_manager/javascript/tabs/');
	foreach($Imports as $fInfo){
		if ($fInfo->isDot() === true || $fInfo->isDir() === true) {
			continue;
		}

		$App->addJavascriptFile(str_replace(sysConfig::getDirFsCatalog(), '', $fInfo->getPathName()));
	}

	$App->addJavascriptFile('extensions/templateManager/admin/base_app/layout_manager/javascript/construct-parser.js');

	$App->addJavascriptFile('ext/jQuery/external/miniColors/jquery.miniColors.js');
	$App->addStylesheetFile('ext/jQuery/external/miniColors/jquery.miniColors.css');
}

function addStyles($El, $Styles) {
	$css = array();
	foreach($Styles as $sInfo){
		if ($sInfo->definition_key == 'table'){
			continue;
		}
		if ($sInfo->definition_key == 'custom_css'){
			$El->attr('data-custom_css', htmlspecialchars($sInfo->definition_value));
		}

		if (substr($sInfo->definition_value, 0, 1) == '{' || substr($sInfo->definition_value, 0, 1) == '['){
			$css[$sInfo->definition_key] = json_decode($sInfo->definition_value);
		}
		else {
			$css[$sInfo->definition_key] = $sInfo->definition_value;
		}
		$El->css($sInfo->definition_key, $css[$sInfo->definition_key]);
	}
	$El->attr('data-styles', htmlspecialchars(json_encode($css)));
}

function addInputs($El, $Config) {
	$inputVals = array();
	foreach($Config as $cInfo){
		if ($cInfo->configuration_key == 'table'){
			continue;
		}
		if (substr($cInfo->configuration_value, 0, 1) == '{' || substr($cInfo->configuration_value, 0, 1) == '['){
			$inputVals[$cInfo->configuration_key] = json_decode($cInfo->configuration_value);
		}
		else {
			$inputVals[$cInfo->configuration_key] = $cInfo->configuration_value;
		}
	}
	$El->attr('data-inputs', htmlspecialchars(json_encode($inputVals)));
}

function processContainerChildren($MainObj, &$El) {
	$El->addClass('wrapper');
	foreach($MainObj->Children as $childObj){
		$NewEl = htmlBase::newElement('div')
			->attr('data-container_id', $childObj->container_id)
			->attr('data-sort_order', (int)$childObj->sort_order)
			->addClass('container');

		if ($childObj->Styles->count() > 0){
			addStyles($NewEl, $childObj->Styles);
		}

		if ($childObj->Configuration->count() > 0){
			addInputs($NewEl, $childObj->Configuration);
		}

		$El->append($NewEl);
		processContainerColumns($NewEl, $childObj->Columns);
		if ($childObj->Children->count() > 0){
			processContainerChildren($childObj, $NewEl);
		}
	}
}

function processContainerColumns(&$Container, $Columns) {
	global $LayoutBuilder, $Layout;
	if (!$Columns){
		return;
	}

	foreach($Columns as $col){
		$ColEl = htmlBase::newElement('div')
			->attr('data-column_id', $col->column_id)
			->attr('data-sort_order', (int)$col->sort_order)
			->addClass('column');

		if ($col->Styles && $col->Styles->count() > 0){
			addStyles($ColEl, $col->Styles);
		}

		if ($col->Configuration && $col->Configuration->count() > 0){
			addInputs($ColEl, $col->Configuration);
		}

		if ($col->Children && $col->Children->count() > 0){
			processContainerColumns($ColEl, $col->Children);
		}
		else {
			$WidgetList = htmlBase::newElement('ul');
			if ($col->Widgets && $col->Widgets->count() > 0){
				foreach($col->Widgets as $wid){
					$widgetSettings = '';
					$widgetInputs = array();
					if ($wid->Configuration->count() > 0){
						foreach($wid->Configuration as $cInfo){
							if ($cInfo->configuration_key == 'widget_settings'){
								$widgetSettings = $cInfo->configuration_value;
							}
							else {
								$widgetInputs[] = $cInfo;
							}
						}
					}

					$WidgetName = 'undefined';
					$WidgetClass = $LayoutBuilder->getWidget($wid->identifier);
					if ($WidgetClass !== false){
						if (method_exists($WidgetClass, 'showLayoutPreview')){
							$settings = array(
								'settings' => json_decode($widgetSettings)
							);
							if (isset($wid->Configuration['table'])){
								$settings['tableSettings'] = json_decode($wid->Configuration['table']->configuration_value);
								$settings['tableStyles'] = json_decode($wid->Styles['table']->definition_value);
							}
							$WidgetName = $WidgetClass->showLayoutPreview($settings);
						}
						else {
							$WidgetName = $WidgetClass->getTitle();
						}
					}

					$ListItem = htmlBase::newElement('li')
						->addClass('widget')
						->attr('data-widget_id', $wid->widget_id)
						->attr('data-widget_code', $wid->identifier)
						->attr('data-widget_settings', addslashes(htmlspecialchars($widgetSettings)))
						->attr('data-sort_order', $wid->sort_order)
						->attr('data-is_table', ($WidgetClass->isTable() ? 'true' : 'false'))
						->html('<span class="widgetName">' . $WidgetName . '</span>');

					if ($wid->Styles->count() > 0){
						addStyles($ListItem, $wid->Styles);
					}
					if (sizeof($widgetInputs) > 0){
						addInputs($ListItem, $widgetInputs);
					}

					$WidgetList->append($ListItem);
				}
			}
			$ColEl->append($WidgetList);
		}

		$Container->append($ColEl);
	}
}

switch($App->getPageName()){
	case 'default':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_TEMPLATES'));
		break;
	case 'layouts':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_LAYOUTS'));
		break;
	case 'editLayout':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_LAYOUT_EDITOR'));
		break;
}