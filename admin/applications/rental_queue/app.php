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

set_time_limit(0);

$QUsePickupRequests = Doctrine_Query::create()
	->from('PickupRequests pr')
	->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
$usePickupRequest = false;
if (count($QUsePickupRequests) > 0){
	$usePickupRequest = true;
}

if (isset($_GET['cID'])){
	//require(sysConfig::getDirFsCatalog() . 'includes/classes/product.php');

	$userAccount = new rentalStoreUser($_GET['cID']);
	$userAccount->loadPlugins();
	$membership =& $userAccount->plugins['membership'];
	$addressBook =& $userAccount->plugins['addressBook'];
	//require(sysConfig::getDirFsAdmin() . 'includes/classes/rental_queue.php');
	//$rentalQueue = new rentalQueue_admin($_GET['cID']);
}

switch($App->getPageName()){
	case 'availability':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_AVAIL'));
		break;
	case 'default':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE'));
		break;
	case 'details':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_DETAILS'));
		break;
	case 'issues':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE'));
		break;
	case 'pastdue':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE'));
		break;
	case 'pickup_requests':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_PICKUP_REQUESTS'));
		break;
	case 'pickup_requests_report':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE'));
		break;
	case 'pickup_requests_types':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_PICKUP_TYPES'));
		break;
	case 'rented':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_RENTED'));
		break;
	case 'return':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_RETURN'));
		break;
	case 'return_barcode':
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_RETURN_BARCODE'));
		break;
}
