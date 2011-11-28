<?php
require(sysConfig::getDirFsCatalog() . 'includes/classes/FileParser/csv.php');

$FileObj = new FileParserCsv(sysConfig::getDirFsCatalog() . 'admin/csv_export/maintenanceReport.csv', 'w+');
$csvRow = array();
$csvRow[] = 'Barcode';
$csvRow[] = 'Barcode Type';
$csvRow[] = 'Maintenance Type';
$csvRow[] = 'Maintenance Person';
$csvRow[] = 'Maintenance Date';
$csvRow[] = 'Price';
$csvRow[] = 'Used parts';
$csvRow[] = 'Parts price';

$FileObj->addRow($csvRow);

$Qmaint = Doctrine_Query::create()
	->from('BarcodeHistoryRented bhr')
	->leftJoin('bhr.ProductsInventoryBarcodes pib')
	->leftJoin('pib.PayPerRentalMaintenanceRepairs pmr')
	->leftJoin('pmr.Admin a')
	->leftJoin('pmr.PayPerRentalMaintenanceRepairParts pmrp');

if(isset($_GET['start_date']) && !empty($_GET['start_date'])){
	$Qmaint->where('bhr.last_maintenance_date >= ?',$_GET['start_date']);
}

if(isset($_GET['end_date']) && !empty($_GET['end_date'])){
	$Qmaint->andWhere('bhr.last_maintenance_date <= ?',$_GET['end_date']);
}

$multiStore = $appExtension->getExtension('multiStore');
if ($multiStore !== false && $multiStore->isEnabled() === true){
	$Qmaint->leftJoin('pib.ProductsInventoryBarcodesToStores pibs')
		->andWhereIn('pibs.inventory_store_id', Session::get('admin_showing_stores'));
}

$maintenances = $Qmaint->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
if ($maintenances){
	foreach($maintenances as $maintenance){
		$mId = $maintenance['barcode_id'];
		$eventType = '8-points check';
		$price = 0;
		$priceParts = 0;
		$admin = '';
		$parts = '';
		foreach($maintenance['ProductsInventoryBarcodes']['PayPerRentalMaintenanceRepairs'] as $repair){
			$price += $repair['price'];
			foreach($repair['PayPerRentalMaintenanceRepairParts'] as $part){
				$priceParts += $part['part_price'];
				$parts .= $part['part_name'].'; ';
			}
			$admin .= $repair['Admin']['admin_firstname'].' '.$repair['Admin']['admin_lastname'].'<br/>';
		}

		$QPPRMaintenancePeriods = Doctrine_Core::getTable('PayPerRentalMaintenancePeriods')->find($maintenance['last_maintenance_type']);
		if($QPPRMaintenancePeriods){
			$eventType = $QPPRMaintenancePeriods->maintenance_period_name;
		}

		$csvRow = array();
		$csvRow[] = $maintenance['ProductsInventoryBarcodes']['barcode'];
		$csvRow[] = is_null($maintenance['ProductsInventoryBarcodes']['barcode_type'])?'None':$maintenance['ProductsInventoryBarcodes']['barcode_type'];
		$csvRow[] = $eventType;
		$csvRow[] = $admin;
		$csvRow[] = strftime(sysLanguage::getDateFormat('long'), strtotime($maintenance['last_maintenance_date']));
		$csvRow[] = $currencies->format($price);
		$csvRow[] = $parts;
		$csvRow[] = $currencies->format($priceParts);
		$FileObj->addRow($csvRow);
	}
}
EventManager::attachActionResponse(itw_app_link('action=downloadReport&report=maintenanceReport&appExt=payPerRentals', 'maintenance_reports', 'default'), 'redirect');
?>