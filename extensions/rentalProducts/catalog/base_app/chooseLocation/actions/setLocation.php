<?php
Session::set('current_store_id', $_GET['location_id']);

$MultiStore = $appExtension->getExtension('multiStore');
$MultiStore->loadStoreInfo();

if (Session::exists('on_set_location')){
	$_POST = Session::get('on_set_location');
	$ShoppingCart->add($_POST['products_id']);
	Session::remove('on_set_location');
}

if ($userAccount->isLoggedIn() === true){
	Doctrine_Query::create()
		->update('CustomersToStores')
		->set('stores_id = ?', $MultiStore->getStoreId())
		->where('customers_id = ?', $userAccount->getCustomerId())
		->execute();
}

EventManager::attachActionResponse(array(
		'success' => true,
		'storeUrl' => itw_app_link(null, 'checkout', 'default')
	), 'json');