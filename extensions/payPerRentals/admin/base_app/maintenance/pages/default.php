<?php
	$Qmaint = Doctrine_Query::create()
	->from('PayPerRentalMaintenance pm')
    ->leftJoin('pm.ProductsInventoryBarcodes pib');
	if(!isset($_GET['bad'])){
		$Qmaint = $Qmaint->where('maintenance_date = ?','');
	}else{
		$Qmaint = $Qmaint->where('cond = ?','2');
	}

	$tableGrid = htmlBase::newElement('grid')
	->usePagination(true)
	->setPageLimit((isset($_GET['limit']) ? (int)$_GET['limit']: 25))
	->setCurrentPage((isset($_GET['page']) ? (int)$_GET['page'] : 1))
	->setQuery($Qmaint);
    $colmnsg = array();

	$colmnsg[] = array('text' => sysLanguage::get('TABLE_HEADING_MAINTENANCE'));

	if(isset($_GET['bad'])){
				$colmnsg[] = array('text' => sysLanguage::get('TABLE_HEADING_LAST_REPAIR_DATE'));
				$colmnsg[] = array('text' => sysLanguage::get('TABLE_HEADING_LAST_REPAIR_COMMENTS'));
	}

	$colmnsg[] = array('text' => sysLanguage::get('TABLE_HEADING_ACTION'));

	$tableGrid->addHeaderRow(array(
			'columns' => $colmnsg
	));

	$maintenance = &$tableGrid->getResults();
	if ($maintenance){
		foreach($maintenance as $maint){
			$maintenanceId = $maint['pay_per_rental_maintenance_id'];

			if ((!isset($_GET['mID']) || (isset($_GET['mID']) && ($_GET['mID'] == $maintenanceId))) && !isset($cInfo)){
				$cInfo = new objectInfo($maint);
			}

			$arrowIcon = htmlBase::newElement('icon')
			->setHref(itw_app_link(tep_get_all_get_params(array('action', 'mID')) . 'mID=' . $maintenanceId));

			$onClickLink = itw_app_link(tep_get_all_get_params(array('action', 'mID')) . 'mID=' . $maintenanceId);
			if (isset($cInfo) && $maintenanceId == $cInfo->pay_per_rental_maintenance_id){
				$addCls = 'ui-state-default';
				$onClickLink = itw_app_link(tep_get_all_get_params(array('action', 'mID')) . 'action=edit&mID=' . $maintenanceId);
				$arrowIcon->setType('circleTriangleEast');
			} else {
				$addCls = '';
				$arrowIcon->setType('info');
			}
			$columns = array();

			$columns[] = array('text' => $maint['ProductsInventoryBarcodes']['barcode']);
			if(isset($_GET['bad'])){
				$comments = '';
				$date = '';
				$QRepairs = Doctrine_Query::create()
				->from('PayPerRentalMaintenance pm')
				->leftJoin('pm.PayPerRentalMaintenanceRepairs')
				->where('pm.barcode_id = ?',$maint['ProductsInventoryBarcodes']['barcode_id'])
				->andWhere('repair_date != ? ','0000-00-00 00:00:00')
				->orderBy('repair_date')
				->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
				foreach($QRepairs[0]['PayPerRentalMaintenanceRepairs'] as $repair){
					$date = $repair['repair_date'];
					$comments = $repair['comments'];
					break;
				}
				$columns[] = array('text' => $date);
				$columns[] = array('text' => $comments);

			}
			$columns[] = array('text' => $arrowIcon->draw(), 'align' => 'right');

			$tableGrid->addBodyRow(array(
				'addCls'  => $addCls,
				'click'   => 'js_redirect(\'' . $onClickLink . '\');',
				'columns' => $columns
			));
		}
	}

	$infoBox = htmlBase::newElement('infobox');
	$infoBox->setButtonBarLocation('top');

	switch ($action){
		case 'edit':
			$infoBox->setForm(array(
					'action'    => itw_app_link(tep_get_all_get_params(array('action')) . 'action=save&type=1'),
					'method'    =>  'post',
					'name'      => 'edit_maintenance'
				)
			);

			if (isset($_GET['mID'])) {
				$maintenanceRel = Doctrine_Core::getTable('PayPerRentalMaintenance')->find($_GET['mID']);
				$infoBox->setHeader('<b>Edit Maintenance</b>');
			}

			$infoMaint = htmlBase::newElement('textarea')
				->attr('rows', '5')
				->attr('cols','20')
				->addClass('makeFCK')
				->attr('name','comments');
			$infoMaint->html($maintenanceRel->comments);

			$condMaint = htmlBase::newElement('radio')
				->addGroup(array(
					'name'      => 'cond',
					'checked'   => 'g',
					'data'      => array(
						array('label' => 'Good', 'labelPosition' => 'before', 'value' => 'g'),
						array('label' => 'Broken', 'labelPosition' => 'before', 'value' => 'b')
					)
			));



			$saveButton = htmlBase::newElement('button')
				->setType('submit')
				->usePreset('save');
			$cancelButton = htmlBase::newElement('button')
				->usePreset('cancel')
				->setHref(itw_app_link(tep_get_all_get_params(array('action', 'appPage')), null, 'default', 'SSL'));



			$infoBox->addContentRow($infoMaint->draw());
			$infoBox->addContentRow($condMaint->draw());
			$infoBox->addButton($saveButton)->addButton($cancelButton);

			break;


		default:
			if (isset($cInfo) && is_object($cInfo)) {
				$infoBox->setHeader('<b>' . $cInfo->ProductsInventoryBarcodes['barcode'] . '</b>');

				$editButton = htmlBase::newElement('button')->setType('submit')->usePreset('edit')
				->setHref(itw_app_link(tep_get_all_get_params(array('action', 'mID')) . 'action=edit&mID=' . $cInfo->pay_per_rental_maintenance_id,'maintenance','default'));

				$repairButton = htmlBase::newElement('button')->setType('submit')->setText('Repair')
				->setHref(itw_app_link(tep_get_all_get_params(array('action', 'mID')) . 'action=edit&mID=' . $cInfo->pay_per_rental_maintenance_id,'maintenance','repairs'));


				$infoBox->addButton($editButton);
				$infoBox->addButton($repairButton);
			}
			break;
	}
