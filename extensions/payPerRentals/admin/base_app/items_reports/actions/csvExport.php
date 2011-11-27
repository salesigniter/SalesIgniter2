<?php
require(sysConfig::getDirFsCatalog() . 'includes/classes/FileParser/csv.php');

$FileObj = new FileParserCsv(sysConfig::getDirFsCatalog() . 'admin/csv_export/itemsReport.csv', 'w+');
$csvRow = array();
$csvRow[] = 'Barcode';
$csvRow[] = 'Barcode Type';
$csvRow[] = 'Manufacturer';
$csvRow[] = 'Last Maintenance Date';
$csvRow[] = 'Last Maintenance Type';
$csvRow[] = 'Status';
$csvRow[] = 'End Date of Last Rent';

$FileObj->addRow($csvRow);

$Qmaint = Doctrine_Query::create()
	->from('Products p')
	->leftJoin('p.ProductsInventory pi')
	->leftJoin('pi.ProductsInventoryBarcodes pib')
	->leftJoin('pib.BarcodeHistoryRented bhr')
	->leftJoin('p.Manufacturers m');

$multiStore = $appExtension->getExtension('multiStore');
if ($multiStore !== false && $multiStore->isEnabled() === true){
	$Qmaint->leftJoin('pib.ProductsInventoryBarcodesToStores pibs')
		->andWhereIn('pibs.inventory_store_id', Session::get('admin_showing_stores'));
}

if(isset($_GET['start_date']) && !empty($_GET['start_date'])){
	$Qmaint->where('bhr.last_maintenance_date >= ?',$_GET['start_date']);
}

if(isset($_GET['end_date'])&& !empty($_GET['end_date'])){
	$Qmaint->andWhere('bhr.last_maintenance_date <= ?',$_GET['end_date']);
}

if(isset($_GET['status']) && $_GET['status'] != 'All'){
	$Qmaint->andWhere('pib.status = ?',$_GET['status']);
}

if(isset($_GET['maintenance_type']) && $_GET['maintenance_type'] != '0'){
	$Qmaint->andWhere('bhr.last_maintenance_type = ?', $_GET['maintenance_type']);
}

$products = $Qmaint->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

if ($products){
	foreach($products as $product){
		foreach($product['ProductsInventory'] as $inv){
			foreach($inv['ProductsInventoryBarcodes'] as $pib){
				$mId = $pib['barcode_id'];
				$model = $product['products_model'];
				$manufacturer = $product['Manufacturers']['manufacturers_name'];
				$last_maintenance = $pib['BarcodeHistoryRented'][0]['last_maintenance_date'];
				$QPayPerRentalMaintenancePeriods = Doctrine_Core::getTable('PayPerRentalMaintenancePeriods')->find($pib['BarcodeHistoryRented'][0]['last_maintenance_type']);
				if($QPayPerRentalMaintenancePeriods){
					$last_maintenance_type = $QPayPerRentalMaintenancePeriods->maintenance_period_name;
				}else{
					$last_maintenance_type = 'None';
				}

				$Qreservations = Doctrine_Query::create()
					->from('OrdersProductsReservation opr')
					->leftJoin('opr.ProductsInventoryBarcodes ib')
					->where('ib.barcode_id = ?', $mId)
					->andWhereIn('opr.rental_state', array('out','reserved'))
					->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

				$status = 'Available';
				$date_return = 'Not Applicable';
				if($pib['status'] == 'M'){
					$status = 'Maintenance';
				}else{
					if(isset($Qreservations[0])){
						if($Qreservations[0]['rental_state'] == 'reserved'){
							$status = 'Reserved';
						}else{
							$status = 'On Hire';
						}
						$date_return = $Qreservations[0]['end_date'];
					}
				}

				$csvRow = array();
				$csvRow[] = $pib['barcode'];
				$csvRow[] = is_null($pib['barcode_type'])?'None':$pib['barcode_type'];
				$csvRow[] = $manufacturer;
				$csvRow[] = $model;
				$csvRow[] = (isset($last_maintenance) && $last_maintenance != '0000-00-00 00:00:00')?strftime(sysLanguage::getDateFormat('long'), strtotime($last_maintenance)):'None';
				$csvRow[] = (isset($last_maintenance_type))?$last_maintenance_type:'';
				$csvRow[] = $status;
				$csvRow[] = ($date_return != 'Not Applicable'?strftime(sysLanguage::getDateFormat('long'), strtotime($date_return)):$date_return);

				$FileObj->addRow($csvRow);
			}
		}
	}
}

EventManager::attachActionResponse(itw_app_link('action=downloadReport&report=itemsReport&appExt=payPerRentals', 'items_reports', 'default'), 'redirect');
?>