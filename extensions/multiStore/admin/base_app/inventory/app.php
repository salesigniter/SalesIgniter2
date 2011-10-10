<?php
/*
	Multi Stores Extension Version 1

	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

$appContent = $App->getAppContentFile();
$App->addJavascriptFile('ext/jQuery/ui/jquery.ui.progressbar.js');

$transferStatuses = array(
	'E' => 'Flagged For Return', //Flagged by ET to be returned
	'P' => 'Preparing', //Flagged by store returned
	'S' => 'Shipped', //Flagged by store returned
	'R' => 'Recieved' //Flagged by ET, product was received in.
);
?>