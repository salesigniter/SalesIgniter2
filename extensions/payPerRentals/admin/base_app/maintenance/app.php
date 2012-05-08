<?php
	$appContent = $App->getAppContentFile();

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
