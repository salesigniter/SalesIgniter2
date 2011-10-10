<?php
class productListing_productsPriceNew {

	public function sortColumns(){
		$selectSortKeys = array(
								array(
									'value' => 'p.products_price',
									'name'  => sysLanguage::get('PRODUCT_LISTING_PRICE_NEW')
								),
								array(
									'value' => 'p.products_price_used',
									'name'  => sysLanguage::get('PRODUCT_LISTING_PRICE_USED')
								),
								array(
									'value' => 'p.products_price_stream',
									'name'  => sysLanguage::get('PRODUCT_LISTING_PRICE_STREAM')
								),
								array(
									'value' => 'p.products_price_download',
									'name'  => sysLanguage::get('PRODUCT_LISTING_PRICE_DOWNLOAD')
								)

		);
		return $selectSortKeys;
	}

	public function show(Product &$Product){
		global $currencies;
		$ProductType = $Product->getProductTypeClass();
		if (method_exists($ProductType, 'showProductListing')){
			$return = $ProductType->showProductListing('productsPriceNew');
		}else{
			$return = $currencies->format($Product->getPrice());
		}
		return $return;
	}

}
?>