<?php
$TableColumns = (isset($WidgetSettings->tableColumns) ? $WidgetSettings->tableColumns : array());

$columnsPath = realpath(__DIR__ . '/../columns');
$clientColumnsPath = sysConfig::getDirFsCatalog() . 'clientData/' . str_replace(sysConfig::getDirFsCatalog(), '', $columnsPath);

$ColumnClasses = array();
$Dir = new DirectoryIterator(__DIR__ . '/../columns');
foreach($Dir as $dInfo){
	if ($dInfo->isDot() || $dInfo->isDir() || file_exists($clientColumnsPath . '/' . $dInfo->getBasename('.php'))){
		continue;
	}
	$ClassName = 'InvoiceListingWidgetColumn' . ucfirst($dInfo->getBasename('.php'));
	if (class_exists($ClassName) === false){
		require($dInfo->getPathname());
	}

	$ColumnClasses[$dInfo->getBasename('.php')] = new $ClassName();
}

$Dir = new DirectoryIterator($clientColumnsPath);
foreach($Dir as $dInfo){
	if ($dInfo->isDot() || $dInfo->isDir()){
		continue;
	}
	$ClassName = 'InvoiceListingWidgetColumn' . ucfirst($dInfo->getBasename('.php'));
	if (class_exists($ClassName) === false){
		require($dInfo->getPathname());
	}

	$ColumnClasses[$dInfo->getBasename('.php')] = new $ClassName();
}

ksort($ColumnClasses);

$WidgetSettingsTable->addBodyRow(array(
	'columns' => array(
		array(
			'colspan' => 2,
			'text'    => '<b> Invoice Listing Products Widget Properties</b>'
		)
	)
));

$ColumnsTable = htmlBase::newGrid();

$ColumnsTable->addHeaderRow(array(
	'columns' => array(
		array('text' => 'Listing Column'),
		array('text' => 'Show'),
		array('text' => 'Display Order'),
		array('text' => 'Heading Text'),
	)
));
foreach($ColumnClasses as $ColumnClass){
	$Checkbox = htmlBase::newCheckbox()
		->setName('columns[]')
		->setValue($ColumnClass->getCode())
		->setChecked(isset($TableColumns->{$ColumnClass->getCode()}));

	$DisplayOrderInput = htmlBase::newInput()
		->setSize(3)
		->setName('display_order[' . $ColumnClass->getCode() . ']')
		->setValue((isset($TableColumns->{$ColumnClass->getCode()}) ? $TableColumns->{$ColumnClass->getCode()}->display_order : ''));

	$HeadingInput = htmlBase::newInput()
		->setName('column_properties[' . $ColumnClass->getCode() . '][heading_text]')
		->setValue((isset($TableColumns->{$ColumnClass->getCode()}) ? $TableColumns->{$ColumnClass->getCode()}->column_properties->heading_text : ''));

	$ColumnsTable->addBodyRow(array(
		'addCls' => 'noHover noSelect',
		'columns' => array(
			array('valign' => 'top', 'text' => $ColumnClass->getTitle() . '<br><label>' . $ColumnClass->getDescription() . '</label>'),
			array('align' => 'center', 'text' => $Checkbox),
			array('align' => 'center', 'text' => $DisplayOrderInput),
			array('align' => 'center', 'text' => $HeadingInput)
		)
	));
}

$WidgetSettingsTable->addBodyRow(array(
	'columns' => array(
		array(
			'colspan' => 2,
			'text'    => $ColumnsTable
		)
	)
));
