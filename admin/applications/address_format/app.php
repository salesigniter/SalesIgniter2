<?php
	$appContent = $App->getAppContentFile();

	if ($App->getAppPage() == 'new'){
		$App->addJavascriptFile('admin/rental_wysiwyg/ckeditor.js');
		$App->addJavascriptFile('admin/rental_wysiwyg/adapters/jquery.js');
	}
	if (isset($_GET['fID'])){
		$App->setInfoBoxId($_GET['fID']);
	} 
?>