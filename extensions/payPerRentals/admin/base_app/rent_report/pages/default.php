<?php
	$Qmaint = Doctrine_Query::create()
    ->from('Orders o')
	->leftJoin('o.OrdersProducts op')
	->leftJoin('op.OrdersProductsReservation opr')
	->leftJoin('opr.ProductsInventoryBarcodes pib')
	->leftJoin('o.OrdersAddresses oa')
	->leftJoin('o.OrdersTotal ot')
	->andWhere('o.orders_status != ?', sysConfig::get('ORDERS_STATUS_ESTIMATE_ID'))
	->andWhere('oa.address_type = ?', 'customer')
	->andWhereIn('ot.module_type', array('total', 'ot_total','tax','ot_tax'));

	//->from('BarcodeHistoryRented bhr')
	//->leftJoin('bhr.ProductsInventoryBarcodes pib')
	//->leftJoin('pib.PayPerRentalMaintenanceRepairs pmr')
	//->leftJoin('pmr.Admin a')
	//->leftJoin('pmr.PayPerRentalMaintenanceRepairParts pmrp');

	if(isset($_GET['start_date']) && !empty($_GET['start_date'])){
		$Qmaint->where('opr.start_date >= ?',$_GET['start_date']);
	}

	if(isset($_GET['end_date']) && !empty($_GET['end_date'])){
		$Qmaint->andWhere('opr.end_date <= ?',$_GET['end_date']);
	}

	$multiStore = $appExtension->getExtension('multiStore');
	if ($multiStore !== false && $multiStore->isEnabled() === true){
		$Qmaint->leftJoin('o.OrdersToStores as o2s')
		//->leftJoin('pib.ProductsInventoryBarcodesToStores pibs')
		->leftJoin('o2s.Stores s')
		->leftJoin('s.StoresFees sf')
		->andWhereIn('o2s.stores_id', Session::get('admin_showing_stores'));
	}


$tableGrid = htmlBase::newElement('newGrid')
	->usePagination(true)
	->setPageLimit((isset($_GET['limit']) ? (int)$_GET['limit'] : 25))
	->setCurrentPage((isset($_GET['page']) ? (int)$_GET['page'] : 1))
	->setQuery($Qmaint);
$gridHeaderColumns = array();
if ($multiStore !== false && $multiStore->isEnabled() === true){
	$gridHeaderColumns[] = array('text' => sysLanguage::get('TABLE_HEADING_STORE_NAME'));
	$gridHeaderColumns[] = array('text' => sysLanguage::get('TABLE_HEADING_STORE_REG_NO'));
}

$gridHeaderColumns[] = array('text' => sysLanguage::get('TABLE_HEADING_HIRE_START_DATE'));
$gridHeaderColumns[] = array('text' => sysLanguage::get('TABLE_HEADING_HIRE_END_DATE'));
if ($multiStore !== false && $multiStore->isEnabled() === true){
	$gridHeaderColumns[] = array('text' => sysLanguage::get('TABLE_HEADING_HIRE_ADMIN_NAME'));
}

$gridHeaderColumns[] = array('text' => sysLanguage::get('TABLE_HEADING_CUSTOMER_NAME'));
$gridHeaderColumns[] = array('text' => sysLanguage::get('TABLE_HEADING_CUSTOMER_GUEST_WALKIN'));
$gridHeaderColumns[] = array('text' => sysLanguage::get('TABLE_HEADING_CUSTOMER_ID_TYPE'));
$gridHeaderColumns[] = array('text' => sysLanguage::get('TABLE_HEADING_CUSTOMER_ID_NUMBER'));

$gridHeaderColumns[] = array('text' => sysLanguage::get('TABLE_HEADING_HIRE_PRICE_DAY'));
//$gridHeaderColumns[] = array('text' => sysLanguage::get('TABLE_HEADING_HIRE_DISCOUNT'));

