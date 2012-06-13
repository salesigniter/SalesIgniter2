<?php
if (isset($_POST['purchase_type']) && (in_array('reservation', $_POST['purchase_type']))){
	$PayPerRental = $Product->ProductsPayPerRental;

	$PayPerRental->max_period = (int)$_POST['reservation_max_period'];
	$PayPerRental->max_type = (int)$_POST['reservation_max_type'];
	$PayPerRental->deposit_amount = (float)$_POST['reservation_deposit_amount'];
	$PayPerRental->insurance_value = (float)$_POST['reservation_insurance_value'];
	$PayPerRental->insurance_cost = (float)$_POST['reservation_insurance_cost'];
	$PayPerRental->min_period = (int)$_POST['reservation_min_period'];
	$PayPerRental->min_type = (int)$_POST['reservation_min_type'];

	if (isset($_POST['ppr_discounts'])){
		$Discounts =& $PayPerRental->ProductsPayPerRentalDiscounts;
		$Discounts->delete();
		foreach($_POST['ppr_discounts'] as $storeId => $sInfo){
			foreach($sInfo as $typeId => $typeInfo){
				foreach($typeInfo as $discountId => $discInfo){
					if (
						($discInfo['from'] == '' || $discInfo['from'] <= 0) &&
						($discInfo['to'] == '' || $discInfo['to'] <= 0) &&
						($discInfo['amount'] == '' || $discInfo['amount'] <= 0)
					){
						continue;
					}

					$Discount = new ProductsPayPerRentalDiscounts();
					$Discount->ppr_type = $typeId;
					$Discount->store_id = $storeId;
					$Discount->discount_stage = $discountId;
					$Discount->discount_from = $discInfo['from'];
					$Discount->discount_to = $discInfo['to'];
					$Discount->discount_amount = $discInfo['amount'];
					$Discount->discount_type = $discInfo['type'];

					$Discounts->add($Discount);
				}
			}
		}
	}

	if (isset($_POST['reservation_price_period'])){
		$Period = Doctrine_Core::getTable('ProductsPayPerPeriods');
		if ($Product->products_id > 0){
			$Period = $Period->findByProductsId($Product->products_id);
			$Period->delete();
		}
		//else{
		//	$Period = $Period->getRecord();
		//}

		foreach($_POST['reservation_price_period'] as $period => $price){
			$ProductPeriods = new ProductsPayPerPeriods;
			$ProductPeriods->products_id = $Product->products_id;
			$ProductPeriods->period_id = $period;
			$ProductPeriods->price = $price;
			$ProductPeriods->save();
		}
	}

	if (isset($_POST['reservation_shipping'])){
		if (is_array($_POST['reservation_shipping'])){
			$PayPerRental->shipping = implode(',', $_POST['reservation_shipping']);
		}
		else {
			$PayPerRental->shipping = $_POST['reservation_shipping'];
		}
	}

	if (isset($_POST['reservation_overbooking'])){
		$PayPerRental->overbooking = (int)$_POST['reservation_overbooking'];
	}
	else {
		$PayPerRental->overbooking = '0';
	}

	$Product->save();

	/*Period Metrics*/
	$PricePerRentalPerProducts = $PayPerRental->PricePerRentalPerProducts;
	$saveArray = array();
	if (isset($_POST['pprp'])){
		$PricePerRentalPerProducts->delete();

		foreach($_POST['pprp'] as $pprid => $iPrice){
			$PricePerProduct = new PricePerRentalPerProducts();
			$Description = $PricePerProduct->Description;
			if (isset($iPrice['details'])){
				foreach($iPrice['details'] as $langId => $Name){
					if (isset($Name) && !empty($Name)){
						$Description[$langId]->language_id = $langId;
						$Description[$langId]->price_per_rental_per_products_name = $Name;
					}
				}
			}

			$PricePerProduct->price = $iPrice['price'];
			$PricePerProduct->number_of = $iPrice['number_of'];
			$PricePerProduct->pay_per_rental_types_id = $iPrice['type'];

			$PricePerRentalPerProducts->add($PricePerProduct);
		}
	}
	/*End Period Metrics*/

	/*Hidden dates*/
	$PayPerRentalHiddenDatesTable = Doctrine_Core::getTable('PayPerRentalHiddenDates');
	Doctrine_Query::create()
		->delete('PayPerRentalHiddenDates')
	//->whereNotIn('price_per_rental_per_products_id', $saveArray)
		->andWhere('products_id =?', $Product->products_id)
		->execute();

	if (isset($_POST['pprhidden'])){
		foreach($_POST['pprhidden'] as $hiddenid => $iHidden){
			$PayPerRentalHiddenDates = $PayPerRentalHiddenDatesTable->create();
			$PayPerRentalHiddenDates->hidden_start_date = $iHidden['start_date'];
			$PayPerRentalHiddenDates->hidden_end_date = $iHidden['end_date'];
			$PayPerRentalHiddenDates->products_id = $Product->products_id;
			$PayPerRentalHiddenDates->save();
		}
	}
	/*End Hidden Dates*/
}

$Product->save();
?>