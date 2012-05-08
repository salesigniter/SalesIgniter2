<?php
/*
	Articles Extension Version 1

	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

	$appContent = $App->getAppContentFile();

switch($App->getPageName()){
	case 'default':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE'));
		break;
	case 'newTopic':
		$App->addJavascriptFile('admin/rental_wysiwyg/ckeditor.js');
		$App->addJavascriptFile('admin/rental_wysiwyg/adapters/jquery.js');
		if (isset($_GET['tID']) && empty($_POST)){
			$headingTitle = sysLanguage::get('TEXT_INFO_HEADING_EDIT_TOPIC');
		}else{
			$headingTitle = sysLanguage::get('TEXT_INFO_HEADING_NEW_TOPIC');
		}
		sysLanguage::set('PAGE_TITLE', $headingTitle);
		break;
}
