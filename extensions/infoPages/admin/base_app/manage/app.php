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

if (substr($App->getAppPage(), 0, 3) == 'new'){
	$App->addJavascriptFile('admin/rental_wysiwyg/ckeditor.js');
	$App->addJavascriptFile('admin/rental_wysiwyg/adapters/jquery.js');
}
sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE'));
