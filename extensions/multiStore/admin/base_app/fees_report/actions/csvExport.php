<?php
require(sysConfig::getDirFsCatalog() . 'includes/classes/FileParser/csv.php');

$FileObj = new FileParserCsv(sysConfig::getDirFsCatalog() . 'admin/csv_export/feesReport.csv', 'w+');
$csvRow = array();
$csvRow[] = 'Store ID';
$csvRow[] = 'VAT Number';
$csvRow[] = 'Reg Number';
$csvRow[] = 'Address';
$csvRow[] = 'Total Sales';

$csvRow[] = 'Royalty Not Billed';
$csvRow[] = 'Management Not Billed';
$csvRow[] = 'Marketing Not Billed';
$csvRow[] = 'Labor Not Billed';
$csvRow[] = 'Parts Not Billed';

$csvRow[] = 'Royalty Billed';
$csvRow[] = 'Management Billed';
$csvRow[] = 'Marketing Billed';
$csvRow[] = 'Labor Billed';
$csvRow[] = 'Parts Billed';

$csvRow[] = 'Royalty Paid';
$csvRow[] = 'Management Paid';
$csvRow[] = 'Marketing Paid';
$csvRow[] = 'Labor Paid';
$csvRow[] = 'Parts Paid';

$FileObj->addRow($csvRow);

