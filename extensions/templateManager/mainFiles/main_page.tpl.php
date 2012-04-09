<?php
require(sysConfig::getDirFsCatalog() . 'includes/classes/template.php');
$thisTemplate = sysConfig::get('TEMPLATE_DIRECTORY');
$thisApp = $App->getAppName();
$thisAppPage = $App->getAppPage() . '.php';
$thisDir = sysConfig::get('DIR_FS_TEMPLATE');
$thisFile = basename($_SERVER['PHP_SELF']);
$thisExtension = (isset($_GET['appExt']) ? $_GET['appExt'] : '');

/* Determine Which Type Of Layout To Use --BEGIN-- */
$layoutType = 'desktop';
$tplFile = 'layout.tpl';
$PageContentFile = 'pageContent.tpl';
if (Session::exists('kiosk_active') && Session::get('kiosk_active') == 'True'){
	$layoutType = 'smartphone';
}
elseif (preg_match('/(ipad|xoom)/i', strtolower($_SERVER['HTTP_USER_AGENT']))){
	$layoutType = 'tablet';
	$tplFile = 'layout-tablet.tpl';
	$PageContentFile = 'tabletPageContent.tpl';
}
elseif (preg_match('/(smartphone|phone|iphone|ipod|android)/i', strtolower($_SERVER['HTTP_USER_AGENT']))){
	$layoutType = 'smartphone';
	$tplFile = 'layout-mobile.tpl';
	$PageContentFile = 'mobilePageContent.tpl';
}

$layoutPath = sysConfig::getDirFsCatalog() . 'extensions/templateManager/mainFiles';
if (file_exists(sysConfig::get('DIR_FS_TEMPLATE') . $tplFile)){
	$layoutPath = sysConfig::get('DIR_FS_TEMPLATE');
}
Session::set('layoutType', $layoutType);

$Template = new Template($tplFile, $layoutPath);

$Template->setVars(array(
	'stylesheets' => $App->getStylesheetFiles(),
	'javascriptFiles' => $App->getJavascriptFiles(),
	'pageStackOutput' => ($messageStack->size('pageStack') > 0 ? $messageStack->output('pageStack') : '')
));

if (isset($_GET['cPath']) && $thisApp == 'index'){
	$thisAppPage = 'index.php';
}

$TemplateId = Doctrine_Manager::getInstance()
	->getCurrentConnection()
	->fetchAssoc('select template_id from template_manager_templates_configuration where configuration_key = "DIRECTORY" and configuration_value = "' . $thisTemplate . '"');

$Page = Doctrine_Manager::getInstance()
	->getCurrentConnection()
	->fetchAssoc('select layout_id from template_pages where extension = "' . $thisExtension . '" and application = "' . $thisApp . '" and page = "' . $thisAppPage . '"');

$PageLayouts = (substr($Page[0]['layout_id'], 0, 1) == ',' ? substr($Page[0]['layout_id'], 1) : $Page[0]['layout_id']);
$PageLayouts = (substr($Page[0]['layout_id'], -1) == ',' ? substr($PageLayouts, 0, -1) : $PageLayouts);
if (empty($PageLayouts)){
	$Page = Doctrine_Manager::getInstance()
		->getCurrentConnection()
		->fetchAssoc('select tml.layout_id, (select count(*) from template_pages tp where FIND_IN_SET(tml.layout_id, tp.layout_id) > 0) as totalPages from template_manager_layouts tml where tml.template_id = "' . $TemplateId[0]['template_id'] . '"');
	$largestCount = 0;
	foreach($Page as $pInfo){
		if ($pInfo['totalPages'] > $largestCount){
			$PageLayouts = $pInfo['layout_id'];
		}
	}
	Doctrine_Manager::getInstance()
		->getCurrentConnection()
		->exec('insert into template_pages (application, page, extension, layout_id) values ("' . $thisApp . '", "' . $thisAppPage . '", "' . $thisExtension . '", "' . $PageLayouts . '")');
}

$PageLayoutId = Doctrine_Manager::getInstance()
	->getCurrentConnection()
	->fetchAssoc('select layout_id from template_manager_layouts where template_id = "' . $TemplateId[0]['template_id'] . '" and layout_id IN(' . $PageLayouts . ') and layout_type = "' . $layoutType . '"');
if (sizeof($PageLayoutId) <= 0){
	$PageLayoutId = Doctrine_Manager::getInstance()
		->getCurrentConnection()
		->fetchAssoc('select layout_id from template_manager_layouts where template_id = "' . $TemplateId[0]['template_id'] . '" and layout_id IN(' . $PageLayouts . ') and layout_type = "desktop"');
}

$layout_id = $PageLayoutId[0]['layout_id'];
$Template->set('templateLayoutId', $layout_id);

$templateDir = sysConfig::get('DIR_FS_TEMPLATE');

$pageContentPath = sysConfig::getDirFsCatalog() . 'extensions/templateManager/widgetTemplates';
if (file_exists(sysConfig::get('DIR_FS_TEMPLATE') . $PageContentFile)){
	$pageContentPath = sysConfig::get('DIR_FS_TEMPLATE');
}

$pageContent = new Template($PageContentFile, $pageContentPath);

$checkFiles = array(
	sysConfig::get('DIR_FS_TEMPLATE') . '/applications/' . $App->getAppName() . '/pages/' . $App->getPageName() . '.php',
	sysConfig::getDirFsCatalog() . 'applications/' . $App->getAppName() . '/pages/' . $App->getPageName() . '.php',
	(isset($appContent) ? $appContent : false),
	sysConfig::getDirFsCatalog() . 'applications/' . $appContent
);

$requireFile = false;
foreach($checkFiles as $filePath){
	if (file_exists($filePath) && is_file($filePath)){
		$requireFile = $filePath;
		break;
	}
}

if ($requireFile !== false){
	require($requireFile);
}
$Template->set('pageContent', $pageContent);

$Construct = htmlBase::newElement('div')->attr('id', 'bodyContainer');
$ExtTemplateManager = $appExtension->getExtension('templateManager');
$ExtTemplateManager->buildLayout($Construct, $layout_id);
$Template->set('templateLayoutContent', $Construct->draw());

echo $Template->parse();
?>
