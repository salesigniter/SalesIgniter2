<?php
	$appContent = $App->getAppContentFile();
	$App->addJavascriptFile('admin/rental_wysiwyg/ckeditor.js');
$App->addJavascriptFile('admin/rental_wysiwyg/adapters/jquery.js');
	if (isset($_GET['mID'])){
		$App->setInfoBoxId($_GET['mID']);
	}
sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_MAINTENANCE'));