$gridHeaderColumns[] = array('text' => sysLanguage::get('TABLE_HEADING_HIRE_TOTAL_EXCL_VAT'));
$gridHeaderColumns[] = array('text' => sysLanguage::get('TABLE_HEADING_HIRE_TOTAL_INCL_VAT'));

$gridHeaderColumns[] = array('text' => sysLanguage::get('TABLE_HEADING_HIRE_MANAGEMENT_FEES_EXCL_VAT'));
$gridHeaderColumns[] = array('text' => sysLanguage::get('TABLE_HEADING_HIRE_MANAGEMENT_FEES_INCL_VAT'));
$gridHeaderColumns[] = array('text' => sysLanguage::get('TABLE_HEADING_HIRE_MARKETING_FEES_EXCL_VAT'));
$gridHeaderColumns[] = array('text' => sysLanguage::get('TABLE_HEADING_HIRE_MARKETING_FEES_INCL_VAT'));



$limitField = htmlBase::newElement('selectbox')
	->setName('limit')
	->setLabel('Hires per Page: ')
	->setLabelPosition('before');

$limitField->addOption('25','25');
$limitField->addOption('100','100');
$limitField->addOption('250','250');

if (isset($_GET['limit']) && !empty($_GET['limit'])){
	$limitField->selectOptionByValue($_GET['limit']);
}

$searchForm = htmlBase::newElement('form')
	->attr('name', 'search')
	->attr('id', 'searchForm')
	->attr('action', itw_app_link('appExt=payPerRentals','rent_report', 'default'))
	->attr('method', 'get');

$startdateField = htmlBase::newElement('input')
	->setName('start_date')
	->setLabel('Start Date: ')
	->setLabelPosition('before')
	->setId('start_date');

if (isset($_GET['start_date']) && !empty($_GET['start_date'])){
	$startdateField->val($_GET['start_date']);
}

$enddateField = htmlBase::newElement('input')
	->setName('end_date')
	->setLabel('End Date: ')
	->setLabelPosition('before')
	->setId('end_date');

if (isset($_GET['end_date']) && !empty($_GET['end_date'])){
	$enddateField->val($_GET['end_date']);
}

$submitButton = htmlBase::newElement('button')
	->setType('submit')
	->usePreset('save')
	->setText('Search');

$searchForm
->append($limitField)
->append($startdateField)
->append($enddateField)
->append($submitButton);

$tableGrid->addHeaderRow(array(
		'columns' => $gridHeaderColumns
	));


$orders = &$tableGrid->getResults();

