<?php
$appContent = $App->getAppContentFile();

switch($App->getPageName()){
	case 'classes':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_CLASSES'));
		break;
	case 'rates':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_RATES'));
		break;
	case 'zones':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_ZONES'));
		break;
}
