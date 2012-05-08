<?php
	$appContent = $App->getAppContentFile();
	if (isset($_GET['eID'])){
		$App->setInfoBoxId($_GET['eID']);
	}

switch($App->getPageName()){
	case 'default':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_EVENTS'));
		break;
	case 'new':
		$App->addJavascriptFile('admin/rental_wysiwyg/ckeditor.js');
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_EVENTS'));
		break;
}
