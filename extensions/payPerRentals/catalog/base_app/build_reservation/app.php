<?php
/**
 * Sales Igniter E-Commerce System
 * Version: {ses_version}
 *
 * I.T. Web Experts
 * http://www.itwebexperts.com
 *
 * Copyright (c) {ses_copyright} I.T. Web Experts
 *
 * This script and its source are not distributable without the written consent of I.T. Web Experts
 */

/*
	Pay Per Rentals Extension Version 1

	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

require('includes/functions/google_maps_ppr.php');
require('includes/classes/json.php');

$App->addJavascriptFile('ext/jQuery/external/fullcalendar/fullcalendar.js');
$App->addJavascriptFile('ext/jQuery/external/datepick/jquery.datepick.js');
$App->addJavascriptFile('ext/jQuery/external/datepick/jquery.datepick.ext.js');

$App->addStylesheetFile('ext/jQuery/external/fullcalendar/fullcalendar.css');
$App->addStylesheetFile('ext/jQuery/external/datepick/css/ui-ui-lightness.datepick.css');

if (isset($_POST['action']) && ($_POST['action'] == 'checkRes' || $_POST['action'] == 'getReservedDates')){
	$action = $_POST['action'];
}
elseif (isset($_GET['action']) && ($_GET['action'] == 'checkRes' || $_GET['action'] == 'getReservedDates')) {
	$action = $_GET['action'];
}

$navigation->remove_current_page();
