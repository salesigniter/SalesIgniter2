<?php
require('includes/application_top.php');

$Rental = PurchaseTypeModules::getModule('rental');
$Notify = $appExtension->getExtension('notify');

$CurTime = time();

$Qorders = Doctrine_Query::create()
	->from('Orders o')
	->leftJoin('o.OrdersProducts op')
	->leftJoin('op.OrdersProductsRentals opr')
	->where('opr.rental_state = ?', $Rental->getConfigData('RENTAL_STATUS_RESERVED'))
	->execute();
if ($Qorders && $Qorders->count() > 0){
	foreach($Qorders as $Order){
		$save = false;
		foreach($Order->OrdersProducts as $Product){
			if ($Product->OrdersProductsRentals){
				$ExpiresTime = strtotime($Product->OrdersProductsRentals->date_expires);
				if ($ExpiresTime > 0 && $ExpiresTime <= $CurTime){
					$save = true;
					$Product->OrdersProductsRentals->rental_state = $Rental->getConfigData('RENTAL_STATUS_EXPIRED');
				}
			}
		}

		if ($save === true){
			$Order->save();
		}
	}
}

$Qorders = Doctrine_Query::create()
	->from('Orders o')
	->leftJoin('o.OrdersProducts op')
	->leftJoin('op.OrdersProductsRentals opr')
	->where('opr.rental_state = ?', $Rental->getConfigData('RENTAL_STATUS_OUT'))
	->execute();
if ($Qorders && $Qorders->count() > 0){
	foreach($Qorders as $Order){
		foreach($Order->OrdersProducts as $Product){
			if ($Product->OrdersProductsRentals){
				$EndTime = strtotime($Product->OrdersProductsRentals->end_date);
				if ($EndTime <= $CurTime){
					$cInfo = $Order->Customers;
					if (!empty($cInfo->customers_cell_phone) && !empty($cInfo->customers_cell_phone_carrier)){
						$sendTo = $cInfo->customers_cell_phone . '@' . $Notify->getCarrierDomain($cInfo->customers_cell_phone_carrier);
					}else{
						$sendTo = $cInfo->customers_email_address;
					}

					$Notify->sendMessage(
						$sendTo,
						'Late Movie Return',
						'You have a movie that is late, please return it as soon as possible.'
					);
				}
			}
		}
	}
}


$Qdata = Doctrine_Query::create()
	->from('CustomersBasket')
	->execute();
if ($Qdata && $Qdata->count() > 0){
	foreach($Qdata as $Cart){
		$save = false;
		$CartContents = unserialize($Cart->cart_data);
		foreach($CartContents as $CartProduct){
			if ($CartProduct->hasData('expires')){
				$ExpireTime = $CartProduct->getData('expires');
				if ($ExpireTime > 0 && $ExpireTime <= $CurTime){
					$CartContents->remove($CartProduct);
					$save = true;
				}
			}
		}
		if ($save === true){
			$Cart->cart_data = $CartContents->serialize();
			$Cart->save();
		}
	}
}

require('includes/application_bottom.php');
