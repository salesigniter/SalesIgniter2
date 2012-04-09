<?php
	class PurchaseTypeTabReservation_tab_maintenance{

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
		$typeText = sysLanguage::get('PURCHASE_TYPE_TAB_RESERVATION_HEADING_MAINTENANCE');
		$maintenanceArr = $PurchaseType->getMaintenance();

		$methods = array();
		if (isset($maintenanceArr)){
			$methods = explode(',', $maintenanceArr);
		}

		$QMaintenancePeriods = Doctrine_Query::create()
		->from('PayPerRentalMaintenancePeriods')
		->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
		$maintenanceInputs = array();
		foreach($QMaintenancePeriods as $mPeriod){
				$maintenanceInputs[] = array(
					'value' => $mPeriod['maintenance_period_id'],
					'label' => $mPeriod['maintenance_period_name'],
					'labelPosition' => 'after'
				);
		}


		$maintenanceGroup = htmlBase::newElement('checkbox')->addGroup(array(
			'separator' => '<br />',
			'name' => 'maintenance[]',
			'checked' => $methods,
			'data' => $maintenanceInputs
		));
		$mainTable = htmlBase::newElement('table')->setCellPadding(3)->setCellSpacing(0);
		$mainTable->addBodyRow(array(
			'columns' => array(
				array('addCls' => 'main', 'text' => sysLanguage::get('TEXT_PAY_PER_RENTAL_MAINTENANCE'), 'valign' => 'top'),
				array('addCls' => 'main', 'text' => $maintenanceGroup)
			)
		));
		$TabsObj->addTabHeader('productMaintenanceTab_' . $typeName, array('text' => $typeText))
		->addTabPage('productMaintenanceTab_' . $typeName, array('text' => $mainTable->draw()));
	}
}

?>