?>
 <div class="pageHeading"><?php echo sysLanguage::get('HEADING_TITLE');?></div>
 <br />
 <div style="width:75%;float:left;">
  <div class="ui-widget ui-widget-content ui-corner-all" style="width:99%;margin-right:5px;margin-left:5px;">
   <div style="width:99%;margin:5px;"><?php echo $tableGrid->draw();?></div>
  </div>
  <div style="text-align:right;"><?php
      echo htmlBase::newElement('button')->setText('8-Point Check')
	  ->setHref(itw_app_link('appExt=payPerRentals', 'maintenance','default'))
	  ->draw();
	  echo htmlBase::newElement('button')->setText('8-Point Check Repaired')
		  ->setHref(itw_app_link('appExt=payPerRentals&bad=1', 'maintenance','default'))
		  ->draw();
      echo htmlBase::newElement('button')->setText('Biweekly')
	  ->setHref(itw_app_link('appExt=payPerRentals', 'maintenance','biweek'))
	  ->draw();
	  echo htmlBase::newElement('button')->setText('Monthly')
	  ->setHref(itw_app_link('appExt=payPerRentals', 'maintenance','monthly'))
	  ->draw();
	  echo htmlBase::newElement('button')->setText('Quarantine')
	  ->setHref(itw_app_link('appExt=payPerRentals', 'maintenance','quarantine'))
	  ->draw();
  ?></div>
 </div>
 <div style="width:25%;float:right;"><?php echo $infoBox->draw();?></div>