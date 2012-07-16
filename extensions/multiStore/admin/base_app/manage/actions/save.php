<?php
/*
	Multi Stores Extension Version 1.1
	
	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/
$MultiStore = $appExtension->getExtension('multiStore');

$Stores = Doctrine::getTable('Stores');
if (isset($_GET['store_id'])){
	$Store = $Stores->find((int)$_GET['store_id']);
}
else {
	$Store = $Stores->create();
}

$isDefault = $_POST['is_default'];
if ($isDefault == 1){
	Doctrine_Query::create()
		->update('Stores')
		->set('is_default', '?', '0')
		->execute();
}

$Store->stores_name = $_POST['stores_name'];
$Store->stores_domain = $_POST['stores_domain'];
$Store->stores_ssl_domain = $_POST['stores_ssl_domain'];
$Store->stores_data = $_POST['stores_data'];
$Store->is_default = $isDefault;

$CategoriesToStores = $Store->CategoriesToStores;

if (isset($_GET['store_id'])){
	$CategoriesToStores->delete();
}

if (isset($_POST['categories'])){
	$addedProducts = array();
	$addedCategories = array();
	foreach($_POST['categories'] as $categoryId){
		$CategoriesToStores[]->categories_id = $categoryId;
	}
}

//print_r($Store->toArray());
$Store->save();

EventManager::attachActionResponse(array(
	'success' => true
), 'json');
