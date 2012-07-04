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

require(sysConfig::getDirFsCatalog() . 'includes/classes/Order/Base.php');

AccountsReceivableModules::loadModules();

switch($AppPage->getName()){
	case 'details':
		$backButton = htmlBase::newElement('button')
			->usePreset('back')
			->setHref(itw_app_link(tep_get_all_get_params(array('action')), null, 'sales'));

		$AppPage->addMenuItem($backButton);
		break;
}
sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE'));
