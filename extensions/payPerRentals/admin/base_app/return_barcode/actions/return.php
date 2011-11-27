<?php
	$error = true;
	$returnBarcodes = array();
	$lastBarcode = -1;
	for($i=0, $n=sizeof($_POST['barcode']); $i<$n; $i++){
		if (!empty($_POST['barcode'][$i])){
			$error = false;
			$returnBarcodes[] = array(
				'barcode' => $_POST['barcode'][$i],
				'broken'  => (isset($_POST['broken'][$i]) ? $_POST['broken'][$i] : 0),
				'comment' => $_POST['comment'][$i]
			);
		}
	}

	if ($error === true){
		$messageStack->addSession('pageStack', sysLanguage::get('TEXT_ERROR_NO_BARCODE_ENTERED'), 'error');
	}else{
		$msgError = array();
		$msgSuccess = array();
		$msgWarning = array();
		for($i=0;$i<sizeof($returnBarcodes);$i++){
			$error = false;
			$Qbarcode = Doctrine_Query::create()
			->select('i.type, b.barcode_id')
			->from('ProductsInventory i')
			->leftJoin('i.ProductsInventoryBarcodes b')
			->where('b.barcode = ?', $returnBarcodes[$i]['barcode'])
			->fetchOne();
			if ($Qbarcode === false){
				$msgWarning[] = sprintf(
					sysLanguage::get('TEXT_BARCODE_NOT_RECOGNIZED'),
					$returnBarcodes[$i]['barcode']
				);
				continue;
			}

			$inventory = $Qbarcode->toArray(true);
			$barcode = $inventory['ProductsInventoryBarcodes'][0];

			if ($inventory['type'] == 'reservation'){
				$Qbooking = Doctrine_Query::create()
				->from('OrdersProductsReservation')
				->where('barcode_id = ?', $barcode['barcode_id'])
				->andWhere('rental_state = ?', 'out')
				->andWhere('parent_id is null')
				->fetchOne();
				if ($Qbooking === false){
					$error = true;
				}else{
					$lastBarcode = $barcode['barcode_id'];
					ReservationUtilities::returnReservation(
						$Qbooking['orders_products_reservations_id'],
						($returnBarcodes[$i]['broken'] == '1' ? 'B' : ''),
						$returnBarcodes[$i]['comment'],
						'',
						$returnBarcodes[$i]['broken']
					);
				}
			}

			if ($error === true){
				$msgError[] = sprintf(
					sysLanguage::get('TEXT_BARCODE_NOT_RENTED'),
					$returnBarcodes[$i]['barcode']
				);
			}else{
				$msgSuccess[] = sprintf(
					sysLanguage::get('TEXT_PRODUCT_RETURNED'),
					tep_get_products_name($productsId),
					$returnBarcodes[$i]['barcode']
				);
			}
		}

		if (isset($msgError) && sizeof($msgError) > 0){
			$messageStack->addSessionMultiple('pageStack', $msgError, 'error', 'ordered_list');
		}

		if (isset($msgSuccess) && sizeof($msgSuccess) > 0){
			$messageStack->addSessionMultiple('pageStack', $msgSuccess, 'success');
		}

		if (isset($msgWarning) && sizeof($msgWarning) > 0){
			$messageStack->addSessionMultiple('pageStack', $msgWarning, 'warning');
		}
	}
EventManager::attachActionResponse(array(
		'success' => true,
		'lastBarcode' => $lastBarcode
	), 'json');
?>