if ($orders){
	foreach($orders as $order){
		foreach($order['OrdersProducts'] as $orderp){
			foreach($orderp['OrdersProductsReservation'] as $ores){
				$gridBodyColumns = array();

				if ($multiStore !== false && $multiStore->isEnabled() === true){
					$gridBodyColumns[] = array('text' => $order['OrdersToStores']['Stores']['stores_name']);
					$gridBodyColumns[] = array('text' => $order['OrdersToStores']['Stores']['stores_reg_number']);
				}
				$gridBodyColumns[] = array('text' => strftime(sysLanguage::getDateTimeFormat('long'), strtotime($ores['start_date'])));
				$gridBodyColumns[] = array('text' => strftime(sysLanguage::getDateTimeFormat('long'), strtotime($ores['end_date'])));
				if ($multiStore !== false && $multiStore->isEnabled() === true){
					$Qadmin = Doctrine_Query::create()
					->from('Admin')
					->where('admin_id = ?', $order['admin_id'])
					->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
					$gridBodyColumns[] = array('text' => $Qadmin[0]['admin_firstname']. ' '.$Qadmin[0]['admin_lastname']);
				}
				$gridBodyColumns[] = array('text' => $order['OrdersAddresses']['customer']['entry_name']);
				if(!empty($order['customers_room_number'])){
					$gridBodyColumns[] = array('text' => 'Guest');
				}else{
					$gridBodyColumns[] = array('text' => 'Walkin');
				}
				if(!empty($order['customers_passport'])){
					$gridBodyColumns[] = array('text' => 'Passport');
					$gridBodyColumns[] = array('text' => $order['customers_passport']);
				}elseif(!empty($order['customers_drivers_license'])){
					$gridBodyColumns[] = array('text' => 'Driver License');
					$gridBodyColumns[] = array('text' => $order['customers_drivers_license']);
				}else{
					$gridBodyColumns[] = array('text' => 'None');
					$gridBodyColumns[] = array('text' => 'Room Number: '.$order['customers_room_number']);
				}

					$QPricePerRentalProducts = Doctrine_Query::create()
					->from('ProductsPayPerRental ppr')
					//->leftJoin('ppr.ProductsPayPerRentalDiscounts pprd')
					->leftJoin('ppr.PricePerRentalPerProducts pprp')
					->leftJoin('pprp.PricePayPerRentalPerProductsDescription pprpd')
					->where('ppr.products_id = ?', $orderp['products_id'])
					->andWhere('pprpd.language_id=?', Session::get('languages_id'))
					->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

					$priceTable = htmlBase::newElement('table')
						->setCellPadding(3)
						->setCellSpacing(0)
						->attr('align', 'center');

					foreach($QPricePerRentalProducts as $iPrices){
						$priceHolder = htmlBase::newElement('span')
							->css(array(
								'font-size' => '1.3em',
								'font-weight' => 'bold'
							))
							->html($currencies->format($iPrices['PricePerRentalPerProducts']['price']));

						$perHolder = htmlBase::newElement('span')
							->css(array(
								'white-space' => 'nowrap',
								'font-size' => '1.1em',
								'font-weight' => 'bold'
							))
							->html($iPrices['PricePerRentalPerProducts']['PricePayPerRentalPerProductsDescription'][0]['price_per_rental_per_products_name']);

						$priceTable->addBodyRow(array(
								'columns' => array(
									array('addCls' => 'main', 'align' => 'right', 'text' => $priceHolder->draw()),
									array('addCls' => 'main', 'align' => 'left', 'text' => $perHolder->draw())
								)
						));
					}

					$gridBodyColumns[] = array('text' => $priceTable->draw());
				    $otValueTotal = 0;
				    $otValueTax = 0;
					foreach($order['OrdersTotal'] as $otInfo){
						$moduleType = $otInfo['module_type'];

						if ($moduleType == 'total' || $moduleType == 'ot_total'){
							$otValueTotal = $otInfo['value'];
						}

						if ($moduleType == 'tax' || $moduleType == 'ot_tax'){
							$otValueTax = $otInfo['value'];
						}
					}

					$gridBodyColumns[] = array('text' => $currencies->format($otValueTotal - $otValueTax));
					$gridBodyColumns[] = array('text' => $currencies->format($otValueTotal));

					$ManagementFeePercent = (float)$order['OrdersToStores']['Stores']['StoresFees']['fee_management'];
					$MarketingFeePercent = (float)$order['OrdersToStores']['Stores']['StoresFees']['fee_marketing'];

					$gridBodyColumns[] = array('text' => $ManagementFeePercent * ($otValueTotal - $otValueTax));
					$gridBodyColumns[] = array('text' => $ManagementFeePercent * $otValueTotal);
					$gridBodyColumns[] = array('text' => $MarketingFeePercent * ($otValueTotal - $otValueTax));
					$gridBodyColumns[] = array('text' => $MarketingFeePercent * $otValueTotal);


					$tableGrid->addBodyRow(array(
							'rowAttr' => array(
								'data-orders_products_reservation_id' => $ores['orders_products_reservations_id']
							),
							'columns' => $gridBodyColumns
					));
				//break;
			}
			//break;
		}
		//break;
	}
}

?>
<div><?php
	echo $searchForm->draw();
	?></div>
<br />
<div class="ui-widget ui-widget-content ui-corner-all" style="margin-right:5px;margin-left:5px;">
	<div style="margin:5px;"><?php echo $tableGrid->draw();?></div>
</div>
