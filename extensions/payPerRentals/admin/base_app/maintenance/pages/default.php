<?php
if(!isset($_GET['cond']) && !isset($_GET['type'])){
	      $_GET['cond'] = 'good';
}
	$QMaintenancePeriodsAll = Doctrine_Query::create()
	->from('PayPerRentalMaintenancePeriods')
	->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

	$type = isset($_GET['type'])?$_GET['type']:$QMaintenancePeriodsAll[0]['maintenance_period_id'];


	$QMaintenancePeriods = Doctrine_Query::create()
	->from('PayPerRentalMaintenancePeriods')
	->where('maintenance_period_id = ?', $type)
	->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

	if($QMaintenancePeriods[0]['is_repair'] != '1'){
		$Qmaint = Doctrine_Query::create()
		->from('ProductsInventoryBarcodes pib')
		->leftJoin('pib.BarcodeHistoryRented bhr');
		if(!isset($_GET['cond']) || $_GET['cond'] == 'good'){
			$Qmaint->where('bhr.current_maintenance_type = ?',$type);
		}else{

			$Qmaint->where('bhr.current_maintenance_cond = ?', (($_GET['cond'] == 'bad')?'2':'3') );
		}
	}else{
		$Qmaint = Doctrine_Query::create()
		->from('ProductsInventoryBarcodes pib')
		->leftJoin('pib.BarcodeHistoryRented bhr')
		->where('bhr.current_maintenance_cond = ?','2');
	}

	/*$multiStore = $appExtension->getExtension('multiStore');
	if ($multiStore !== false && $multiStore->isEnabled() === true){
		$Qmaint->leftJoin('ProductsInventoryBarcodesToStore pibs')
		->andWhereIn('pibs.inventory_store_id', Session::get('admin_showing_stores'));
	}*/


	$tableGrid = htmlBase::newElement('newGrid')
	->usePagination(true)
	->setPageLimit((isset($_GET['limit']) ? (int)$_GET['limit']: 25))
	->setCurrentPage((isset($_GET['page']) ? (int)$_GET['page'] : 1))
	->setQuery($Qmaint);
    if(!isset($_GET['cond']) || $_GET['cond'] != 'bad'){
			$tableGrid->addButtons(array(
				htmlBase::newElement('button')->setText('Edit')->addClass('editButton')->disable()

			));
	}

    $colmnsg = array();

	$colmnsg[] = array('text' => sysLanguage::get('TABLE_HEADING_ITEMS'));

	if(isset($_GET['cond']) && $_GET['cond'] == 'repaired'){
				$colmnsg[] = array('text' => sysLanguage::get('TABLE_HEADING_LAST_REPAIR_DATE'));
				$colmnsg[] = array('text' => sysLanguage::get('TABLE_HEADING_LAST_REPAIR_ADMIN'));
				$colmnsg[] = array('text' => sysLanguage::get('TABLE_HEADING_LAST_REPAIR_COMMENTS'));
	}

	$tableGrid->addHeaderRow(array(
			'columns' => $colmnsg
	));

	$maintenance = &$tableGrid->getResults();
	if ($maintenance){
		foreach($maintenance as $maint){
			$maintenanceId = $maint['barcode_id'];

			$columns = array();

			$columns[] = array('text' => $maint['barcode']);
			if(isset($_GET['cond']) && $_GET['cond'] == 'repaired'){
				$comments = '';
				$date = '';

				$QRepairs = Doctrine_Query::create()
				->from('BarcodeHistoryRented bhr')
				->leftJoin('bhr.ProductsInventoryBarcodes pib')
				->leftJoin('pib.PayPerRentalMaintenanceRepairs ppmr')
				->where('bhr.barcode_id = ?',$maint['barcode_id'])
				->orderBy('ppmr.repair_date')
				->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

				foreach($QRepairs[0]['ProductsInventoryBarcodes']['PayPerRentalMaintenanceRepairs'] as $repair){
					$date = $repair['repair_date'];
					$Admin = Doctrine_Core::getTable('Admin')->find($repair['admin_id']);
					$adminName = $Admin->admin_firstname . ' ' . $Admin->admin_lastname;
					$comments = $repair['comments'];
					break;
				}
				$columns[] = array('text' => $date);
				$columns[] = array('text' => $adminName);
				$columns[] = array('text' => $comments);

			}


			$tableGrid->addBodyRow(array(
				'rowAttr' => array(
						'data-barcode_id'     => $maintenanceId,
						'data-type' => $type
				),
				'columns' => $columns
			));
		}
	}
?>
 <div class="pageHeading"><?php echo $QMaintenancePeriods[0]['maintenance_period_name'];?></div>
 <br />
<?php
   if(!isset($_GET['dialog'])){
?>
	<div>
		<form name="" method="get" action="<?php echo itw_app_link(tep_get_all_get_params(array('cond')),null,null);?>">
		Select Item Condition: <select name="cond" onchange="this.form.submit()">
			<option value="good" <?php echo (isset($_GET['cond']) && $_GET['cond'] == 'good'?'selected="selected"':''); ?>>Not Checked</option>
			<option value="repaired" <?php echo (isset($_GET['cond']) && $_GET['cond'] == 'repaired'?'selected="selected"':''); ?>>Repaired Needs Approval</option>
		</select>
		</form>
	<?php
	  //here i show all the maintenance buttons for this admin

		$QMaintenancePeriods = Doctrine_Query::create()
			->from('PayPerRentalMaintenancePeriods');
		if(Session::get('login_groups_id') != '1'){
			$QMaintenancePeriods->where('FIND_IN_SET(?, assign_to)', Session::get('login_id'));
		}
		$QMaintenancePeriods = $QMaintenancePeriods->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
		$qMaintenanceSelect = htmlBase::newElement('selectbox')
		->setName('maintenance_selectbox')
		->attr('id', 'maintenance_selectbox');

		$qMaintenanceSelect->selectOptionByValue($type);

		foreach($QMaintenancePeriods as $qPeriod){
			$qMaintenanceSelect->addOption($qPeriod['maintenance_period_id'], $qPeriod['maintenance_period_name']);
		}
		//$qMaintenanceSelect->addOption('-1', sysLanguage::get('TEXT_BUTTON_REPAIRS'));

		echo $qMaintenanceSelect->draw();

	?>

	</div>
<?php
}
?>
<div class="gridContainer">
	<div style="width:100%;float:left;">
		<div class="ui-widget ui-widget-content ui-corner-all" style="width:99%;margin-right:5px;margin-left:5px;">
			<div style="width:99%;margin:5px;"><?php echo $tableGrid->draw();?></div>
			<input type="hidden" id="lastBarcode" value="<?php echo isset($_GET['lastBarcode'])?$_GET['lastBarcode']:''; ?>">
		</div>
	</div>
</div>

	<?php
/*
 <option value="bad" <?php echo (isset($_GET['cond']) && $_GET['cond'] == 'bad'?'selected="selected"':''); ?>>To be Repaired</option>
 * */
?>