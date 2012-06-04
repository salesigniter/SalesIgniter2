<?php
	$appContent = $App->getAppContentFile();

	require(sysConfig::getDirFsCatalog() . 'includes/functions/crypt.php');
	require(sysConfig::getDirFsCatalog() . 'includes/classes/http_client.php');

	if ($App->getAppPage() == 'new'){
		$runInit = false;
		if (!isset($_GET['action'])){
			$Editor = new OrderCreator();

			Session::set('OrderCreator', $Editor);
		}else{
			$runInit = true;
		}

		$Editor =& Session::getReference('OrderCreator');
		if ($runInit === true){
			$Editor->init();
		}
	}
	
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
			'id' => $status['orders_status_id'],
			'text' => $status['OrdersStatusDescription'][0]['orders_status_name']
		);
		$orders_status_array[$status['orders_status_id']] = $status['OrdersStatusDescription'][0]['orders_status_name'];
	}
	
	if ($Editor->hasErrors()){
		foreach($Editor->getErrors() as $msg){
			$messageStack->add('pageStack', $msg, 'error');
		}
	}

AccountsReceivableModules::loadModules();
OrderPaymentModules::loadModules();
OrderShippingModules::loadModules();
OrderTotalModules::loadModules();

sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE'));