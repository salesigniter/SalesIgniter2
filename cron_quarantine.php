<?php
 require('includes/application_top.php');
$Qmaint = Doctrine_Query::create()
	->from('BarcodeHistoryRented bhr')
	->where('bhr.number_rents >= ?',sysConfig::get('EXTENSION_PAY_PER_RENTALS_HOW_MANY_RENTS_QUARANTINE'))
	->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

foreach($Qmaint as $bhr){
	$ProductBarcode = Doctrine_Core::getTable('ProductsInventoryBarcodes')->find($bhr['barcode_id']);
	$ProductBarcode->status = 'Q';
	$ProductBarcode->save();
}
require('includes/application_bottom.php');
?>