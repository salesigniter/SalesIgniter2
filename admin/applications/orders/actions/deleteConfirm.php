<?php
$Order = Doctrine_Core::getTable('Orders')->find((int)$_GET['oID']);

foreach($Order->OrdersProducts as $opInfo){
	if (isset($_POST['deleteReservationRestock']) && $_POST['deleteReservationRestock'] == '1'){
		/*
		 * Commented out because the OrdersProductsReservation model covers the status updates
		 * The only issue is this flag, is there a reason to not restock reservations that
		 * are either out or reserved?
		 */
		/*foreach($opInfo->OrdersProductsReservation as $oprInfo){
			$reservationId = $oprInfo->orders_products_reservations_id;
			$trackMethod = $oprInfo->track_method;

			if ($trackMethod == 'barcode'){
				$oprInfo->ProductsInventoryBarcodes->status = 'A';
			}
			elseif ($trackMethod == 'quantity') {
				$oprInfo->ProductsInventoryQuantity->qty_out--;
				$oprInfo->ProductsInventoryQuantity->available++;
			}
			$oprInfo->save();
		}
		$opInfo->OrdersProductsReservation->delete(); //delete OrdersProducts to?
		*/
	}

	if (isset($_POST['deleteRestockNoReservation']) && $_POST['deleteRestockNoReservation'] == '1'){
		/*
		 * Commented out because the OrdersProducts model covers the status updates
		 * The only issue is this flag, is there a reason to not restock reservations that
		 * are either out or reserved?
		 */
		/*
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
		*/
	}
}
$Order->delete();
$messageStack->addSession('pageStack', 'The order has been deleted.', 'success');


EventManager::attachActionResponse(array(
	'success' => true
), 'json');
?>