<?php
$QBarcodes = Doctrine_Query::create()
	->from('ProductsInventoryBarcodes')
	->whereIn('barcode_id', $_GET['barcodes'])
	->execute();

if ($_GET['printMethod'] == 'dymo'){
	$labelType = $_GET['labelType'];

	$labelInfo = array(
		'xmlData' => file_get_contents(sysConfig::getDirFsCatalog() . 'ext/dymo_labels/' . $labelType . '.label'),
		'data'    => array()
	);

	foreach($QBarcodes as $bInfo){
		$labelInfo['data'][] = array(
			'Barcode'     => $bInfo->barcode,
			'BarcodeType' => sysConfig::get('SYSTEM_BARCODE_FORMAT')
		);
	}

	EventManager::attachActionResponse(array(
		'success'   => true,
		'labelInfo' => $labelInfo
	), 'json');
}
else {
	require(sysConfig::getDirFsAdmin() . 'includes/classes/pdf_labels.php');
	$LabelMaker = new PDF_Labels();

	foreach($QBarcodes as $bInfo){
		$labelInfo['data'][] = array(
			'barcode'              => $bInfo->barcode,
			'barcode_type'         => sysConfig::get('SYSTEM_BARCODE_FORMAT')
		);
	}

	$LabelMaker->setData($labelInfo['data']);
	$LabelMaker->setLabelsType($_GET['labelType']);
	$LabelMaker->setStartLocation($_GET['row_start'], $_GET['col_start']);
	$LabelMaker->buildPDF();
}

/*
?>

<BarcodeObject>
	<Name>Barcode</Name>
	<ForeColor Alpha="255" Red="0" Green="0" Blue="0" />
	<BackColor Alpha="0" Red="255" Green="255" Blue="255" />
	<LinkedObjectName></LinkedObjectName>
	<Rotation>Rotation0</Rotation>
	<IsMirrored>False</IsMirrored>
	<IsVariable>False</IsVariable>
	<Text></Text>
	<Type>QRCode</Type>
	<Size>Large</Size>
	<TextPosition>None</TextPosition>
	<TextFont Family="Arial" Size="8" Bold="False" Italic="False" Underline="False" Strikeout="False" />
	<CheckSumFont Family="Arial" Size="8" Bold="False" Italic="False" Underline="False" Strikeout="False" />
	<TextEmbedding>None</TextEmbedding>
	<ECLevel>0</ECLevel>
	<HorizontalAlignment>Left</HorizontalAlignment>
	<QuietZonesPadding Left="0" Top="0" Right="0" Bottom="0" />
</BarcodeObject>

<DieCutLabel Version="8.0" Units="twips">
	<PaperOrientation>Landscape</PaperOrientation>
	<Id>Address</Id>
	<PaperName>30252 Address</PaperName>
	<DrawCommands/>
	<ObjectInfo>
		<TextObject>
			<Name>Text</Name>
			<ForeColor Alpha="255" Red="0" Green="0" Blue="0" />
			<BackColor Alpha="0" Red="255" Green="255" Blue="255" />
			<LinkedObjectName></LinkedObjectName>
			<Rotation>Rotation0</Rotation>
			<IsMirrored>False</IsMirrored>
			<IsVariable>True</IsVariable>
			<HorizontalAlignment>Left</HorizontalAlignment>
			<VerticalAlignment>Middle</VerticalAlignment>
			<TextFitMode>ShrinkToFit</TextFitMode>
			<UseFullFontHeight>True</UseFullFontHeight>
			<Verticalized>False</Verticalized>
			<StyledText/>
		</TextObject>
		<Bounds X="332" Y="150" Width="4455" Height="1260" />
	</ObjectInfo>
</DieCutLabel>
<?php
*/
