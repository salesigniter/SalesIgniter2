<?php
$saleType = (isset($_GET['sale_type']) ? $_GET['sale_type'] : null);
if ($saleType === null){
	$QSaleType = Doctrine_Query::create()
		->select('sale_module')
		->from('AccountsReceivableSales')
		->where('sale_id = ?', $_GET['sale_id'])
		->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

	$saleType = $QSaleType[0]['sale_module'];
}
$Sale = AccountsReceivable::getSale($saleType, $_GET['sale_id']);
$SaleModule = $Sale->getSaleModule();

$order_updated = false;
$systemComments = array();
if ($_POST['status'] != $Sale->getStatusId()){
	$systemComments[] = 'Status Updated From "' . $Sale->getStatusName() . '" To "' . $Sale->getStatusName($_POST['status']) . '"';
	$Sale->setStatusId((int)$_POST['status']);
}

if (empty($_POST['comments']) === false){
	$oldComments = $Sale->InfoManager->getInfo('adminComments');
	if ($oldComments != $_POST['comments']){
		$systemComments[] = 'Administrator Comment Added';
		$Sale->InfoManager->setInfo(
			'adminComments',
			$oldComments . "\n\n------------------------------------------\n\n" . $_POST['comments']
		);
	}
}

$trackingNumbers = array();
if (!empty($_POST['ups_tracking_number'])){
	$trackingNumbers['ups'] = $_POST['ups_tracking_number'];
}
if (!empty($_POST['usps_tracking_number'])){
	$trackingNumbers['usps'] = $_POST['usps_tracking_number'];
}
if (!empty($_POST['fedex_tracking_number'])){
	$trackingNumbers['fedex'] = $_POST['fedex_tracking_number'];
}
if (!empty($_POST['dhl_tracking_number'])){
	$trackingNumbers['dhl'] = $_POST['dhl_tracking_number'];
}

if (!empty($trackingNumbers)){
	$oldTracking = $Sale->InfoManager->getInfo('tracking');
	if ($oldTracking != $trackingNumbers){
		$systemComments[] = 'Tracking Number(s) Added';
		$Sale->InfoManager->setInfo('tracking', $trackingNumbers);
	}
}

if (isset($_POST['notify'])){
	$systemComments[] = 'Customer Notified Of Changes';
}

if (!empty($systemComments)){
	$SaleSystemComments = $Sale->InfoManager->getInfo('systemComments');
	$SaleSystemComments[] = array(
		'fromRevision' => $SaleModule->getCurrentRevision(),
		'date'         => date(DATE_TIMESTAMP),
		'data'         => $systemComments
	);
	$Sale->InfoManager->setInfo('systemComments', $SaleSystemComments);

	$order_updated = true;
	AccountsReceivable::saveSale($Sale);
}

if (isset($_POST['notify'])){
	$Module = EmailModules::getModule('order');
	$Module->process('ORDER_STATUS_EMAIL', array(
		'SaleObj' => $Sale
	));
}

/*if ($status == sysConfig::get('ORDERS_STATUS_CANCELLED_ID')){ //cancel order
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
	foreach($Qorders as $oInfo){
		foreach($oInfo->OrdersProducts as $opInfo){
			$productClass = new product($opInfo['products_id']);
			$purchaseClass = $productClass->getPurchaseType($opInfo['purchase_type']); //what happens for rental
			$trackMethod = $purchaseClass->getTrackMethod();
			$invItems = $purchaseClass->getInventoryItems();
			if ($opInfo['purchase_type'] == 'new ' || $opInfo['purchase_type'] == 'used'){
				if (!empty($opInfo['barcode_id']) && $trackMethod == 'barcode'){
					$ProductInventoryBarcodes = Doctrine_Core::getTable('ProductsInventoryBarcodes')
						->findOneByBarcodeId($opInfo['barcode_id']);
					$ProductInventoryBarcodes->status = 'A';
					$ProductInventoryBarcodes->save();
				}
				else {
					if ($trackMethod == 'quantity'){
						$invId = $invItems[0]['inventory_id'];
						if (!empty($invId)){
							$ProductsInventoryQuantity = Doctrine_Core::getTable('ProductsInventoryQuantity')
								->findOneByInventoryId($invId);
							$ProductsInventoryQuantity->purchased--;
							$ProductsInventoryQuantity->available++;
							$ProductsInventoryQuantity->save();
						}
					}
				}
			}
		}
	}
}*/

if ($order_updated == true){
	$messageStack->addSession('pageStack', sysLanguage::get('SUCCESS_ORDER_UPDATED'), 'success');
}
else {
	$messageStack->addSession('pageStack', sysLanguage::get('WARNING_ORDER_NOT_UPDATED'), 'warning');
}

EventManager::attachActionResponse(itw_app_link(tep_get_all_get_params(array('action')), null, 'details'), 'redirect');
?>