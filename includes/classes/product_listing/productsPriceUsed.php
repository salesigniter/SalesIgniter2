<?php
class productListing_productsPriceUsed {

	public function sortColumns(){
		$selectSortKeys = array(
								array(
									'value' => 'p.price_used',
									'name'  => sysLanguage::get('PRODUCT_LISTING_PRICE_USED')
								)

		);
		return $selectSortKeys;
	}

	public function show(Product &$Product){
		global $currencies;
		$ProductType = $Product->getProductTypeClass();
		if (method_exists($ProductType, 'showProductListing')){
			$return = $ProductType->showProductListing('productsPriceUsed');
		}else{
			$return = $currencies->format($Product->getPrice());
		}
		return $return;
	}
}
?>