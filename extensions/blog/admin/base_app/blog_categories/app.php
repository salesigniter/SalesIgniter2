<?php
	require(sysConfig::getDirFsAdmin() . 'includes/classes/upload.php');
$appContent = $App->getAppContentFile();

	if ($App->getAppPage() == 'new_category'){
		$App->addJavascriptFile('admin/rental_wysiwyg/ckeditor.js');
		$App->addJavascriptFile('admin/rental_wysiwyg/adapters/jquery.js');
	}
?>