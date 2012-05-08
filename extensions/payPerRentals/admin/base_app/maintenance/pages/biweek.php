<?php
	$Qmaint = Doctrine_Query::create()
	->from('PayPerRentalMaintenance pm')
    ->leftJoin('pm.ProductsInventoryBarcodes pib')
	->leftJoin('pib.BarcodeHistoryRented bhr')
	->orWhere('bhr.last_biweekly_date is null AND DATE_SUB(NOW(), INTERVAL 2 WEEK)<='.sysConfig::get('EXTENSION_PAY_PER_RENTALS_START_MAINTENANCE'))
	->orWhere('DATE_SUB(NOW(), INTERVAL 2 WEEK) <= bhr.last_biweekly_date');

	$tableGrid = htmlBase::newElement('grid')
	->usePagination(true)
	->setPageLimit((isset($_GET['limit']) ? (int)$_GET['limit']: 25))
	->setCurrentPage((isset($_GET['page']) ? (int)$_GET['page'] : 1))
	->setQuery($Qmaint);

	$tableGrid->addHeaderRow(array(
		'columns' => array(
			array('text' => sysLanguage::get('TABLE_HEADING_MAINTENANCE')),
			array('text' => sysLanguage::get('TABLE_HEADING_ACTION'))
		)
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

			$tableGrid->addBodyRow(array(
				'addCls'  => $addCls,
				'click'   => 'js_redirect(\'' . $onClickLink . '\');',
				'columns' => array(
					array('text' => $maint['ProductsInventoryBarcodes']['barcode']),
					array('text' => $arrowIcon->draw(), 'align' => 'right')
				)
			));
		}
	}

	$infoBox = htmlBase::newElement('infobox');
	$infoBox->setButtonBarLocation('top');

	switch ($action){
		case 'edit':
			$infoBox->setForm(array(
					'action'    => itw_app_link(tep_get_all_get_params(array('action')) . 'action=save&type=2'),
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
				->setHref(itw_app_link(tep_get_all_get_params(array('action', 'appPage')), null, 'biweek', 'SSL'));



			$infoBox->addContentRow($infoMaint->draw());
			$infoBox->addContentRow($condMaint->draw());
			$infoBox->addButton($saveButton)->addButton($cancelButton);

			break;


		default:
			if (isset($cInfo) && is_object($cInfo)) {
				$infoBox->setHeader('<b>' . $cInfo->ProductsInventoryBarcodes['barcode'] . '</b>');

				$editButton = htmlBase::newElement('button')->setType('submit')->usePreset('edit')
				->setHref(itw_app_link(tep_get_all_get_params(array('action', 'mID')) . 'action=edit&mID=' . $cInfo->pay_per_rental_maintenance_id,'maintenance','biweek'));

				$repairButton = htmlBase::newElement('button')->setType('submit')->setText('Repair')
				->setHref(itw_app_link(tep_get_all_get_params(array('action', 'mID')) . 'action=edit&mID=' . $cInfo->pay_per_rental_maintenance_id,'maintenance','repairs'));


				$infoBox->addButton($editButton);
				$infoBox->addButton($repairButton);
			}
			break;
	}
?>
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