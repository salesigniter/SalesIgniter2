<?php
/*
	Multi Stores Extension Version 1
	
	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class multiStore_admin_payPerRentals_maintenance_periods_default extends Extension_multiStore {

	public function __construct(){
		parent::__construct('multiStore');
	}
	
	public function load(){
		global $appExtension;
		if ($this->enabled === false) return;
		$appExtension->registerAsResource(__CLASS__, $this);

		EventManager::attachEvents(array(
			'MaintenancePeriodsAddFields'
		), null, $this);

	}


	
	public function MaintenancePeriodsAddFields(&$mainTable){
		$maintenancePeriodId = $_GET['mID'];

		$Qstores = Doctrine_Query::create()
		->from('Stores')
		->orderBy('stores_name')
		->execute(array(), Doctrine::HYDRATE_ARRAY);

		$htmlTabsStores = '<div id="tabStores"><ul>';

		/*
		 <ul>
  <li class="ui-tabs-nav-item"><a href="#page-2"><span><?php echo sysLanguage::get('TAB_DESCRIPTION');?></span></a></li>
 </ul>

 <div id="page-2"><?php include(sysConfig::getDirFsAdmin() . 'applications/address_format/pages_tabs/tab_description.php');?></div>

		 * */

		foreach($Qstores as $sInfo){
			$htmlTabsStores .= '<li class="ui-tabs-nav-item"><a href="#store_'.$sInfo['stores_id'].'"><span>'.$sInfo['stores_name'].'</span></a></li>';
		}
		$htmlTabsStores .= '</ul>';
		foreach($Qstores as $sInfo){

			$QmaintToStores = Doctrine_Query::create()
			->from('MaintenancePeriodsToStores mpts')
			->leftJoin('mpts.Stores s')
			->leftJoin('mpts.PayPerRentalMaintenancePeriods ppmp')
			->where('ppmp.maintenance_period_id = ?', $maintenancePeriodId)
			->andWhere('s.stores_id = ?', $sInfo['stores_id'])
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

			if(isset($QmaintToStores[0]['assign_to'])){
				$adminMaintenanceArr = $QmaintToStores[0]['assign_to'];
			}
			$admins = array();
			if (isset($adminMaintenanceArr)){
				$admins = explode(',', $adminMaintenanceArr);
			}

			$QAdmins = Doctrine_Query::create()
				->from('Admin')
				->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

			$adminInputs = array();
			foreach($QAdmins as $mAdmin){
				$adminInputs[] = array(
					'value' => $mAdmin['admin_id'],
					'label' => $mAdmin['admin_firstname'].' '.$mAdmin['admin_lastname'],
					'labelPosition' => 'after'
				);
			}


			$adminGroup = htmlBase::newElement('checkbox')->addGroup(array(
					'separator' => '<br />',
					'name' => 'admins['.$sInfo['stores_id'].'][]',
					'checked' => $admins,
					'data' => $adminInputs
				));
			$htmlTabsStores .= '<div id="store_'.$sInfo['stores_id'].'">'.$adminGroup->draw().'</div>';
		}

		$htmlTabsStores .= '</div>';

		$mainTable->addBodyRow(array(
				'columns' => array(
					array('addCls' => 'main', 'text' => sysLanguage::get('TEXT_ASSIGN_TO'), 'valign' => 'top'),
					array('addCls' => 'main', 'text' => $htmlTabsStores)
				)
			));
	}
}
?>