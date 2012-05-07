<?php
$appContent = $App->getAppContentFile();

// calculate category path
if (isset($_GET['cPath'])){
	$cPath = $_GET['cPath'];
}
else {
	$cPath = '';
}

if (tep_not_null($cPath)){
	$cPath_array = tep_parse_category_path($cPath);
	$cPath = implode('_', $cPath_array);
	$current_category_id = $cPath_array[(sizeof($cPath_array) - 1)];
}
else {
	$current_category_id = 0;
}

switch($App->getAppPage()){
	case 'new':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_NEW'));
		$App->addJavascriptFile('admin/rental_wysiwyg/ckeditor.js');
		$App->addJavascriptFile('admin/rental_wysiwyg/adapters/jquery.js');
		break;
	case 'default':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_DEFAULT'));
		break;
}
