<?php
class Extension_orderCreator extends ExtensionBase {

	public function __construct(){
		parent::__construct('orderCreator');
	}

	public function preSessionInit(){
		$this->removeSession = true;
		if (isset($_GET['appExt']) && $_GET['appExt'] == 'orderCreator'){
			if (!isset($_GET['action']) && !isset($_POST['action']) && !isset($_GET['error'])){
				$this->removeSession = true;
			}else{
				$this->removeSession = false;
			}
			
			/* 
			 * Require any core classes
			 */
			require(sysConfig::getDirFsCatalog() . 'includes/classes/Order/Base.php');
			
			/*
			 * Require any extension specific classes
			 */
			require(dirname(__FILE__) . '/admin/classes/Order/Base.php');
		}else{
			EventManager::attachEvents(array(
				'SessionBeforeReadValue'
			), null, $this);
		}
	}

	public function SessionBeforeReadValue(&$value){
		if (stristr($value, 'OrderCreator')){
			$value = preg_replace('/OrderCreator\|(.*)(}}|N)/', '', $value) . '<br>';
		}
	}

	public function postSessionInit(){
		if (Session::exists('OrderCreator')){
			if (basename($_SERVER['PHP_SELF']) != 'stylesheet.php' && basename($_SERVER['PHP_SELF']) != 'javascript.php'){
				if (isset($this->removeSession) && $this->removeSession === true){
					Session::remove('OrderCreator');
				}
			}
		}
	}
	
	public function init(){
		global $appExtension;
		if ($this->isEnabled() === false) return;

		EventManager::attachEvents(array(
			'OrdersListingBeforeExecute',
			'OrdersProductsReservationListingBeforeExecuteUtilities',
			'OrderQueryBeforeExecute',
			'ReservationCheckQueryBeforeExecute',
			'ProductInventoryReportsListingQueryBeforeExecute',
			'CustomerGroupsExportQueryBeforeExecute'

		), null, $this);

		if ($appExtension->isAdmin()){
			EventManager::attachEvent('BoxCustomersAddLink', null, $this);
		}
	}

	public function OrdersProductsReservationListingBeforeExecuteUtilities(&$Qorders){
		if(!isset($_GET['isEstimate'])){
			$Qorders->andWhere('opr.is_estimate = ?', '0');
		}
	}

	public function OrdersListingBeforeExecute(&$Qorders){
		if(!isset($_GET['isEstimate'])){
			$Qorders->andWhere('o.orders_status != ?', sysConfig::get('ORDERS_STATUS_ESTIMATE_ID'));
		}
	}

	public function OrderQueryBeforeExecute(&$Qorders){
		if(!isset($_GET['isEstimate'])){
			$Qorders->andWhere('o.orders_status != ?', sysConfig::get('ORDERS_STATUS_ESTIMATE_ID'));
		}
	}

	public function ReservationCheckQueryBeforeExecute(&$Qorders, $settings){
		if(!isset($_GET['isEstimate'])){
			$Qorders->andWhere('is_estimate = ?', '0');
		}
	}

	public function ProductInventoryReportsListingQueryBeforeExecute(&$Products){
		if(!isset($_GET['isEstimate'])){
			$Products->andWhere('opr.is_estimate = 0 or opr.is_estimate is null');
		}
	}

	public function CustomerGroupsExportQueryBeforeExecute(&$Qorders){
		if(!isset($_GET['isEstimate'])){
			$Qorders->andWhere('o.orders_status != ?', sysConfig::get('ORDERS_STATUS_ESTIMATE_ID'));
		}
	}



	public function BoxCustomersAddLink(&$contents){
		if (sysPermissions::adminAccessAllowed('estimates', 'default','orderCreator') === true){
			$contents['children'][] = array(
				'link' => itw_app_link('appExt=orderCreator','estimates','default'),
				'text' => 'Estimates'
			);
		}
	}
}
?>