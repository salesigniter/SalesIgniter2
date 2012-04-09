<?php
	$pageTitle = sysLanguage::get('HEADING_TITLE_LOGOFF');
	
	$pageButtons = htmlBase::newElement('button')
	->usePreset('continue')
	->setHref(itw_app_link(null, 'index', 'default'))
	->draw();

$pageContents = htmlBase::newElement('form')
	->setAction(itw_app_link('action=createAccount', 'account', 'create', 'SSL'))
	->setName('create_account')
	->setMethod('post')
	->html(sysLanguage::get('TEXT_MAIN_LOGOFF'))
	->draw();

	$pageContent->set('pageTitle', $pageTitle);
	$pageContent->set('pageContent', $pageContents);
	$pageContent->set('pageButtons', $pageButtons);
