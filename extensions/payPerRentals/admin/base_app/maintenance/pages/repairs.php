<?php
	$Qmaint = Doctrine_Query::create()
	->from('PayPerRentalMaintenanceRepairs pmr')
	->leftJoin('pmr.PayPerRentalMaintenance pm')
	->leftJoin('pm.ProductsInventoryBarcodes');
	if(!isset($_GET['old'])){
		$Qmaint = $Qmaint->where('repair_date = ?','0000-00-00 00:00:00');
	}else{
		$Qmaint->orderBy('repair_date DESC');
	}

	$tableGrid = htmlBase::newElement('grid')
	->usePagination(true)
	->setPageLimit((isset($_GET['limit']) ? (int)$_GET['limit']: 25))
	->setCurrentPage((isset($_GET['page']) ? (int)$_GET['page'] : 1))
	->setQuery($Qmaint);

	$tableGrid->addHeaderRow(array(
		'columns' => array(
			array('text' => sysLanguage::get('TABLE_HEADING_MAINTENANCE_REPAIR')),
			array('text' => sysLanguage::get('TABLE_HEADING_ACTION'))
		)
	));

	$maintenance = &$tableGrid->getResults();
	if ($maintenance){
		foreach($maintenance as $maint){
			$maintenanceId = $maint['pay_per_rental_maintenance_repairs_id'];

			if ((!isset($_GET['mID']) || (isset($_GET['mID']) && ($_GET['mID'] == $maintenanceId))) && !isset($cInfo)){
				$cInfo = new objectInfo($maint);
			}

			$arrowIcon = htmlBase::newElement('icon')
			->setHref(itw_app_link(tep_get_all_get_params(array('action', 'mID')) . 'mID=' . $maintenanceId));

			$onClickLink = itw_app_link(tep_get_all_get_params(array('action', 'mID')) . 'mID=' . $maintenanceId);
			if (isset($cInfo) && $maintenanceId == $cInfo->pay_per_rental_maintenance_repairs_id){
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
					array('text' => $maint['PayPerRentalMaintenance']['ProductsInventoryBarcodes']['barcode']),
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
					'action'    => itw_app_link(tep_get_all_get_params(array('action')) . 'action=saveRepairs'),
					'method'    =>  'post',
					'name'      => 'edit_maintenance'
				)
			);

			if (isset($_GET['mID'])) {
				$maintenanceRel = Doctrine_Core::getTable('PayPerRentalMaintenanceRepairs')->find($_GET['mID']);
				$infoBox->setHeader('<b>Repair</b>');
				$maintenanceMain = Doctrine_Core::getTable('PayPerRentalMaintenance')->find($maintenanceRel->pay_per_rental_maintenance_id);
			}

			$commentsMaint = htmlBase::newElement('div');
			$commentsMaint->html('');

			$infoMaint = htmlBase::newElement('textarea')
				->attr('rows', '5')
				->attr('cols','20')
				->addClass('makeFCK')
				->attr('name','comments');
			$infoMaint->html($maintenanceRel->comments);

			$priceHtml = htmlBase::newElement('input')
			->setLabel('Labour Price')
			->setLabelPosition('before')
			->setName('price')
			->setValue($maintenanceRel->price);


			$Qcheck = Doctrine_Query::create()
				->select('MAX(pay_per_rental_maintenance_repairs_parts_id) as nextId')
				->from('PayPerRentalMaintenanceRepairParts')
				->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

			$TableParts = htmlBase::newElement('table')
				->setCellPadding(3)
				->setCellSpacing(0)
				->addClass('ui-widget ui-widget-content PartsTable')
				->css(array(
					'width' => '100%'
				))
				->attr('data-next_id', $Qcheck[0]['nextId'] + 1)
				->attr('language_id', Session::get('languages_id'));

			$TableParts->addHeaderRow(array(
					'addCls' => 'ui-state-hover PartsTableHeader',
					'columns' => array(
						array('text' => '<div style="float:left;width:100px;">' .sysLanguage::get('TABLE_HEADING_PRODUCT_PART_NAME').'</div>'.
							'<div style="float:left;width:100px;">'.sysLanguage::get('TABLE_HEADING_PART_PRICE').'</div>'.
							'<div style="float:left;width:40px;">'.htmlBase::newElement('icon')->setType('insert')->addClass('insertIconHidden')->draw().
							'</div><br style="clear:both"/>'
						)
					)
				));

			$deleteIcon = htmlBase::newElement('icon')->setType('delete')->addClass('deleteIconHidden')->draw();
			$hiddenList = htmlBase::newElement('list')
				->addClass('hiddenList');

			if(isset($_GET['mID'])){
				$QParts = Doctrine_Query::create()
					->from('PayPerRentalMaintenanceRepairParts')
					->where('pay_per_rental_maintenance_repairs_id=?', $_GET['mID'])
					->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

				foreach($QParts as $iprodev){
					$prodevid = $iprodev['pay_per_rental_maintenance_repairs_parts_id'];

					$htmlPartName = htmlBase::newElement('input')
						->addClass('ui-widget-content part_name')
						->setName('parts[' . $prodevid . '][part_name]')
						->attr('size', '15')
						->val($iprodev['part_name']);

					$htmlPartPrice = htmlBase::newElement('input')
						->addClass('ui-widget-content')
						->setName('parts[' . $prodevid . '][part_price]')
						->attr('size', '15')
						->val($iprodev['part_price']);

					$divLi1 = '<div style="float:left;width:100px;">'.$htmlPartName->draw().'</div>';
					$divLi2 = '<div style="float:left;width:100px;">'.$htmlPartPrice->draw().'</div>';
					$divLi5 = '<div style="float:left;width:40px;">'.$deleteIcon.'</div>';

					$liObj = new htmlElement('li');
					$liObj->css(array(
							'font-size' => '.8em',
							'list-style' => 'none',
							'line-height' => '1.1em',
							'border-bottom' => '1px solid #cccccc',
							'cursor' => 'crosshair'
						))
						->html($divLi1.$divLi2.$divLi5.'<br style="clear:both;"/>');
					$hiddenList->addItemObj($liObj);
				}
			}
			$TableParts->addBodyRow(array(
					'columns' => array(
						array('align' => 'center', 'text' => $hiddenList->draw(),'addCls' => 'parts')
					)
				));

			$saveButton = htmlBase::newElement('button')
				->setType('submit')
				->usePreset('save');
			$cancelButton = htmlBase::newElement('button')
				->usePreset('cancel')
				->setHref(itw_app_link(tep_get_all_get_params(array('action', 'appPage')), null, 'repairs', 'SSL'));

			$infoBox->addContentRow('Comments of maintenance: '.$maintenanceMain->comments);
			$infoBox->addContentRow($priceHtml->draw());
			$infoBox->addContentRow($TableParts->draw());
			$infoBox->addContentRow($infoMaint->draw());

			$infoBox->addButton($saveButton)->addButton($cancelButton);

			break;


		default:
			if (isset($cInfo) && is_object($cInfo)) {
				$infoBox->setHeader('<b>' . $cInfo->PayPerRentalMaintenance['ProductsInventoryBarcodes']['barcode'] .'</b>');
				$infoBox->addContentRow('Comments of maintenance: '.$cInfo->PayPerRentalMaintenance['comments']);
				$editButton = htmlBase::newElement('button')->setType('submit')->usePreset('edit')
				->setHref(itw_app_link(tep_get_all_get_params(array('action', 'mID')) . 'action=edit&mID=' . $cInfo->pay_per_rental_maintenance_id,'maintenance','repairs'));

				$infoBox->addButton($editButton);
			}
			break;
	}
?>
 <div class="pageHeading"><?php echo sysLanguage::get('HEADING_TITLE_REPAIRS');?></div>
 <br />
 <div style="width:75%;float:left;">
  <div class="ui-widget ui-widget-content ui-corner-all" style="width:99%;margin-right:5px;margin-left:5px;">
   <div style="width:99%;margin:5px;"><?php echo $tableGrid->draw();?></div>
  </div>
  <div style="text-align:right;"><?php
  echo htmlBase::newElement('button')->setText('Old Repairs')
	  ->setHref(itw_app_link('appExt=payPerRentals&old=1', 'maintenance','repairs'))
	  ->draw();
  ?></div>
 </div>
 <div style="width:25%;float:right;"><?php echo $infoBox->draw();?></div>