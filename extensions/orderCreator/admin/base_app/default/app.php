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

require(sysConfig::getDirFsCatalog() . 'includes/functions/crypt.php');
require(sysConfig::getDirFsCatalog() . 'includes/classes/http_client.php');

AccountsReceivableModules::loadModules();
OrderPaymentModules::loadModules();
OrderShippingModules::loadModules();
OrderTotalModules::loadModules();

if ($App->getAppPage() == 'new'){
	$runInit = false;
	if (!isset($_GET['action'])){
		$Editor = new OrderCreator(
			$_GET['sale_module'],
			(isset($_GET['sale_id']) ? $_GET['sale_id'] : 0),
			(isset($_GET['sale_revision']) ? $_GET['sale_revision'] : null)
		);

		Session::set('OrderCreator', $Editor);
	}
	else {
		$runInit = true;
	}

	$Editor =& Session::getReference('OrderCreator');
	if ($runInit === true){
		$Editor->init();
	}
}

//echo '<pre>';print_r($Editor);
$orders_statuses = array();
$orders_status_array = array();
$Qstatus = Doctrine_Query::create()
	->select('s.orders_status_id, sd.orders_status_name')
	->from('OrdersStatus s')
	->leftJoin('s.OrdersStatusDescription sd')
	->where('sd.language_id = ?', (int)Session::get('languages_id'))
	->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
foreach($Qstatus as $status){
	$orders_statuses[] = array(
		'id'   => $status['orders_status_id'],
		'text' => $status['OrdersStatusDescription'][0]['orders_status_name']
	);
	$orders_status_array[$status['orders_status_id']] = $status['OrdersStatusDescription'][0]['orders_status_name'];
}

if ($Editor->hasErrors()){
	foreach($Editor->getErrors() as $msg){
		$messageStack->add('pageStack', $msg, 'error');
	}
}

sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE') . '<span style="color:red;font-size: .8em;display:block;line-height: 1em;margin-bottom: 10px;margin-top: -1.6em;">To add products to order, first enter customer details and click update customer</span>');
