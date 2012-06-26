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

switch($App->getPageName()){
	case 'customers':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_CUSTOMERS'));
		break;
	case 'keywords':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_KEYWORDS'));
		break;
	case 'monthlySales':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_MONTHLY_SALES'));
		break;
	case 'purchasedProducts':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_PURCHASED_PRODUCTS'));
		break;
	case 'salesReport':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_SALES_REPORT'));
		break;
	case 'viewedProducts':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_VIEWED_PRODUCTS'));
		break;
}