$MultiStores = $appExtension->getExtension('multiStore');
foreach($MultiStores->getStoresArray() as $sInfo){
	$csvRow = array();
	$QOrderTotal = Doctrine_Query::create()
		->select('SUM(ot.value) as total')
		->from('Orders o')
		->leftJoin('o.OrdersTotal ot')
		->leftJoin('o.OrdersToStores o2s')
		->where('o2s.stores_id = ?', $sInfo['stores_id'])
		->andWhere('ot.module_type = ?', 'total');

	if (isset($_GET['date_from']) && isset($_GET['date_to'])){
		$QOrderTotal->andWhere('o.date_purchased >= ?', $_GET['date_from'])
			->andWhere('o.date_purchased <= ?', $_GET['date_to']);
	}

	$OrderTotal = $QOrderTotal->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

	$Total = $OrderTotal[0]['total'];

	$QBilled = Doctrine_Query::create()
		->select(
		'SUM(fee_royalty + fee_royalty_discount) as Royalty, ' .
			'SUM(fee_management + fee_management_discount) as Management, ' .
			'SUM(fee_marketing + fee_marketing_discount) as Marketing, ' .
			'SUM(fee_labor + fee_labor_discount) as Labor, ' .
			'SUM(fee_parts + fee_parts_discount) as Parts'
	)
		->from('StoresFeesInvoices')
		->where('stores_id = ?', $sInfo['stores_id'])
		->andWhere('paid = ?', '0');

	if (isset($_GET['date_from']) && isset($_GET['date_to'])){
		$QBilled->andWhere('date_added >= ?', strtotime($_GET['date_from']))
			->andWhere('date_added <= ?', strtotime($_GET['date_to']));
	}

	$Billed = $QBilled->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

	$RoyaltyBilled = $Billed[0]['Royalty'];
	$ManagementBilled = $Billed[0]['Management'];
	$MarketingBilled = $Billed[0]['Marketing'];
	$LaborBilled = $Billed[0]['Labor'];
	$PartsBilled = $Billed[0]['Parts'];

	$QPaid = Doctrine_Query::create()
		->select(
		'SUM(fee_royalty + fee_royalty_discount) as Royalty, ' .
			'SUM(fee_management + fee_management_discount) as Management, ' .
			'SUM(fee_marketing + fee_marketing_discount) as Marketing, ' .
			'SUM(fee_labor + fee_labor_discount) as Labor, ' .
			'SUM(fee_parts + fee_parts_discount) as Parts'
	)
		->from('StoresFeesInvoices')
		->where('stores_id = ?', $sInfo['stores_id'])
		->andWhere('paid = ?', '1');

	if (isset($_GET['date_from']) && isset($_GET['date_to'])){
		$QPaid->andWhere('date_added >= ?', strtotime($_GET['date_from']))
			->andWhere('date_added <= ?', strtotime($_GET['date_to']));
	}

	$Paid = $QPaid->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

	$RoyaltyPaid = $Paid[0]['Royalty'];
	$ManagementPaid = $Paid[0]['Management'];
	$MarketingPaid = $Paid[0]['Marketing'];
	$LaborPaid = $Paid[0]['Labor'];
	$PartsPaid = $Paid[0]['Parts'];



	$RoyaltyFeePercent = (float)$sInfo['StoresFees']['fee_royalty'];
	$ManagementFeePercent = (float)$sInfo['StoresFees']['fee_management'];
	$MarketingFeePercent = (float)$sInfo['StoresFees']['fee_marketing'];
	$LaborFeePercent = (float)$sInfo['StoresFees']['fee_labor'];
	$PartsFeePercent = (float)$sInfo['StoresFees']['fee_parts'];

	$RoyaltyFeesOwed = ($Total * $RoyaltyFeePercent);
	if ($RoyaltyFeesOwed > 0){
		$RoyaltyFeesOwed -= $RoyaltyBilled;
		$RoyaltyFeesOwed -= $RoyaltyPaid;
	}

	$ManagementFeesOwed = ($Total * $ManagementFeePercent);
	if ($ManagementFeesOwed > 0){
		$ManagementFeesOwed -= $ManagementBilled;
		$ManagementFeesOwed -= $ManagementPaid;
	}

	$MarketingFeesOwed = ($Total * $MarketingFeePercent);
	if ($MarketingFeesOwed > 0){
		$MarketingFeesOwed -= $MarketingBilled;
		$MarketingFeesOwed -= $MarketingPaid;
	}

	$LaborFeesOwed = ($Total * $LaborFeePercent);
	if ($LaborFeesOwed > 0){
		$LaborFeesOwed -= $LaborBilled;
		$LaborFeesOwed -= $LaborPaid;
	}

	$PartsFeesOwed = ($Total * $PartsFeePercent);
	if ($PartsFeesOwed > 0){
		$PartsFeesOwed -= $PartsBilled;
		$PartsFeesOwed -= $PartsPaid;
	}

	$csvRow[] = $sInfo['stores_id'];
	$csvRow[] = $sInfo['stores_vat_number'];
	$csvRow[] = $sInfo['stores_reg_number'];
	$csvRow[] = $sInfo['stores_street_address'];
	$csvRow[] = $currencies->format($Total);

	$csvRow[] =  $currencies->format($RoyaltyFeesOwed);
	$csvRow[] =  $currencies->format($ManagementFeesOwed);
	$csvRow[] =  $currencies->format($MarketingFeesOwed);
	$csvRow[] =  $currencies->format($LaborFeesOwed);
	$csvRow[] =  $currencies->format($PartsFeesOwed);

	$csvRow[] =  $currencies->format($RoyaltyBilled);
	$csvRow[] =  $currencies->format($ManagementBilled);
	$csvRow[] =  $currencies->format($MarketingBilled);
	$csvRow[] =  $currencies->format($LaborBilled);
	$csvRow[] =  $currencies->format($PartsBilled);

	$csvRow[] =  $currencies->format($RoyaltyPaid);
	$csvRow[] =  $currencies->format($ManagementPaid);
	$csvRow[] =  $currencies->format($MarketingPaid);
	$csvRow[] =  $currencies->format($LaborPaid);
	$csvRow[] =  $currencies->format($PartsPaid);

	$FileObj->addRow($csvRow);
}

EventManager::attachActionResponse(itw_app_link('action=downloadReport&report=feesReport&appExt=multiStore', 'fees_report', 'default'), 'redirect');
?>