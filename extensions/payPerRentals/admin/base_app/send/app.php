<?php
	$appContent = $App->getAppContentFile();

$App->addJavascriptFile('admin/rental_wysiwyg/ckeditor.js');
$App->addJavascriptFile('admin/rental_wysiwyg/adapters/jquery.js');
sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE'));
