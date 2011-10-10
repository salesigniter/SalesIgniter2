<?php
//mail('sw45859@centurylink.net', 'Android Browser Agent', $_SERVER['HTTP_USER_AGENT']);

//Mozilla/5.0 (iPad; U; CPU OS 4_3_5 like Mac OS X; en-us) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8L1 Safari/6533.18.5
//Mozilla/5.0 (Linux; U; Android 2.2.1; en-gb; SAMSUNG-SGH-I897 Build/FROYO) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1

require(sysConfig::getDirFsCatalog() . 'includes/classes/template.php');
$thisTemplate = Session::get('tplDir');
$thisApp = $App->getAppName();
$thisAppPage = $App->getAppPage() . '.php';
$thisDir = sysConfig::getDirFsCatalog() . 'templates/' . $thisTemplate;
$thisFile = basename($_SERVER['PHP_SELF']);
$thisExtension = (isset($_GET['appExt']) ? $_GET['appExt'] : '');

$Template = new Template('layout.tpl', $thisDir);

$Template->setVars(array(
	'stylesheets' => $App->getStylesheetFiles(),
	'javascriptFiles' => $App->getJavascriptFiles(),
	'pageStackOutput' => ($messageStack->size('pageStack') > 0 ? $messageStack->output('pageStack') : '')
));

if (isset($_GET['cPath']) && $thisApp == 'index'){
	$thisAppPage = 'index.php';
}

$Qpages = mysql_query('select layout_id from template_pages where extension = "' . $thisExtension . '" and application = "' . $thisApp . '" and page = "' . $thisAppPage . '"');
$Page = mysql_fetch_assoc($Qpages);
$PageLayouts = (substr($Page['layout_id'], 0, 1) == ',' ? substr($Page['layout_id'], 1) : $Page['layout_id']);
$PageLayouts = (substr($Page['layout_id'], -1) == ',' ? substr($PageLayouts, 0, -1) : $PageLayouts);

$QtemplateId = mysql_query('select template_id from template_manager_templates_configuration where configuration_key = "DIRECTORY" and configuration_value = "' . $thisTemplate . '"');
$TemplateId = mysql_fetch_assoc($QtemplateId);

/* Determine Which Type Of Layout To Use --BEGIN-- */
$layoutType = 'desktop';
if (preg_match('/(ipad|xoom)/i', strtolower($_SERVER['HTTP_USER_AGENT']))){
	$layoutType = 'tablet';
}
elseif (preg_match('/(smartphone|phone|iphone|ipod|android)/i', strtolower($_SERVER['HTTP_USER_AGENT']))){
	$layoutType = 'smartphone';
}

$Qcheck = mysql_query('select layout_id from template_manager_layouts where template_id = "' . $TemplateId['template_id'] . '" and layout_id IN(' . $PageLayouts . ') and layout_type = "' . $layoutType . '"');
if (mysql_num_rows($Qcheck) > 0){
	$PageLayoutId = mysql_fetch_assoc($Qcheck);
}
else{
	$QpageLayout = mysql_query('select layout_id from template_manager_layouts where template_id = "' . $TemplateId['template_id'] . '" and layout_id IN(' . $PageLayouts . ') and layout_type = "desktop"');
	$PageLayoutId = mysql_fetch_assoc($QpageLayout);
}
/* Determine Which Type Of Layout To Use --END-- */

$layout_id = $PageLayoutId['layout_id'];

$Template->set('templateLayoutId', $layout_id);

$templateDir = sysConfig::getDirFsCatalog() . 'templates/' . Session::get('tplDir');

$pageContent = new Template('pageContent.tpl', sysConfig::getDirFsCatalog() . 'extensions/templateManager/widgetTemplates/');

$checkFiles = array(
	(isset($appContent) ? $appContent : false),
	sysConfig::getDirFsCatalog() . 'applications/' . $appContent,
	sysConfig::getDirFsCatalog() . 'templates/' . Session::get('tplDir') . '/applications/' . $App->getAppName() . '/' . $App->getPageName() . '.php',
	sysConfig::getDirFsCatalog() . 'applications/' . $App->getAppName() . '/pages/' . $App->getPageName() . '.php'
);

$requireFile = false;
foreach($checkFiles as $filePath){
	if (file_exists($filePath)){
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