<?php
	require(sysConfig::getDirFsAdmin() . 'includes/classes/upload.php');
$appContent = $App->getAppContentFile();

	if ($App->getAppPage() == 'new_post' || $App->getAppPage() == 'new_comment'){
        $App->addJavascriptFile('ext/jQuery/ui/jquery.ui.datepicker.js');
		$App->addJavascriptFile('admin/rental_wysiwyg/ckeditor.js');
		$App->addJavascriptFile('admin/rental_wysiwyg/adapters/jquery.js');
	}

?>