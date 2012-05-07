<?php
/*
 * Sales Igniter E-Commerce System
 * Version: 2.0
 *
 * I.T. Web Experts
 * http://www.itwebexperts.com
 *
 * Copyright (c) 2011 I.T. Web Experts
 *
 * This script and its source are not distributable without the written conscent of I.T. Web Experts
 */

Doctrine_Core::loadAllModels();
$appContent = $App->getAppContentFile();

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
?>