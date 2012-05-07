<?php
$labelTypes = array(
	array(
		'id'   => '5164',
		'text' => 'Avery 5164'
	),
	array(
		'id'   => 'pinfo_html',
		'text' => 'Product Info HTML'
	),
	array(
		'id'   => 'barcodes',
		'text' => 'Barcodes'
	)
);
function buildPrintLabelTable() {
	global $labelTypes;
	$printButton = htmlBase::newElement('button')->setText(sysLanguage::get('TEXT_BUTTON_PRINT'))
		->attr('id', 'printLabels');

	$labelTableContainer = htmlBase::newElement('table')
		->setCellPadding(3)
		->setCellSpacing(0)
		->css('width', '95%');

	$labelTable = htmlBase::newElement('table')
		->setCellPadding(3)
		->setCellSpacing(0);

	if (!isset($_GET['product_id'])){
		$labelTable->disable(true);
	}

	$labelTable->addBodyRow(array(
		'columns' => array(
			array(
				'addCls'  => 'main',
				'colspan' => 2,
				'text'    => '<b>Print Labels</b>'
			)
		)
	));

	$labelTable->addBodyRow(array(
		'columns' => array(
			array(
				'addCls' => 'main',
				'text'   => 'Label Type:'
			),
			array(
				'addCls' => 'main',
				'text'   => tep_draw_pull_down_menu('label_type', $labelTypes, '', 'id="labelsType"')
			)
		)
	));

	$labelTable->addBodyRow(array(
		'columns' => array(
			array(
				'addCls'  => 'main',
				'colspan' => 2,
				'text'    => '<input type="checkbox" name="use_dymo" value="1"> Use Dymo Label Printer'
			)
		)
	));

	$labelTable->addBodyRow(array(
		'columns' => array(
			array(
				'addCls'  => 'main',
				'colspan' => 2,
				'text'    => $printButton
			)
		)
	));

	$labelTableContainer->addBodyRow(array(
		'columns' => array(
			array(
				'addCls' => 'main',
				'text'   => ''
			),
			array(
				'addCls' => 'main',
				'align'  => 'right',
				'text'   => $labelTable
			)
		)
	));
	return $labelTableContainer;
}

function buildInventoryCalanderTable($settings) {
	global $appExtension;

	$purchaseType = $settings['purchaseType'];

	$calanderTable = htmlBase::newElement('table')
		->setCellPadding(2)
		->setCellSpacing(0)
		->css(array(
		'margin-left'  => 'auto',
		'margin-right' => 'auto'
	));

	$inventoryReports = htmlBase::newElement('button')
		->setHref(itw_app_link('appExt=payPerRentals&purchase_type_field=' . $purchaseType . '&productsID=' . $_GET['product_id'], 'reservations_reports', 'default'), false, '_blank')
		->setText('View Inventory Calendar');

	$calanderTable->addBodyRow(array(
		'columns' => array(
			array(
				'addCls' => 'main',
				'text'   => $inventoryReports->draw()
			)
		)
	));
	return $calanderTable;
}

function getAttributeInventoryTabContent($settings) {
	global $appExtension;
	$dataSet = $settings['dataSet'];
	$purchaseType = $settings['purchaseType'];
	$productId = $settings['productId'];
	$tabContent = '';
	$extAttributes = $appExtension->getExtension('attributes');

	if (isset($_GET['product_id'])){
		$attributesContainer = htmlBase::newElement('div')
			->addClass('main');

		$ProductsAttributes = attributesUtil::getAttributes((int)$_GET['product_id'], null, null, $purchaseType, null);
		//print_r($ProductsAttributes);
		$Attributes = attributesUtil::organizeAttributeArray($ProductsAttributes);
		//echo 'ooo'.print_r($Attributes);
		//itwExit();
		$hasOptions = false;
		foreach($Attributes as $optionId => $aInfo){
			$input = htmlBase::newElement('selectbox')
				->css('margin-right', '.75em')
				->setName('attribute_inventory_option[' . $optionId . ']')
				->addClass('attributeStockOption')
				->setLabel($aInfo['options_name'])
				->setLabelPosition('before')
				->setLabelSeparator(':&nbsp;');

			foreach($aInfo['ProductsOptionsValues'] as $options){
				$input->addOption($options['options_values_id'], $options['options_values_name']);
				$hasOptions = true;
			}
			$attributesContainer->append($input);
		}

		$addButton = htmlBase::newElement('button')
			->attr('data-purchase_type', $purchaseType)
			->usePreset('install')
			->setText('Add')
			->addClass('attributeStockAddButton');
		if ($hasOptions){
			$attributesContainer->append($addButton);
		}
	}

	if (isset($attributesContainer)){
		$tabContent .= $attributesContainer->draw() .
			htmlBase::newElement('hr')->draw() .
			htmlBase::newElement('br')->draw();
	}

	$attributeInventoryTables = array();
	if (!empty($dataSet)){
		if (isset($dataSet['quantity'])){
			foreach($dataSet['quantity']['inventoryItems'] as $aID_string => $aInfo){
				$attributeInventoryTables[] = $extAttributes->pagePlugin->getInventoryTable(array(
					'productId'    => $productId,
					'purchaseType' => $purchaseType,
					'trackMethod'  => 'quantity',
					'dataSet'      => $dataSet['quantity']['inventoryItems'][$aID_string],
					'options'      => attributesUtil::splitStringToArray($aID_string)
				));
			}
		}

		if (isset($dataSet['barcode'])){
			foreach($dataSet['barcode']['inventoryItems'] as $aID_string => $aInfo){
				$attributeInventoryTables[] = $extAttributes->pagePlugin->getInventoryTable(array(
					'productId'    => $productId,
					'purchaseType' => $purchaseType,
					'trackMethod'  => 'barcode',
					'dataSet'      => $dataSet['barcode']['inventoryItems'][$aID_string],
					'options'      => attributesUtil::splitStringToArray($aID_string)
				));
			}
		}
	}
	return $tabContent . '<div class="attributesInventoryTables">' . implode('', $attributeInventoryTables) . '</div>';
}

?>