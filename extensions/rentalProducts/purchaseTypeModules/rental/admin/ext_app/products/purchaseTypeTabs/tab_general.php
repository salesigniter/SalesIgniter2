<?php
class PurchaseTypeTabRental_tab_general
{

	private $heading;

	private $displayOrder = 1;

	public function __construct() {
		$this->setHeading(sysLanguage::get('TAB_PURCHASE_TYPE_HEADING_GENERAL'));
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
		$PurchaseType->loadData($Product->getId());

		$pricingTypeName = $PurchaseType->getCode();
		$pricingTypeText = $PurchaseType->getTitle();

		$enabledInput = htmlBase::newElement('checkbox')
			->setName('purchase_type[]')
			->setChecked($PurchaseType->getData('status') == 1)
			->val($pricingTypeName);

		$inputTable = htmlBase::newElement('table')
			->setCellPadding(2)
			->setCellSpacing(0);

		$inputTable->addBodyRow(array(
				'columns' => array(
					array('text' => sysLanguage::get('TEXT_PRODUCTS_ENABLED')),
					array('text' => $enabledInput)
				)
			));

		EventManager::notify('NewProductGeneralTabBottom', $Product, &$inputTable, &$PurchaseType);

		$TabsObj->addTabHeader('purchaseTypeRentalSettingsTabGeneral', array('text' => $this->getHeading()))
			->addTabPage('purchaseTypeRentalSettingsTabGeneral', array('text' => $inputTable));
	}
}
