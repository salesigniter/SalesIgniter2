<?php
class PurchaseTypeSettingsTab_lateFees
{

	private $heading;

	private $displayOrder = 5;

	public function __construct() {
		$this->setHeading(sysLanguage::get('TAB_PURCHASE_TYPE_HEADING_LATE_FEES'));
	}

	public function getDisplayOrder() {
		return $this->displayOrder;
	}

	public function setDisplayOrder($val) {
		$this->displayOrder = $val;
	}

	public function setHeading($val) {
		$this->heading = $val;
	}

	public function getHeading() {
		return $this->heading;
	}

	public function addTab(&$TabsObj, Product $Product, $PurchaseType) {
		if ($PurchaseType->getConfigData('LATE_FEES_ENABLED') == 'False') return;

		$PurchaseType->loadData($Product->getId());

		$purchaseTypeCode = $PurchaseType->getCode();
		$purchaseTypeText = $PurchaseType->getTitle();

		$inputTable = htmlBase::newElement('table')
			->setCellPadding(2)
			->setCellSpacing(0);

		$CalculationInput = htmlBase::newElement('selectbox')
			->setName('late_fee_calculation[' . $purchaseTypeCode . ']')
			->addOption('fixed', sysLanguage::get('TEXT_FIXED'))
			->addOption('percent', sysLanguage::get('TEXT_PERCENT'))
			->selectOptionByValue($PurchaseType->getData('late_fee_calculation'));

		$FeeInput = htmlBase::newElement('input')
			->setName('late_fee[' . $purchaseTypeCode . ']')
			->val($PurchaseType->getData('late_fee'));

		$inputTable->addBodyRow(array(
				'columns' => array(
					array('text' => sysLanguage::get('ENTRY_LATE_FEES_CALCULATION')),
					array('text' => $CalculationInput->draw())
				)
			));

		$inputTable->addBodyRow(array(
				'columns' => array(
					array('text' => sysLanguage::get('ENTRY_LATE_FEE')),
					array('text' => $FeeInput->draw())
				)
			));

		EventManager::notify('NewProductLateFeesTabBottom', $Product, &$inputTable, &$PurchaseType);

		$TabsObj->addTabHeader('purchaseType' . ucfirst($purchaseTypeCode) . 'SettingsTabLateFees', array('text' => $this->getHeading()))
			->addTabPage('purchaseType' . ucfirst($purchaseTypeCode) . 'SettingsTabLateFees', array('text' => $inputTable));
	}
}