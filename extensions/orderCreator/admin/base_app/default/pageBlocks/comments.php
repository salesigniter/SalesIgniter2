<?php
$statusDrop = htmlBase::newElement('selectbox')
	->setName('status')
	->selectOptionByValue($Editor->getCurrentStatus(true));
foreach($orders_statuses as $sInfo){
	$statusDrop->addOption($sInfo['id'], $sInfo['text']);
}


echo '<div class="main"><b>' . sysLanguage::get('TABLE_HEADING_COMMENTS') . '</b></div>' .
	htmlBase::newTextarea()->setName('comments')->html($Editor->InfoManager->getInfo('admin_comments'))->draw();
