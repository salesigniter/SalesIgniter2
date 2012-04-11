<?php
$productsTable = htmlBase::newElement('table')
	->setCellPadding(3)
	->setCellSpacing(0)
	->addClass('productTable')
	->css('width', '100%');

$buttonAdd = htmlBase::newElement('button')
	->addClass('insertProductIcon')
	->attr('data-product_entry_method', sysConfig::get('EXTENSION_ORDER_CREATOR_PRODUCT_FIND_METHOD'))
	->setText('Add Product To Order');

$productTableHeaderColumns = array(
	array(
		'colspan' => 2,
		'text'    => sysLanguage::get('TABLE_HEADING_PRODUCTS')
	),
	array('text' => 'Barcode'),
	array('text' => sysLanguage::get('TABLE_HEADING_PRODUCTS_MODEL')),
	array('text' => sysLanguage::get('TABLE_HEADING_TAX')),
	array('text' => sysLanguage::get('TABLE_HEADING_PRICE_EXCLUDING_TAX')),
	array('text' => sysLanguage::get('TABLE_HEADING_PRICE_INCLUDING_TAX')),
	array('text' => sysLanguage::get('TABLE_HEADING_TOTAL_EXCLUDING_TAX')),
	array('text' => sysLanguage::get('TABLE_HEADING_TOTAL_INCLUDING_TAX')),
	array('text' => $buttonAdd->draw())
);

foreach($productTableHeaderColumns as $i => $cInfo){
	$productTableHeaderColumns[$i]['addCls'] = 'main ui-widget-header';
	if ($i > 0){
		$productTableHeaderColumns[$i]['css'] = array(
			'border-left' => 'none'
		);
	}

	if ($i > 1){
		$productTableHeaderColumns[$i]['align'] = 'right';
	}
}

$productsTable->addHeaderRow(array(
	'columns' => $productTableHeaderColumns
));

foreach($Editor->ProductManager->getContents() as $orderedProduct){
	//$productsName = '<input type="text" style="width:90%" class="ui-widget-content" name="product[' . $orderedProductId . '][name]" value="' . $orderedProduct->getName() . '">';

	$bodyColumns = array(
		array(
			'align' => 'right',
			'text'  => $orderedProduct->getQuantityEdit()
		),
		array('text' => $orderedProduct->getNameEdit()),
		array('text' => $orderedProduct->getBarcodeEdit()),
		array('text' => $orderedProduct->getModel()),
		array(
			'align' => 'right',
			'text'  => $orderedProduct->getTaxRateEdit()
		),
		array(
			'align' => 'right',
			'text'  => $orderedProduct->getPriceEdit()
		),
		array(
			'align' => 'right',
			'text'  => $orderedProduct->getPriceEdit(false, true)
		),
		array(
			'align' => 'right',
			'text'  => $orderedProduct->getPriceEdit(true, false)
		),
		array(
			'align' => 'right',
			'text'  => $orderedProduct->getPriceEdit(true, true)
		),
		array(
			'align' => 'right',
			'text'  => '<span class="ui-icon ui-icon-closethick deleteProductIcon"></span>'
		)
	);

	$sizeOf = sizeof($bodyColumns);
	foreach($bodyColumns as $idx => $colInfo){
		$bodyColumns[$idx]['addCls'] = 'ui-widget-content';
		$bodyColumns[$idx]['valign'] = 'top';
		$bodyColumns[$idx]['css'] = array(
			'border-top' => 'none'
		);

		if ($idx > 0 && $idx < $sizeOf){
			$bodyColumns[$idx]['css']['border-left'] = 'none';
		}
	}
	$bodyColumns[2]['addCls'] .= ' barcodeCol';

	$productsTable->addBodyRow(array(
		'attr'    => array(
			'data-id' => $orderedProduct->getId()
		),
		'columns' => $bodyColumns
	));
}
echo $productsTable->draw();
?>