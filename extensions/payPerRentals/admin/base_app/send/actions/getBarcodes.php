<?php
    $term = $_POST['term'];

	$Qres = Doctrine_Query::create()
	->from('OrdersProductsReservation')
	->where('orders_products_reservations_id = ?', $_POST['resid'])
	->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

	$Qprods = Doctrine_Query::create()
	->from('OrdersProducts')
	->where('orders_products_id =?', $Qres[0]['orders_products_id'])
	->execute(array(), Doctrine_Core::HYDRATE_ARRAY);


	//$product = new product($Qprods[0]['products_id']);

	//$purchaseTypeClass = $product->getPurchaseType('reservation');
    $QProductBarcodes = Doctrine_Query::create()
	->from('ProductsInventory pi')
	->leftJoin('pi.ProductsInventoryBarcodes pib')
	->where('pi.products_id = ?', $Qprods[0]['products_id'])
	->andWhere('pi.track_method = ?','barcode')
	->andWhere('pi.controller = ?', 'normal')
	->andWhere('pi.type = ?','reservation')
	->andWhere('pib.barcode LIKE ?', $term.'%')
	->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

	$jsonData = array();
	foreach($QProductBarcodes as $barcode){
		foreach($barcode['ProductsInventoryBarcodes'] as $ibarcode){
			$jsonData[] = array(
				'value' => $ibarcode['barcode_id'],
				'label' => $ibarcode['barcode']
			);
		}
	}
	EventManager::attachActionResponse($jsonData, 'json');
?>