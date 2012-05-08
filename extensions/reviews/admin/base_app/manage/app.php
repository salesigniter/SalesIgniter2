<?php
	$appContent = $App->getAppContentFile();
switch($App->getPageName()){
	case 'default':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_REVIEWS'));
		break;
	case 'edit':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_REVIEWS'));
		break;
	case 'preview':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_REVIEWS'));
		break;
}