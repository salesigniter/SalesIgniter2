<?php
	$appContent = $App->getAppContentFile();
	$App->addJavascriptFile('admin/rental_wysiwyg/ckeditor.js');
	$App->addJavascriptFile('ext/jQuery/ui/jquery.ui.tabs.js');
	if (isset($_GET['mID'])){
		$App->setInfoBoxId($_GET['mID']);
	}
?>