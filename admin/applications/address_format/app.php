<?php
$appContent = $App->getAppContentFile();

switch($App->getPageName()){
	case 'default':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_DEFAULT'));
		break;
	case 'new':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_NEW'));
		break;
}
