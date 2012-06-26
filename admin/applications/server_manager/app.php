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

if (!class_exists('UpgradeManager')){
	require(sysConfig::getDirFsCatalog() . '/includes/classes/upgradeManager/Base.php');
}

if (!class_exists('UpgradeManagerCompareFile')){
	require(sysConfig::getDirFsCatalog() . '/includes/classes/upgradeManager/CompareFile.php');
}

if (!class_exists('UpgradeDatabase')){
	require(sysConfig::getDirFsCatalog() . '/includes/classes/upgradeManager/Database.php');
}
