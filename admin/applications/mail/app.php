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

if ($App->getPageName() == 'preview' && !isset($_POST['customers_email_address'])){
	$messageStack->add('pageStack', sysLanguage::get('ERROR_NO_CUSTOMER_SELECTED'), 'error');
}

if (isset($_GET['mail_sent_to'])){
	$messageStack->add('pageStack', sprintf(sysLanguage::get('NOTICE_EMAIL_SENT_TO'), $_GET['mail_sent_to']), 'success');
}

switch($App->getPageName()){
	case 'default':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE'));
		break;
	case 'preview':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE'));
		break;
}
