<?php
$QRentals = Doctrine_Query::create()
	->from('OrdersProductsRentals')
	->whereIn('orders_products_rentals_id', $_GET['rental'])
	->execute();

if ($_GET['printMethod'] == 'dymo'){
	$labelType = $_GET['labelType'];

	$labelInfo = array(
		'xmlData' => file_get_contents(sysConfig::getDirFsCatalog() . 'ext/dymo_labels/' . $labelType . '.label'),
		'data'    => array()
	);

	foreach($QRentals as $rInfo){
		if ($labelType == '8160-b'){
			$labelInfo['data'][] = array(
				'Barcode'     => $rInfo->ProductsInventoryBarcodes->barcode,
				'BarcodeType' => sysConfig::get('SYSTEM_BARCODE_FORMAT')
			);
		}
		elseif ($labelType == '8160-s') {
			$Address = $rInfo->OrdersProducts->Orders->OrdersAddresses['customer']->toArray();
			$labelInfo['data'][] = array(
				'Address' => tep_address_format($Address['entry_format_id'], $Address, false)
			);
		}
		elseif ($labelType == '8164') {
			$labelInfo['data'][] = array(
				'ProductsName'         => $rInfo->OrdersProducts->products_name,
				'Barcode'              => $rInfo->ProductsInventoryBarcodes->barcode,
				'BarcodeType'          => sysConfig::get('SYSTEM_BARCODE_FORMAT'),
				'ProductsDescription'  => $rInfo->OrdersProducts->Products->ProductsDescription[Session::get('languages_id')]->products_description,
			);
		}
	}

	EventManager::attachActionResponse(array(
		'success'   => true,
		'labelInfo' => $labelInfo
	), 'json');
}
else {
	foreach($QRentals as $rInfo){
		$labelInfo['data'][] = array(
			'products_name'        => $rInfo->OrdersProducts->products_name,
			'barcode'              => $rInfo->ProductsInventoryBarcodes->barcode,
			'barcode_type'         => sysConfig::get('SYSTEM_BARCODE_FORMAT'),
			'barcode_id'           => $rInfo->ProductsInventoryBarcodes->barcode_id,
			'products_description' => $rInfo->OrdersProducts->Products->ProductsDescription[Session::get('languages_id')]->products_description,
			'customers_address'    => $rInfo->OrdersProducts->Orders->OrdersAddresses['customer']->toArray()
		);
	}

	if ($_GET['printMethod'] == 'spreadsheet'){
		require(sysConfig::getDirFsCatalog() . 'includes/classes/FileParser/csv.php');
		$File = new FileParserCsv('temp');
		$File->addRow(array(
			'ProductsName',
			'Barcode',
			'BarcodeType',
			'BarcodeId',
			'ProductsDescription',
			'Address'
		));
		$sep = ';';
		switch($_GET['field_separator']){
			case 'tab'       : $sep = '	';
			case 'semicolon' : $sep = ';';
			case 'colon'     : $sep = ':';
			case 'comma'     : $sep = ',';
		}
		$File->setCsvControl($sep);
		foreach($labelInfo['data'] as $lInfo){
			$lInfo['customers_address'] = strip_tags(str_replace('&nbsp;', ' ', tep_address_format(tep_get_address_format_id($lInfo['customers_address']['entry_country_id']), $lInfo['customers_address'], false)));
			$lInfo['products_description'] = wordwrap(strip_tags(str_replace('&nbsp;', ' ', $lInfo['products_description'])), 70);
			$File->addRow($lInfo);
		}
		$File->output();
	}else{
		require(sysConfig::getDirFsAdmin() . 'includes/classes/pdf_labels.php');
		$LabelMaker = new PDF_Labels();
		$LabelMaker->setData($labelInfo['data']);
		$LabelMaker->setLabelsType($_GET['labelType']);
		$LabelMaker->setStartLocation($_GET['row_start'], $_GET['col_start']);
		$LabelMaker->buildPDF();
	}
}

