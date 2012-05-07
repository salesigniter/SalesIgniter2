<?php
if (!class_exists('CurlRequest')){
	require(sysConfig::getDirFsCatalog() . '/includes/classes/curl/Request.php');
}

if (!class_exists('CurlResponse')){
	require(sysConfig::getDirFsCatalog() . '/includes/classes/curl/Response.php');
}

if (!class_exists('CurlDownload')){
	require(sysConfig::getDirFsCatalog() . '/includes/classes/curl/Download.php');
}

if (!class_exists('DiffFile')){
	require(dirname(__FILE__) . '/classes/DiffFile.php');
}

if (!function_exists('striprn')){
	function striprn(&$v, $k) { $v = rtrim($v, "\r\n"); }
}

$appContent = $App->getAppContentFile();

$App->addJavascriptFile('admin/applications/ses_update/javascript/jsdifflib.js');
$App->addJavascriptFile('admin/applications/ses_update/javascript/jsdifflibview.js');
$App->addStylesheetFile('admin/applications/ses_update/javascript/jsdifflibview.css');
?>