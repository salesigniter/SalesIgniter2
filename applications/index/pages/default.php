<?php
$infoPages = $appExtension->getExtension('infoPages');
$pageContents = '';
if ($appExtension->isInstalled('infoPages')){
	$pageContents .= $infoPages->displayContentBlock(1);
}
$pageContent->set('pageTitle', 'Rent A Camera or Lens Easily');
$pageContent->set('pageSubTitle', 'Going on vacation? Don\'t pack your camera. Rent it!');
$pageContent->set('pageContent', $pageContents);
