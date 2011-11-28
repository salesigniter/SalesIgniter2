<?php
	$QMaintenancePeriods = Doctrine_Query::create()
	->from('PayPerRentalMaintenancePeriods');
	
	$tableGrid = htmlBase::newElement('grid')
	->usePagination(true)
	->setPageLimit((isset($_GET['limit']) ? (int)$_GET['limit']: 25))
	->setCurrentPage((isset($_GET['page']) ? (int)$_GET['page'] : 1))
	->setQuery($QMaintenancePeriods);

	$tableGrid->addHeaderRow(array(
		'columns' => array(
			array('text' => sysLanguage::get('TABLE_HEADING_MAINTENANCE_NAME')),
			array('text' => sysLanguage::get('TABLE_HEADING_ACTION'))
		)
	));
	
	$maintenances = &$tableGrid->getResults();
	if ($maintenances){
		foreach($maintenances as $mInfo){
			$maintenanceId = $mInfo['maintenance_period_id'];
		
			if ((!isset($_GET['mID']) || (isset($_GET['mID']) && ($_GET['mID'] == $maintenanceId))) && !isset($mObject)){
				$mObject = new objectInfo($mInfo);
			}
		
			$arrowIcon = htmlBase::newElement('icon')
			->setHref(itw_app_link(tep_get_all_get_params(array('action', 'mID')) . 'mID=' . $maintenanceId));

			$onClickLink = itw_app_link(tep_get_all_get_params(array('action', 'mID')) . 'mID=' . $maintenanceId);
			if (isset($mObject) && $maintenanceId == $mObject->maintenance_period_id){
				$addCls = 'ui-state-default';
				$onClickLink .= itw_app_link(tep_get_all_get_params(array('action', 'mID')) . 'action=edit&mID=' . $maintenanceId);
				$arrowIcon->setType('circleTriangleEast');
			} else {
				$addCls = '';
				$arrowIcon->setType('info');
			}
		
			$tableGrid->addBodyRow(array(
				'addCls'  => $addCls,
				'click'   => 'js_redirect(\'' . $onClickLink . '\');',
				'columns' => array(
					array('text' => $mInfo['maintenance_period_name']),
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
								'action'    => itw_app_link(tep_get_all_get_params(array('action')) . 'action=save'),
								'method'    =>  'post',
								'name'      => 'edit_maintenance'
							)
			);

		 	 if (isset($_GET['mID'])) {
		            $maintenances = Doctrine_Core::getTable('PayPerRentalMaintenancePeriods')->find($_GET['mID']);
				    $mName = $maintenances->maintenance_period_name;
				    $mDescription = $maintenances->maintenance_period_description;
				    $mSDate = $maintenances->maintenance_period_start_date;
				    $beforeSend = ($maintenances->before_send == 0)?false:true;
				    $hours_beforeSend = $maintenances->hours_before_send;
				    $afterReturn = ($maintenances->after_return == 0)?false:true;
				    $hours_afterReturn = $maintenances->hours_after_return;
				    $quarantineUntilCompleted = ($maintenances->quarantine_until_completed == 0)?false:true;
				    $isRepair = ($maintenances->is_repair == 0)?false:true;
				    $emailAdmin = ($maintenances->email_admin == 0)?false:true;
				    $mNumberDays = $maintenances->show_number_days;
				    $mNumberRentals = $maintenances->show_number_rentals;
				    $qNumberDays = $maintenances->quarantine_number_days;
				    $qNumberRentals = $maintenances->quarantine_number_rentals;

				    $infoBox->setHeader('<b>Edit Maintenance Period</b>');
			 }else{
			  	    $mName = '';
				    $mDescription = '';
				    $mSDate = date('Y-m-d');
				    $beforeSend = false;
				    $afterReturn = false;
				    $hours_beforeSend = 0;
				    $hours_afterReturn = 0;
				    $quarantineUntilCompleted = false;
				    $emailAdmin = false;
				    $mNumberDays = 0;
				    $isRepair = 0;
				    $mNumberRentals = 0;
				    $qNumberDays = 0;
				    $qNumberRentals = 0;
				    $infoBox->setHeader('<b>New Maintenance Period</b>');
			 }

			 $htmlMaintenanceName = htmlBase::newElement('input')
			 ->setLabel(sysLanguage::get('TEXT_MAINTENANCE_NAME'))
			 ->setLabelPosition('before')
			 ->setName('maintenance_period_name')
			 ->setValue($mName);

			 $htmlMaintenanceDescription = sysLanguage::get('TEXT_MAINTENANCE_DESCRIPTION').': '. tep_draw_textarea_field('maintenance_period_description', 'soft', 30, 5, $mDescription, 'class="makeFCK"');

			 $htmlMaintenanceStartDate = htmlBase::newElement('input')
				 ->setLabel(sysLanguage::get('TEXT_MAINTENANCE_START_DATE'))
				 ->setLabelPosition('before')
				 ->setName('maintenance_period_start_date')
				 ->addClass('not_repair')
			     ->attr('id','start_date')
				 ->setValue($mSDate);

			 $htmlBeforeSend = htmlBase::newElement('checkbox')
				 ->setName('before_send')
			     ->setLabel(sysLanguage::get('PERFORM_BEFORE_SEND'))
			     ->setLabelPosition('before')
				 ->addClass('not_repair')
				 ->setChecked($beforeSend);

			 $htmlHoursBeforeSend = htmlBase::newElement('input')
				 ->setLabel(sysLanguage::get('TEXT_HOURS_BEFORE_SEND'))
				 ->setLabelPosition('before')
				 ->setName('hours_before_send')
				 ->addClass('not_repair')
				 ->setValue($hours_beforeSend);

			 $htmlAfterReturn = htmlBase::newElement('checkbox')
				 ->setName('after_return')
				 ->setLabel(sysLanguage::get('PERFORM_AFTER_RETURN'))
				 ->setLabelPosition('before')
				 ->addClass('not_repair')
				 ->setChecked($afterReturn);

			 $htmlHoursAfterReturn = htmlBase::newElement('input')
				 ->setLabel(sysLanguage::get('TEXT_HOURS_AFTER_RETURN'))
				 ->setLabelPosition('before')
				 ->addClass('not_repair')
				 ->setName('hours_after_return')
				 ->setValue($hours_afterReturn);

			 $htmlQuarantineUntilCompleted = htmlBase::newElement('checkbox')
				 ->setName('quarantine_until_completed')
				 ->setLabel(sysLanguage::get('QUARANTINE_UNTIL_COMPLETED'))
				 ->addClass('not_repair')
				 ->setLabelPosition('before')
				 ->setChecked($quarantineUntilCompleted);

			 $htmlIsRepair= htmlBase::newElement('checkbox')
				 ->setName('is_repair')
				 ->addClass('isRepair')
				 ->setLabel(sysLanguage::get('IS_REPAIR'))
				 ->setLabelPosition('before')
				 ->setChecked($isRepair);

			 $htmlEmailAdmin = htmlBase::newElement('checkbox')
				 ->setName('email_admin')
				 ->addClass('not_repair')
				 ->setLabel(sysLanguage::get('EMAIL_ADMIN'))
				 ->setLabelPosition('before')
				 ->setChecked($emailAdmin);

			 $htmlMaintenanceShowNumberDays = htmlBase::newElement('input')
				 ->setLabel(sysLanguage::get('TEXT_MAINTENANCE_SHOW_DAYS'))
				 ->addClass('not_repair')
				 ->setLabelPosition('before')
				 ->setName('show_number_days')
				 ->setValue($mNumberDays);

			 $htmlMaintenanceShowNumberRentals = htmlBase::newElement('input')
				 ->setLabel(sysLanguage::get('TEXT_MAINTENANCE_SHOW_RENTALS'))
				 ->addClass('not_repair')
				 ->setLabelPosition('before')
				 ->setName('show_number_rentals')
				 ->setValue($mNumberRentals);

			 $htmlMaintenanceQuarantineNumberDays = htmlBase::newElement('input')
				 ->setLabel(sysLanguage::get('TEXT_MAINTENANCE_QUARANTINE_DAYS'))
				 ->addClass('not_repair')
				 ->setLabelPosition('before')
				 ->setName('quarantine_number_days')
				 ->setValue($qNumberDays);

			 $htmlMaintenanceQuarantineNumberRentals = htmlBase::newElement('input')
				 ->setLabel(sysLanguage::get('TEXT_MAINTENANCE_QUARANTINE_RENTALS'))
				 ->addClass('not_repair')
				 ->setLabelPosition('before')
				 ->setName('quarantine_number_rentals')
				 ->setValue($qNumberRentals);



			 $mainTable = htmlBase::newElement('table')->setCellPadding(3)->setCellSpacing(0);
			 EventManager::notify('MaintenancePeriodsAddFields', &$mainTable);

 			 $saveButton = htmlBase::newElement('button')
			 ->setType('submit')
			 ->usePreset('save');
			 $cancelButton = htmlBase::newElement('button')
			 ->usePreset('cancel')
			 ->setHref(itw_app_link(tep_get_all_get_params(array('action', 'appPage')), null, 'default', 'SSL'));


			 $infoBox->addContentRow($htmlMaintenanceName->draw());
			 $infoBox->addContentRow($htmlMaintenanceDescription);
			 $infoBox->addContentRow($htmlMaintenanceStartDate->draw());
			 $infoBox->addContentRow($htmlBeforeSend->draw());
			 $infoBox->addContentRow($htmlHoursBeforeSend->draw());
			 $infoBox->addContentRow($htmlAfterReturn->draw());
			 $infoBox->addContentRow($htmlHoursAfterReturn->draw());
			 $infoBox->addContentRow($htmlQuarantineUntilCompleted->draw());
			 $infoBox->addContentRow($htmlIsRepair->draw());
			 $infoBox->addContentRow($htmlEmailAdmin->draw());
			 $infoBox->addContentRow($htmlMaintenanceShowNumberDays->draw());
			 $infoBox->addContentRow($htmlMaintenanceShowNumberRentals->draw());
			 $infoBox->addContentRow($htmlMaintenanceQuarantineNumberDays->draw());
			 $infoBox->addContentRow($htmlMaintenanceQuarantineNumberRentals->draw());
			 $infoBox->addContentRow($mainTable->draw());

			 $infoBox->addButton($saveButton)->addButton($cancelButton);

			 break;
		default:
			if (isset($mObject) && is_object($mObject)) {
				$infoBox->setHeader('<b>' . $mObject->maintenance_period_name . '</b>');
				
				$deleteButton = htmlBase::newElement('button')
				->usePreset('delete')
				->setHref(itw_app_link(tep_get_all_get_params(array('action', 'mID')) . 'action=deleteConfirm&mID=' . $mObject->maintenance_period_id));
				$editButton = htmlBase::newElement('button')
				->usePreset('edit')
				->setHref(itw_app_link(tep_get_all_get_params(array('action', 'mID')) . 'action=edit' . '&mID=' . $mObject->maintenance_period_id, 'maintenance_periods', 'default'));
				
				$infoBox->addButton($editButton)->addButton($deleteButton);

			}
			break;
	}
?>
 <div class="pageHeading"><?php echo sysLanguage::get('HEADING_TITLE_MAINTENANCE');?></div>
 <br />
 <div style="width:60%;float:left;">
  <div class="ui-widget ui-widget-content ui-corner-all" style="width:99%;margin-right:5px;margin-left:5px;">
   <div style="width:99%;margin:5px;"><?php echo $tableGrid->draw();?></div>
  </div>
  <div style="text-align:right;"><?php
  	echo htmlBase::newElement('button')
		    ->usePreset('new')
		    ->setText('New Period')
  	        ->setHref(itw_app_link(tep_get_all_get_params(array('action', 'mID')) . 'action=edit', null, 'default', 'SSL'))
  	        ->draw();
  ?></div>
 </div>
 <div style="width:40%;float:right;"><?php echo $infoBox->draw();?></div>