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

require(dirname(__FILE__) . '/classes/labels.php');

$App->addJavascriptFile('ext/jQuery/ui/jquery.ui.labelPrinter.js');
sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE'));
