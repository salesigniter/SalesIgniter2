<?php
/*
	Articles Extension Version 1

	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

	$appContent = $App->getAppContentFile();

	if ($App->getAppPage() == 'new'){
	}else{
	}

switch($App->getPageName()){
	case 'default':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE'));
		break;
	case 'new':
		$App->addJavascriptFile('admin/rental_wysiwyg/ckeditor.js');
		$App->addJavascriptFile('admin/rental_wysiwyg/adapters/jquery.js');
		if (isset($_GET['aID'])){
			$headingTitle = sysLanguage::get('HEADING_TITLE_EDIT');
		}else{
			$headingTitle = sysLanguage::get('HEADING_TITLE_NEW');
		}
		sysLanguage::set('PAGE_TITLE', $headingTitle);
		break;
}