<?php
	$appContent = $App->getAppContentFile();
$App->addJavascriptFile('admin/rental_wysiwyg/ckeditor.js');
$App->addJavascriptFile('ext/jQuery/ui/jquery.effects.core.js');
$App->addJavascriptFile('ext/jQuery/ui/jquery.effects.slide.js');
$App->addJavascriptFile('ext/jQuery/ui/jquery.effects.fold.js');
$App->addJavascriptFile('ext/jQuery/ui/jquery.effects.fade.js');
require(sysConfig::getDirFsCatalog() . 'includes/classes/ProductBase.php');
require(sysConfig::getDirFsCatalog() . 'includes/classes/currencies.php');
$currencies = new currencies();
?>