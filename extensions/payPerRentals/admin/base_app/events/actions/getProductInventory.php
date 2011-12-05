<?php
$Qproduct = Doctrine_Query::create()
	->select('products_id')
	->from('Products')
	->where('products_model = ?', $_GET['model'])
	->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

$Product = new Product($Qproduct[0]['products_id']);

EventManager::attachActionResponse(array(
	'success' => true,
	'inventory' => $Product->getProductTypeClass()->getPurchaseType('reservation')->getInventoryClass()->getCurrentStock()
), 'json');