<?php
class PurchaseTypeTabMembershipRental_tab_inventory
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

	public function addTab(htmlWidget_tabs &$TabsObj, Product $Product, PurchaseType_MembershipRental $PurchaseType) {
		if ($PurchaseType->getConfigData('INVENTORY_ENABLED') == 'True'){
			$TabsObj->addTabHeader('purchaseTypeMembershipRentalSettingsTabInventory', array('text' => $this->getHeading()))
				->addTabPage('purchaseTypeMembershipRentalSettingsTabInventory', array('text' => buildNormalInventoryTabs($Product, $PurchaseType)));
		}
	}
}
