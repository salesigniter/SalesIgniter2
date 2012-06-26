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

$App->addJavascriptFile('ext/jQuery/external/datetimepicker/jquery-ui-timepicker-addon.js');
$App->addJavascriptFile('ext/jQuery/external/hoverintent/hoverIntent.js');
//$App->addJavascriptFile('ext/jQuery/external/rfullcalendar/date.js');
$App->addJavascriptFile('ext/jQuery/external/rfullcalendar/fullcalendar.js');
$App->addStylesheetFile('ext/jQuery/external/rfullcalendar/fullcalendar.css');
$App->addJavascriptFile('ext/jQuery/external/transposetable/tabletranspose.js');
$App->addJavascriptFile('ext/jQuery/external/qTip/jquery.qtip.min.js');
$App->addStylesheetFile('ext/jQuery/external/qTip/jquery.qtip.min.css');

sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE'));
