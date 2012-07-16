<?php
class rentalProducts_admin_products_new_product extends Extension_rentalProducts {

	public function __construct(){
		parent::__construct();
	}

	public function load(){
		if ($this->isEnabled() === false) return;

		EventManager::attachEvents(array(
				'NewProductPricingTabBottom'
			), null, $this);
	}

	public function NewProductPricingTabBottom($tInfo, Product &$Product, &$inputTable, PurchaseTypeBase &$purchaseType){
		if ($purchaseType->getCode() == 'rental'){
			$RentalInfo = $purchaseType->getRentalSettings();
			$rentalPeriod = $RentalInfo['rental_period'];
			if ($rentalPeriod == '' || $rentalPeriod <= 0){
				$rentalPeriod = sysConfig::get('EXTENSION_RENTAL_PRODUCTS_RENTAL_PERIOD');
			}

			$inputTable->addBodyRow(array(
					'columns' => array(
						array('text' => sysLanguage::get('TEXT_ENTRY_RENTAL_PERIOD')),
						array('text' => $rentalPeriod . sysLanguage::get('TEXT_DAYS'))
					)
				));
		}
	}
}