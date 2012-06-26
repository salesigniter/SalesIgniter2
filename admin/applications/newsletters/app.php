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

$checkPages = array(
	'new',
	'send',
	'confirm_send'
);
if (in_array($App->getPageName(), $checkPages)){
	if (isset($_GET['nID'])){
		$Qcheck = Doctrine_Query::create()
			->select('locked')
			->from('Newsletters')
			->where('newsletters_id = ?', (int)$_GET['nID'])
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

		if ($Qcheck[0]['locked'] < 1){
			switch($App->getPageName()){
				case 'new':
					$error = sysLanguage::get(' ERROR_EDIT_UNLOCKED_NEWSLETTER');
					break;
				case 'send':
					$error = sysLanguage::get('ERROR_SEND_UNLOCKED_NEWSLETTER');
					break;
				case 'confirm_send':
					$error = sysLanguage::get('ERROR_SEND_UNLOCKED_NEWSLETTER');
					break;
			}

			$messageStack->addSession('pageStack', $error, 'error');
			tep_redirect(itw_app_link('nID=' . $_GET['nID'], 'newsletters', 'default'));
		}
	}
}

sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE'));
