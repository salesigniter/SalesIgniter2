<?php
	$appContent = $App->getAppContentFile();

switch($App->getPageName()){
	case 'new_banner':
		$App->addJavascriptFile('admin/rental_wysiwyg/ckeditor.js');
		$App->addJavascriptFile('admin/rental_wysiwyg/adapters/jquery.js');
		if (isset($_GET['bID'])){
			$headingTitle = 'Edit Banner';
		}else{
			$headingTitle = 'New Banner';
		}
		sysLanguage::set('PAGE_TITLE', $headingTitle);
		break;
	case 'default':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE'));
		break;
}
