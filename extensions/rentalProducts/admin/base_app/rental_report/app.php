<?php
	$appContent = $App->getAppContentFile();
	$App->addJavascriptFile('ext/jQuery/ui/jquery.ui.datepicker.js');

	require(sysConfig::getDirFsCatalog() . 'includes/classes/currencies.php');
	$currencies = new currencies();
?>