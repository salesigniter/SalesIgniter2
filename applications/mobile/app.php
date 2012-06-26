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

$pageName = $App->getPageName();
if (file_exists(sysConfig::getDirFsCatalog() . 'applications/mobile/pagesApps/' . $pageName . '.php')){
	require(sysConfig::getDirFsCatalog() . 'applications/mobile/pagesApps/' . $pageName . '.php');
}
