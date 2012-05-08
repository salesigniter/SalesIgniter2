<?php
$appContent = $App->getAppContentFile();
if ($App->getAppPage() != 'noAccess'){
	$App->addJavascriptFile('ext/jQuery/external/cookie/jquery.cookie.js');
	$App->addJavascriptFile('admin/applications/index/javascript/sesWidgets.js');
	$App->addStylesheetFile('admin/applications/index/javascript/index.css');
	$App->addStylesheetFile('admin/applications/index/javascript/default.js.css');
}

switch($App->getPageName()){
	case 'default':
		sysLanguage::set('PAGE_TITLE', 'My Dashboard');
		break;
	case 'manageFavorites':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_FAVORITES'));
		break;
	case 'noAccess':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_NOACCESS'));
		break;
}
?>