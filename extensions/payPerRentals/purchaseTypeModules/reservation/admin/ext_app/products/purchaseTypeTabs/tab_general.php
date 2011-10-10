<?php
	class PurchaseTypeTabReservation_tab_general{

	private $heading;
	private $displayOrder = 0;

	public function __construct() {
		$this->setHeading(sysLanguage::get('PURCHASE_TYPE_TAB_RESERVATION_HEADING'));
	}

	public function setHeading($val) {
		$this->heading = $val;
	}

	public function getHeading() {
		return $this->heading;
	}

	public function getDisplayOrder(){
		return $this->displayOrder;
	}

	public function setDisplayOrder($val){
		$this->displayOrder = $val;
	}

	public function addTab(&$TabsObj, Product $Product, $PurchaseType) {
		$PurchaseType->loadData($Product->getId());

		$PayPerRentalTypes = Doctrine_Query::create()
		->from('PayPerRentalTypes')
		->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

		$typeName = $PurchaseType->getCode();
		$typeText = sysLanguage::get('PURCHASE_TYPE_TAB_RESERVATION_HEADING_GENERAL');

		$enabledInput = htmlBase::newElement('checkbox')
			->setName('products_type[]')
			->setChecked($PurchaseType->getData('status') == 1)
			->val($typeName);

		$overbookingInput = htmlBase::newElement('checkbox')
		->setName('reservation_overbooking')->setValue('1');
		$maxInput = htmlBase::newElement('input')
		->setName('reservation_max_period');
		$depositChargeInput = htmlBase::newElement('input')
		->setName('reservation_deposit_amount');
		$insuranceInput = htmlBase::newElement('input')
		->setName('reservation_insurance');
		$minInput = htmlBase::newElement('input')
		->setName('reservation_min_period');

		$htype = htmlBase::newElement('selectbox')
		->attr('id', 'types_select');
		$htypeMin = htmlBase::newElement('selectbox')
		->setName('reservation_min_type');
		$htypeMax = htmlBase::newElement('selectbox')
		->setName('reservation_max_type');
		foreach($PayPerRentalTypes as $iType){
			$htype->addOption($iType['pay_per_rental_types_id'], $iType['pay_per_rental_types_name']);
			$htypeMin->addOption($iType['pay_per_rental_types_id'], $iType['pay_per_rental_types_name']);
			$htypeMax->addOption($iType['pay_per_rental_types_id'], $iType['pay_per_rental_types_name']);
		}

			$overbookingInput->setChecked(($PurchaseType->getOverbooking() == 1));
			$maxInput->val($PurchaseType->getMaxPeriod());
			$htypeMax->selectOptionByValue($PurchaseType->getMaxType());
			$depositChargeInput->val($PurchaseType->getDepositAmount());
			$insuranceInput->val($PurchaseType->getInsurance());
			$minInput->val($PurchaseType->getMinPeriod());
			$htypeMin->selectOptionByValue($PurchaseType->getMinType());

		$mainTable = htmlBase::newElement('table')->setCellPadding(3)->setCellSpacing(0);
		$mainTable->addBodyRow(array(
				'columns' => array(
					array('addCls' => 'main', 'text' => sysLanguage::get('TEXT_PRODUCTS_ENABLED')),
					array('addCls' => 'main', 'text' => $enabledInput)
				)
			));

		$mainTable->addBodyRow(array(
				'columns' => array(
					array('addCls' => 'main', 'text' => sysLanguage::get('TEXT_PAY_PER_RENTAL_OVERBOOKING')),
					array('addCls' => 'main', 'text' => $overbookingInput)
				)
			));

		$mainTable->addBodyRow(array(
			'columns' => array(
				array('addCls' => 'main', 'text' => sysLanguage::get('TEXT_PAY_PER_RENTAL_DEPOSIT_AMOUNT')),
				array('addCls' => 'main', 'text' => $depositChargeInput->draw())
			)
		));

		$mainTable->addBodyRow(array(
			'columns' => array(
				array('addCls' => 'main', 'text' => sysLanguage::get('TEXT_PAY_PER_RENTAL_INSURANCE')),
				array('addCls' => 'main', 'text' => $insuranceInput->draw())
			)
		));

		$mainTable->addBodyRow(array(
			'columns' => array(
				array('addCls' => 'main', 'text' => sysLanguage::get('TEXT_PAY_PER_RENTAL_MIN_RENTAL_DAYS')),
				array('addCls' => 'main', 'text' => $minInput->draw() . $htypeMin->draw())
			)
		));

		$mainTable->addBodyRow(array(
			'columns' => array(
				array('addCls' => 'main', 'text' => sysLanguage::get('TEXT_PAY_PER_RENTAL_MAX_DAYS')),
				array('addCls' => 'main', 'text' => $maxInput->draw() . $htypeMax->draw())
			)
		));
		$TabsObj->addTabHeader('productGeneralTab_' . $typeName, array('text' => $typeText))
		->addTabPage('productGeneralTab_' . $typeName, array('text' => $mainTable->draw()));
	}
}

?>