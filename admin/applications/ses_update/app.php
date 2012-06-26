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

$App->addJavascriptFile('admin/applications/ses_update/javascript/jsdifflib.js');
$App->addJavascriptFile('admin/applications/ses_update/javascript/jsdifflibview.js');
$App->addStylesheetFile('admin/applications/ses_update/javascript/jsdifflibview.css');
