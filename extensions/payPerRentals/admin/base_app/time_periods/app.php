<?php
	$appContent = $App->getAppContentFile();

	if (isset($_GET['pID'])){
		$App->setInfoBoxId($_GET['pID']);
	} 
switch($App->getPageName()){
	case 'new':
		$App->addJavascriptFile('admin/rental_wysiwyg/ckeditor.js');
		$App->addJavascriptFile('admin/rental_wysiwyg/adapters/jquery.js');
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE'));
		break;
	case 'default':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE'));
		break;
}
