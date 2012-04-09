<?php
$appContent = $App->getAppContentFile();

//$App->addJavascriptFile('http://maps.google.com/maps?file=api&amp;v=2&amp;sensor=false&amp;key=' . sysConfig::get('GOOGLE_API_BROWSER_KEY'));
$App->addJavascriptFile('admin/rental_wysiwyg/ckeditor.js');
$App->addJavascriptFile('admin/rental_wysiwyg/adapters/jquery.js');

?>