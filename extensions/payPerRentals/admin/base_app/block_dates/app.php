<?php
	$appContent = $App->getAppContentFile();

	if (isset($_GET['pID'])){
		$App->setInfoBoxId($_GET['pID']);
	}
switch($App->getPageName()){
	case 'new':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE'));
		break;
	case 'default':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE'));
		break;
}
