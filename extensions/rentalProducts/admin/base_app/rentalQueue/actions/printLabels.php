<?php
$LabelProducts = array();
$QueueContents = $RentalQueue->getContents()->getIterator();
while($QueueContents->valid()){
	$RentalProduct = $QueueContents->current();
	if (in_array($RentalProduct->getId(), $_GET['queueItem'])){
		$bInfo = array(
			'id' => '',
			'barcode' => ''
		);
		if (isset($_GET['barcode'][$RentalProduct->getId()])){
			$Qbarcode = Doctrine_Query::create()
				->from('ProductsInventoryBarcodes')
				->where('barcode_id = ?', $_GET['barcode'][$RentalProduct->getId()])
				->fetchOne();

			$bInfo = array(
				'id' => $Qbarcode->barcode_id,
				'barcode' => $Qbarcode->barcode
			);
		}

		$LabelProducts[] = array(
			'product' => $RentalProduct,
			'barcode' => $bInfo
		);
	}
	$QueueContents->next();
}

$Address = Doctrine_Query::create()
	->from('AddressBook ab')
	->leftJoin('ab.Countries c')
	->where('ab.customers_id = ?', $_GET['cID'])
	->fetchOne()->toArray();

if ($_GET['printMethod'] == 'dymo'){
	$labelType = $_GET['labelType'];

	$labelInfo = array(
		'xmlData' => file_get_contents(sysConfig::getDirFsCatalog() . 'ext/dymo_labels/' . $labelType . '.label'),
		'data'    => array()
	);

	foreach($LabelProducts as $rInfo){
		if ($labelType == '8160-b'){
			$labelInfo['data'][] = array(
				'Barcode'     => $rInfo['barcode']['barcode'],
				'BarcodeType' => sysConfig::get('SYSTEM_BARCODE_FORMAT')
			);
		}
		elseif ($labelType == '8160-s') {
			$labelInfo['data'][] = array(
				'Address' => tep_address_format($Address['Countries']['address_format_id'], $Address, false)
			);
		}
		elseif ($labelType == '8164') {
			$labelInfo['data'][] = array(
				'ProductsName'         => $rInfo['product']->getName(),
				'Barcode'              => $rInfo['barcode']['barcode'],
				'BarcodeType'          => sysConfig::get('SYSTEM_BARCODE_FORMAT'),
				'ProductsDescription'  => wordwrap(strip_tags($rInfo['product']->getDescription()), 70),
			);
		}
	}

	EventManager::attachActionResponse(array(
		'success'   => true,
		'labelInfo' => $labelInfo
	), 'json');
}
else {
	require(sysConfig::getDirFsAdmin() . 'includes/classes/pdf_labels.php');
	$LabelMaker = new PDF_Labels();

	foreach($LabelProducts as $rInfo){
		$labelInfo['data'][] = array(
			'products_name'        => $rInfo['product']->getName(),
			'barcode'              => $rInfo['barcode']['barcode'],
			'barcode_type'         => sysConfig::get('SYSTEM_BARCODE_FORMAT'),
			'barcode_id'           => $rInfo['barcode']['id'],
			'products_description' => $rInfo['product']->getDescription(),
			'customers_address'    => $Address
		);
	}

	$LabelMaker->setData($labelInfo['data']);
	$LabelMaker->setLabelsType($_GET['labelType']);
	$LabelMaker->setStartLocation($_GET['row_start'], $_GET['col_start']);
	$LabelMaker->buildPDF();
}

