<?php
	$Barcode = Doctrine_Core::getTable('ProductsInventoryBarcodes')->findOneByBarcodeId((int)$_GET['bID']);
	if ($Barcode){
		$error = false;
		if ($Barcode->status == 'O'){
			$error = true;
			$response = array(
				'success' => true,
				'errorMsg' => sysLanguage::get('TEXT_BARCODE_OUT')
			);
		}elseif ($Barcode->status == 'P'){
			$error = true;
			$response = array(
				'success' => true,
				'errorMsg' => sysLanguage::get('TEXT_BARCODE_PURCHASED')
			);
		}elseif ($appExtension->isEnabled('payPerRentals') === true){
			$Qproducts = Doctrine_Query::create()
				->from('ProductsInventoryBarcodes pib')
				->leftJoin('pib.OrdersProductsReservation opr')
				->where('pib.barcode_id=?', $_GET['bID'])
				->andWhere('opr.start_date >= ?', date('Y-m-d'))
				->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
			if (count($Qproducts) > 0){
				$error = true;
				$response = array(
					'success' => true,
					'errorMsg' => sysLanguage::get('TEXT_FUTURE_RESERVATION')
				);
			}
		}

		if ($error === false){
			$Barcode->delete();
			$response = array('success' => true);
		}
	}else{
		$response = array('success' => false);
	}
	EventManager::attachActionResponse($response, 'json');
?>