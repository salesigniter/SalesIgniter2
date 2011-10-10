<?php
/*
	Multi Stores Extension Version 1

	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/
require(sysConfig::getDirFsCatalog() . 'includes/classes/currencies.php');
$currencies = new currencies();

$appContent = $App->getAppContentFile();
?>