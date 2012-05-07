<?php
$barcodeID = (int)$_GET['barcode_id'];
if (isset($_GET['barcodeTypeSelectBox']) && $_GET['barcodeTypeSelectBox'] != 'None'){
	Doctrine_Query::create()
		->update('ProductsInventoryBarcodes')
		->set('type', '?', $_GET['barcodeTypeSelectBox'])
		->where('barcode_id = ?', $barcodeID)
		->execute();
}
EventManager::attachActionResponse(array(
	'success' => true
), 'json');
?>