<?php
$appContent = $App->getAppContentFile();

switch($App->getPageName()){
	case 'new_post':
		$App->addJavascriptFile('admin/rental_wysiwyg/ckeditor.js');
		$App->addJavascriptFile('admin/rental_wysiwyg/adapters/jquery.js');
		if (isset($_GET['pID'])){
			$headingTitle = 'Edit Post';
		}else{
			$headingTitle = 'New Post';
		}
		sysLanguage::set('PAGE_TITLE', $headingTitle);
		break;
	case 'new_comment':
		$App->addJavascriptFile('admin/rental_wysiwyg/ckeditor.js');
		$App->addJavascriptFile('admin/rental_wysiwyg/adapters/jquery.js');
		if (isset($_GET['cID'])){
			$headingTitle = 'Edit Comment';
		}else{
			$headingTitle = 'New Comment';
		}
		sysLanguage::set('PAGE_TITLE', $headingTitle);
		break;
	case 'default':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE'));
		break;
}
