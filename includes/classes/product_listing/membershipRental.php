<?php
class productListing_membershipRental {
	public function sortColumns(){
		$selectSortKeys = array(

		);
		return $selectSortKeys;
	}

	public function show(Product &$Product){
		$return = false;
		$ProductType = $Product->getProductTypeClass();
		$ProductType->setPurchaseTypes('');
		foreach($ProductType->getPurchaseTypes() as $k => $pType){
			if($pType->getCode() == 'membershipRental'){
				$PurchaseType = PurchaseTypeModules::getModule('membershipRental');
				$PurchaseType->loadProduct($Product->getID());

				if (method_exists($PurchaseType, 'showProductListing')){
					$return = $PurchaseType->showProductListing('membershipRental');

				}
				break;
			}

		}
		return $return;
	}
}
?>