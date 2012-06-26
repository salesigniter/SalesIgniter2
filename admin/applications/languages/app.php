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

Doctrine_Core::loadAllModels();

switch($App->getPageName()){
	case 'defines':
		$App->addJavascriptFile(sysConfig::getDirFsAdmin() . 'rental_wysiwyg/ckeditor.js');
		$App->addJavascriptFile(sysConfig::getDirFsAdmin() . 'rental_wysiwyg/adapters/jquery.js');
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_DEFINES'));
		break;
	case 'default':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_DEFAULT'));
		break;
}

if (sysConfig::exists('GOOGLE_API_SERVER_KEY') && sysConfig::get('GOOGLE_API_SERVER_KEY') != ''){
	$googleLanguages = sysLanguage::getGoogleLanguages();
}
