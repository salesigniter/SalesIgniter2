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

if (isset($_GET['customer_id'])){
	$cID = $_GET['customer_id'];
}

DataManagementModules::loadModule('customers');
$ExportModule = DataManagementModules::getModule('customers');

include(sysConfig::getDirFsCatalog() . 'includes/functions/crypt.php');

if ($App->getAppPage() == 'new'){
	$userAccount = new rentalStoreUser((isset($cID) ? $cID : ''));
	$userAccount->loadPlugins();
	if (isset($cID)){
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_EDIT'));
	}
	else {
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_NEW'));
	}
}
else {
	sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_DEFAULT'));
}
