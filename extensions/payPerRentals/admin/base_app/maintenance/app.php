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

switch($App->getPageName()){
	case 'biweek':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_BIWEEK'));
		break;
	case 'default':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE'));
		break;
	case 'monthly':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_MONTHLY'));
		break;
	case 'quarantine':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_QUARANTINE'));
		break;
	case 'repairs':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_REPAIRS'));
		break;
}
