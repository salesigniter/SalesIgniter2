<?php
	$appContent = $App->getAppContentFile();		

	if (substr($App->getAppPage(), 0, 3) == 'new'){
		$App->addJavascriptFile('admin/rental_wysiwyg/ckeditor.js');
		$App->addJavascriptFile('admin/rental_wysiwyg/adapters/jquery.js');
	}
sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE'));