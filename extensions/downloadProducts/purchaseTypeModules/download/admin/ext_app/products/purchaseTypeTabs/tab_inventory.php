<?php
class PurchaseTypeTabDownload_tab_inventory
{

	private $heading;

	private $displayOrder = 3;

	public function __construct() {
		$this->setHeading(sysLanguage::get('TAB_PURCHASE_TYPE_HEADING_INVENTORY'));
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
		global $tax_class_array;
		if ($PurchaseType->getConfigData('INVENTORY_ENABLED') == 'True'){
			$TabsObj->addTabHeader('purchaseTypeDownloadSettingsTabInventory', array('text' => $this->getHeading()))
				->addTabPage('purchaseTypeDownloadSettingsTabInventory', array('text' => buildNormalInventoryTabs($Product, $PurchaseType)));
		}
	}
}
