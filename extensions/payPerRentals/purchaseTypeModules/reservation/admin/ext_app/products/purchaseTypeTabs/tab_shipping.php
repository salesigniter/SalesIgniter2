<?php
	class PurchaseTypeTabReservation_tab_shipping{

	private $heading;
	private $displayOrder = 4;
	public function __construct() {
		//$this->setHeading(sysLanguage::get('PURCHASE_TYPE_TAB_RESERVATION_HEADING'));
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

	public function addTab(htmlWidget_tabs &$TabsObj, Product $Product, PurchaseType_reservation $PurchaseType) {
		$typeName = $PurchaseType->getCode();
		$typeText = sysLanguage::get('PURCHASE_TYPE_TAB_RESERVATION_HEADING_SHIPPING');
		$shippingArr = $PurchaseType->getShipping();
		$shippingInputs = array(array(
			'id' => 'noShip',
			'value' => 'false',
			'label' => sysLanguage::get('TEXT_PAY_PER_RENTAL_DONT_SHOW_SHIPPING'),
			'labelPosition' => 'after',
			'checked' => (isset($shippingArr) && $shippingArr == 'false')
		));

		$shippingInputs[] = array(
			'id' => 'storeMethods',
			'value' => 'store',
			'label' => 'Use Store Methods',
			'labelPosition' => 'after',
			'checked' => (isset($shippingArr) && $shippingArr == 'store')
		);

		$methods = array();
		if (isset($shippingArr)){
			$methods = explode(',', $shippingArr);
		}

		if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_UPS_RESERVATION') == 'False'){
			$module = OrderShippingModules::getModule('zonereservation');
		}
		else {
			$module = OrderShippingModules::getModule('upsreservation');
		}

		if (isset($module) && is_object($module)){
			$quotes = $module->quote();
			for($i = 0, $n = sizeof($quotes['methods']); $i < $n; $i++){
				$shippingInputs[] = array(
					'value' => $quotes['methods'][$i]['id'],
					'label' => 'Reservation: ' . $quotes['methods'][$i]['title'],
					'labelPosition' => 'after'
				);
			}
		}

		$shippingGroup = htmlBase::newElement('checkbox')->addGroup(array(
			'separator' => '<br />',
			'name' => 'reservation_shipping[]',
			'checked' => $methods,
			'data' => $shippingInputs
		));
		$mainTable = htmlBase::newElement('table')->setCellPadding(3)->setCellSpacing(0);
		$mainTable->addBodyRow(array(
			'columns' => array(
				array('addCls' => 'main', 'text' => sysLanguage::get('TEXT_PAY_PER_RENTAL_SHIPPING'), 'valign' => 'top'),
				array('addCls' => 'main', 'text' => $shippingGroup)
			)
		));
		$TabsObj->addTabHeader('productShippingTab_' . $typeName, array('text' => $typeText))
		->addTabPage('productShippingTab_' . $typeName, array('text' => $mainTable->draw()));
	}
}

?>