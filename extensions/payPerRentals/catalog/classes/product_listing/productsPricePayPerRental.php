<?php
class productListing_productsPricePayPerRental {

   public function sortColumns(){

	    $QPricePerRentalProducts = Doctrine_Query::create()
	    ->from('PricePerRentalPerProducts pprp')
	    ->leftJoin('pprp.PricePayPerRentalPerProductsDescription pprpd')
	    ->where('pprpd.language_id =?', Session::get('languages_id'))
	    ->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

	    $selectSortKeys = array();

		foreach($QPricePerRentalProducts as $iPrices){
			$sortc =  array(
				'value' => 'ppprp.price',
				'name'  => $iPrices['PricePayPerRentalPerProductsDescription'][0]['price_per_rental_per_products_name']
			);
			$selectSortKeys[] = $sortc;
		}

		return $selectSortKeys;
	}

	public function show(Product &$Product){
		$return = false;
		$ProductType = $Product->getProductTypeClass();
		$ProductType->setPurchaseTypes('');
		foreach($ProductType->getPurchaseTypes() as $k => $pType){
			if($pType->getCode() == 'reservation' && $pType->hasInventory()){
				$PurchaseType = PurchaseTypeModules::getModule('reservation');
				$PurchaseType->loadProduct($Product->getID());

				if (method_exists($PurchaseType, 'showProductListing')){
					$return = $PurchaseType->showProductListing('productsPricePayPerRental');

				}
				break;
			}

		}
		/* */
		return $return;
	}
}
?>