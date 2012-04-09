<?php
$tabsArr = array(
	'tabReturning' => array(
		'heading' => sysLanguage::get('HEADING_RETURNING_CUSTOMER'),
		'contentFile' => sysConfig::getDirFsCatalog() . 'applications/account/pages_tabs/login/returning.php'
	),
	'tabNewAccount' => array(
		'heading' => sysLanguage::get('HEADING_NEW_CUSTOMER'),
		'contentFile' => sysConfig::getDirFsCatalog() . 'applications/account/pages_tabs/login/customer.php'
	)
);

EventManager::notify('AccountLoginAddTabs', &$tabsArr);

$TabsObj = htmlBase::newElement('tabs')
	->setId('tabs');
foreach($tabsArr as $tabId => $tInfo){
	$TabsObj->addTabHeader($tabId, array(
			'text' => $tInfo['heading']
		));

	ob_start();
	include($tInfo['contentFile']);
	$TabsObj->addTabPage($tabId, array(
			'text' => ob_get_contents()
		));
	ob_end_clean();
}

$pageTitle = sysLanguage::get('HEADING_TITLE_LOGIN');

$pageContents = htmlBase::newElement('form')
	->setAction(itw_app_link('action=processLogin', 'account', 'login', 'SSL'))
	->setName('login')
	->setMethod('post')
	->html($TabsObj->draw())
	->draw();

$pageContent->set('pageTitle', $pageTitle);
$pageContent->set('pageContent', $pageContents);
