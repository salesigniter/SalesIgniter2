<?php
class PurchaseTypeTabReservation_tab_general
{

	private $heading;

	private $displayOrder = 0;

	public function __construct()
	{
		//$this->setHeading(sysLanguage::get('PURCHASE_TYPE_TAB_RESERVATION_HEADING'));
	}

	public function setHeading($val)
	{
		$this->heading = $val;
	}

	public function getHeading()
	{
		return $this->heading;
	}

	public function getDisplayOrder()
	{
		return $this->displayOrder;
	}

	public function setDisplayOrder($val)
	{
		$this->displayOrder = $val;
	}

	public function addTab(htmlWidget_tabs &$TabsObj, Product $Product, PurchaseType_reservation $PurchaseType)
	{
		$typeName = $PurchaseType->getCode();

		$enabledInput = htmlBase::newCheckbox()
			->setName('purchase_type[]')
			->setChecked($PurchaseType->getData('status') == 1)
			->val($typeName);

		$overbookingInput = htmlBase::newCheckbox()
			->setName('reservation_overbooking')
			->setValue('1')
			->setChecked(($PurchaseType->getOverbooking() == 1));

		$minInput = htmlBase::newInput()
			->setName('reservation_min_period')
			->val($PurchaseType->getMinPeriod());

		$maxInput = htmlBase::newInput()
			->setName('reservation_max_period')
			->val($PurchaseType->getMaxPeriod());

		$depositChargeInput = htmlBase::newInput()
			->setName('reservation_deposit_amount')
			->val($PurchaseType->getDepositAmount());

		$insuranceInputValue = htmlBase::newInput()
			->setName('reservation_insurance_value')
			->val($PurchaseType->getInsuranceValue());

		$insuranceInputCost = htmlBase::newInput()
			->setName('reservation_insurance_cost')
			->val($PurchaseType->getInsuranceCost());

		$htypeMin = htmlBase::newSelectbox()
			->setName('reservation_min_type')
			->selectOptionByValue($PurchaseType->getMinType());

		$htypeMax = htmlBase::newSelectbox()
			->setName('reservation_max_type')
			->selectOptionByValue($PurchaseType->getMaxType());

		foreach(PurchaseType_reservation_utilities::getRentalTypes() as $iType){
			$htypeMin->addOption($iType['pay_per_rental_types_id'], $iType['pay_per_rental_types_name']);
			$htypeMax->addOption($iType['pay_per_rental_types_id'], $iType['pay_per_rental_types_name']);
		}

		$mainTable = htmlBase::newElement('table')
			->setCellPadding(3)
			->setCellSpacing(0);

		$mainTable->addBodyRow(array(
			'columns' => array(
				array('addCls' => 'main', 'text' => sysLanguage::get('TEXT_PRODUCTS_ENABLED')),
				array('addCls' => 'main', 'text' => $enabledInput)
			)
		));

		$mainTable->addBodyRow(array(
			'columns' => array(
				array('addCls' => 'main', 'text' => sysLanguage::get('ENTRY_PAY_PER_RENTAL_OVERBOOKING')),
				array('addCls' => 'main', 'text' => $overbookingInput)
			)
		));

		$mainTable->addBodyRow(array(
			'columns' => array(
				array('addCls' => 'main', 'text' => sysLanguage::get('ENTRY_PAY_PER_RENTAL_DEPOSIT_AMOUNT')),
				array('addCls' => 'main', 'text' => $depositChargeInput->draw())
			)
		));

		$mainTable->addBodyRow(array(
			'columns' => array(
				array('addCls' => 'main', 'text' => sysLanguage::get('ENTRY_PAY_PER_RENTAL_INSURANCE_VALUE')),
				array('addCls' => 'main', 'text' => $insuranceInputValue->draw())
			)
		));

		$mainTable->addBodyRow(array(
			'columns' => array(
				array('addCls' => 'main', 'text' => sysLanguage::get('ENTRY_PAY_PER_RENTAL_INSURANCE_COST')),
				array('addCls' => 'main', 'text' => $insuranceInputCost->draw())
			)
		));

		$mainTable->addBodyRow(array(
			'columns' => array(
				array('addCls' => 'main', 'text' => sysLanguage::get('ENTRY_PAY_PER_RENTAL_MIN_RENTAL_PERIOD')),
				array('addCls' => 'main', 'text' => $minInput->draw() . $htypeMin->draw())
			)
		));

		$mainTable->addBodyRow(array(
			'columns' => array(
				array('addCls' => 'main', 'text' => sysLanguage::get('ENTRY_PAY_PER_RENTAL_MAX_RENTAL_PERIOD')),
				array('addCls' => 'main', 'text' => $maxInput->draw() . $htypeMax->draw())
			)
		));

		$TabsObj->addTabHeader('productGeneralTab_' . $typeName, array(
			'text' => sysLanguage::get('PURCHASE_TYPE_TAB_RESERVATION_HEADING_GENERAL')
		))->addTabPage('productGeneralTab_' . $typeName, array(
			'text' => $mainTable->draw()
		));
	}
}

?>