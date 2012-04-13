<?php
$orders = explode(',', $_GET['oID']);
$htmlForm = htmlBase::newElement('div')
	->attr('id', 'deleteForm');
foreach($orders as $orderId){
	$checkBoxDeleteReservation = htmlBase::newElement('checkbox')
		->setName('deleteReservationRestock[' . $orderId . ']')
		->attr('id', 'deleteReservationRestock')
		->setLabel('Delete reservations')
		->setChecked(true)
		->setValue('1');
	$checkBoxDeleteRestock = htmlBase::newElement('checkbox')
		->setName('deleteRestockNoReservation[' . $orderId . ']')
		->attr('id', 'deleteRestockNoReservation')
		->setLabel('Restock quantity based inventory')
		->setChecked(true)
		->setValue('1');

	$QOrdersQuery = Doctrine_Query::create()
		->from('Orders o')
		->leftJoin('o.OrdersAddresses oa')
		->leftJoin('o.OrdersProducts op')
		->leftJoin('op.OrdersProductsReservation opr')
		->leftJoin('opr.ProductsInventoryBarcodes ib')
		->leftJoin('ib.ProductsInventory ibi')
		->leftJoin('opr.ProductsInventoryQuantity iq')
		->leftJoin('iq.ProductsInventory iqi')
		->where('o.orders_id = ?', $orderId)
		->andWhere('oa.address_type = ?', 'customer')
		->andWhere('parent_id IS NULL');

	$Qorders = $QOrdersQuery->execute();
	$isreservation = false;
	$isquantity = false;
	foreach($Qorders as $oInfo){
		foreach($oInfo->OrdersProducts as $opInfo){
			foreach($opInfo->OrdersProductsReservation as $oprInfo){
				$isreservation = true;
				break 3;
			}

			if ($opInfo['purchase_type'] == 'new ' || $opInfo['purchase_type'] == 'used'){
				$isquantity = true;
				break 3;
			}
		}
	}

	$orderFieldset = htmlBase::newElement('fieldset')
		->setLegend('Order #' . $orderId);
	if ($isreservation){
		$orderFieldset->append($checkBoxDeleteReservation);
	}

	if ($isquantity){
		$orderFieldset->append($checkBoxDeleteRestock);
	}

	$orderIdHidden = htmlBase::newElement('input')
		->setType('hidden')
		->setName('orders[]')
		->val($orderId);
	$htmlForm->append($orderIdHidden)->append($orderFieldset);
}

EventManager::attachActionResponse(array(
	'success' => true,
	'html'	=> $htmlForm->draw()
), 'json');
?>