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

$App->addJavascriptFile('admin/rental_wysiwyg/ckeditor.js');
$App->addJavascriptFile('admin/rental_wysiwyg/adapters/jquery.js');

$moduleType = $_GET['moduleType'];
switch($moduleType){
	case 'purchaseType':
		$accessorClass = 'PurchaseTypeModules';
		$headingTitle = sysLanguage::get('HEADING_TITLE_PURCHASE_TYPE');
		$moduleDirectory = 'purchaseTypeModules';
		break;
	case 'productType':
		$accessorClass = 'ProductTypeModules';
		$headingTitle = sysLanguage::get('HEADING_TITLE_PRODUCT_TYPE');
		$moduleDirectory = 'productTypeModules';
		ProductTypeModules::loadModules();
		break;
	case 'orderShipping':
		$accessorClass = 'OrderShippingModules';
		$headingTitle = sysLanguage::get('HEADING_TITLE_ORDER_SHIPPING');
		$moduleDirectory = 'orderShippingModules';
		break;
	case 'orderTotal':
		$accessorClass = 'OrderTotalModules';
		$headingTitle = sysLanguage::get('HEADING_TITLE_ORDER_TOTAL');
		$moduleDirectory = 'orderTotalModules';
		break;
	case 'accountsReceivable':
		$accessorClass = 'AccountsReceivableModules';
		$headingTitle = 'Accounts Receivable Sale Modules';
		$moduleDirectory = 'accountsReceivableModules';
		break;
	case 'cronjob':
		$accessorClass = 'CronjobModules';
		$headingTitle = 'Cron Job Modules';
		$moduleDirectory = 'cronjobModules';
		break;
	case 'orderPayment':
	default:
		$accessorClass = 'OrderPaymentModules';
		$headingTitle = sysLanguage::get('HEADING_TITLE_ORDER_PAYMENT');
		$moduleDirectory = 'orderPaymentModules';
		break;
}
sysLanguage::set('PAGE_TITLE', $headingTitle);