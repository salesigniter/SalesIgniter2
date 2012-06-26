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

if ($App->getAppPage() == 'editAccount' && Session::exists('confirm_account') === false){
	if (empty($_POST)){
		tep_redirect(itw_app_link(null, null, 'default', 'SSL'));
	}
}

sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE'));
