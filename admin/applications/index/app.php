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

if ($App->getAppPage() != 'noAccess'){
	$App->addJavascriptFile('ext/jQuery/external/cookie/jquery.cookie.js');
	$App->addJavascriptFile('admin/applications/index/javascript/sesWidgets.js');
	$App->addStylesheetFile('admin/applications/index/javascript/index.css');
	$App->addStylesheetFile('admin/applications/index/javascript/default.js.css');
}

switch($App->getPageName()){
	case 'default':
		sysLanguage::set('PAGE_TITLE', 'My Dashboard');
		break;
	case 'manageFavorites':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_FAVORITES'));
		break;
	case 'noAccess':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_NOACCESS'));
		break;
}
