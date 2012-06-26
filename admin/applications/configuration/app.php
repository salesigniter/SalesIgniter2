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

$_GET['key'] = (isset($_GET['key']) ? $_GET['key'] : 'coreMyStore');

require(sysConfig::getDirFsCatalog() . 'includes/classes/fileSystemBrowser.php');

switch($App->getPageName()){
	case 'default':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE'));
		break;
	case 'product_listing':
		sysLanguage::set('PAGE_TITLE', 'Product Listing Order');
		break;
	case 'product_sort_listing':
		sysLanguage::set('PAGE_TITLE', 'Product Sort Listing');
		break;
}
