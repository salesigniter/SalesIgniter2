<?php
	$appContent = $App->getAppContentFile();

	if ($App->getAppPage() == 'new_group'){
		$App->addJavascriptFile('admin/rental_wysiwyg/ckeditor.js');
		$App->addJavascriptFile('admin/rental_wysiwyg/adapters/jquery.js');
	}
?>