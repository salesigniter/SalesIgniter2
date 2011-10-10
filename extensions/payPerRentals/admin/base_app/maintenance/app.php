<?php
	$appContent = $App->getAppContentFile();

	$App->addJavascriptFile('admin/rental_wysiwyg/ckeditor.js');

	if (isset($_GET['mID'])){
		$App->setInfoBoxId($_GET['mID']);
	} 
?>