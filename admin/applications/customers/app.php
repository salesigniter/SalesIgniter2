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

if ($AppPage->getName() == 'new'){
	$userAccount = new rentalStoreUser((isset($cID) ? $cID : ''));
	$userAccount->loadPlugins();

	$SaveButton = htmlBase::newElement('button')
	->setType('submit')
	->usePreset('save');
	if (isset($cID)){
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_EDIT'));
		$SaveButton->setText(sysLanguage::get('TEXT_BUTTON_UPDATE'));
	}
	else {
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_NEW'));
		$SaveButton->setText(sysLanguage::get('TEXT_BUTTON_INSERT'));
	}

	$AppPage->setPageFormParam(array(
		'name'   => 'customers',
		'action' => itw_app_link(tep_get_all_get_params(array('action')) . 'action=save', null, null, 'SSL'),
		'method' => 'post'
	));

	$AppPage->addMenuItem($SaveButton);

	$CancelButton = htmlBase::newElement('button')
	->usePreset('cancel')
	->setHref(itw_app_link(null, null, 'default', 'SSL'));
	$AppPage->addMenuItem($CancelButton);
}
else {
	sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_DEFAULT'));
}
