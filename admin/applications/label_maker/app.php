<?php
	$appContent = $App->getAppContentFile();

	require(sysConfig::getDirFsCatalog() . 'includes/classes/product.php');
	//require(sysConfig::getDirFsAdmin() . 'includes/classes/pdf_labels.php');
	require(dirname(__FILE__) . '/classes/labels.php');

	$App->addJavascriptFile('ext/jQuery/ui/jquery.ui.datepicker.js');
	$App->addJavascriptFile('ext/jQuery/ui/jquery.ui.labelPrinter.js');
?>