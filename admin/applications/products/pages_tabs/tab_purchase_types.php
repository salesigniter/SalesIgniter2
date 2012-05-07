<?php
function buildNormalInventoryTabs(Product $Product, PurchaseTypeBase $PurchaseType) {
	//$PurchaseType->loadData($Product->getId());
	$purchaseTypeCode = $PurchaseType->getCode();
	$invController = 'normal';

	$trackMethodTable = htmlBase::newElement('table')
		->setCellPadding(3)->setCellSpacing(0)->css('width', '98%');

	$trackMethods = $PurchaseType->getConfigData('INVENTORY_TRACK_METHODS');
	foreach($trackMethods as $methodName){
		$radioField = htmlBase::newElement('radio')
			->addClass('trackMethodButton')
			->setName('track_method[' . $invController . '][' . $purchaseTypeCode . ']')
			->setLabelPosition('after')
			->setLabelSeparator('&nbsp;')
			->val($methodName)
			->setLabel('Use ' . ucfirst($methodName) . ' Tracking')
			->setChecked($PurchaseType->getData('inventory_track_method') == $methodName);

		$trackMethodTable->addBodyRow(array(
			'columns' => array(
				array(
					'addCls' => 'main',
					'text'   => $radioField->draw()
				)
			)
		));
	}

	EventManager::notify('NewProductAddTrackMethods', $invController, &$PurchaseType, &$trackMethodTable);

	$inputTable = htmlBase::newElement('table')
		->setCellPadding(2)
		->setCellSpacing(0)
		->css('width', '100%');

	$inputTable->addBodyRow(array(
		'columns' => array(
			array('text' => $trackMethodTable->draw())
		)
	));

	$InvTabs = htmlBase::newElement('tabs')
		->addClass('PurchaseTypeInventoryTabs_normal')
		->setId('PurchaseType' . ucfirst($purchaseTypeCode) . 'InventoryTabs_normal');

	foreach($trackMethods as $methodName){
		$function = 'buildNormalInventory' . ucfirst($methodName) . 'Table';
		$InvTabs
			->addTabHeader('PurchaseType' . ucfirst($purchaseTypeCode) . 'InventoryTabs_normal_' . $methodName, array(
			'text' => ucfirst($methodName) . ' Based'
		))->addTabPage('PurchaseType' . ucfirst($purchaseTypeCode) . 'InventoryTabs_normal_' . $methodName, array(
			'text' => $function($Product, $PurchaseType)
		));

		if ($Product->getId() == 0 && $methodName == 'barcode'){
			$InvTabs->disableTab('PurchaseType' . ucfirst($purchaseTypeCode) . 'InventoryTabs_normal_' . $methodName);
		}
	}

	$inputTable->addBodyRow(array(
		'columns' => array(
			array('text' => $InvTabs->draw())
		)
	));

	EventManager::notify('NewProductInventoryTabBottom', $Product, &$inputTable, &$PurchaseType);

	return $inputTable->draw();
}

function buildNormalInventoryBarcodeTable(Product $Product, $PurchaseType) {
	global $barcodeStatuses;
	$purchaseTypeTitle = $PurchaseType->getTitle();
	$purchaseTypeCode = $PurchaseType->getCode();

	$addButton = htmlBase::newElement('button')->setText(sysLanguage::get('TEXT_BUTTON_ADD'))->addClass('addBarcode');

	$barcodeTableHeaders = array(
		array(
			'addCls' => 'main',
			'text'   => 'Barcode'
		),
		array(
			'addCls' => 'main',
			'text'   => 'Type'
		),
		array(
			'addCls' => 'main',
			'text'   => 'Status'
		),
	);

	EventManager::notify('NewProductAddBarcodeOptionsHeader', &$barcodeTableHeaders);

	$barcodeTableHeaders[] = array(
		'addCls' => 'rightAlign main',
		'text'   => 'Action'
	);

	$barcodeInput = htmlBase::newElement('input')
		->setName('barcodeNumber')
		->addClass('barcodeNumber');

	$autoGenTextInput = htmlBase::newElement('input')
		->setSize(3)
		->setName('autogenTotal')
		->addClass('autogenTotal')
		->disable(true);

	$autoGenCheckboxInput = htmlBase::newElement('checkbox')
		->addClass('autogen')
		->setName('autogen')
		->setLabel('Auto Generate')
		->setLabelPosition('after')
		->setLabelSeparator('&nbsp;');

	$barcodeTableBody = array(
		array(
			'addCls' => 'main',
			'text'   => $barcodeInput->draw() . '<br />' . $autoGenTextInput->draw() . $autoGenCheckboxInput->draw()
		),
		array(
			'addCls' => 'main',
			'text'   => $purchaseTypeTitle
		),
		array(
			'addCls' => 'main',
			'text'   => $barcodeStatuses['A']
		)
	);

	EventManager::notify('NewProductAddBarcodeOptionsBody', &$barcodeTableBody);

	//if (isset($settings['attributeString'])){
	//	$addButton->attr('data-attribute_string', $settings['attributeString']);
	//}
	$addButton->attr('data-purchase_type', $purchaseTypeCode);

	$barcodeTableBody[] = array(
		'addCls' => 'rightAlign main',
		'text'   => $addButton
	);

	$barcodeTable = htmlBase::newElement('table')
		->setCellPadding(3)
		->setCellSpacing(0)
		->css('width', '100%')
		->addHeaderRow(array(
		'columns' => $barcodeTableHeaders
	))
		->addBodyRow(array(
		'columns' => $barcodeTableBody
	));

	$dataSet = array();

	$TableGrid = htmlBase::newElement('newGrid')
		->addClass('currentBarcodeTable');

	$checkAllBox = htmlBase::newElement('checkbox')
		->addClass('checkAll')
		->val($purchaseTypeCode);

	$currentBarcodesTableHeaders = array(
		array(
			'css'  => array('width' => '20px'),
			'text' => $checkAllBox
		),
		array('text' => 'Barcode'),
		array('text' => 'Type'),
		array('text' => 'Status')
	);

	EventManager::notify('NewProductAddBarcodeListingHeader', &$currentBarcodesTableHeaders);

	$currentBarcodesTableHeaders[] = array('text' => 'Action');

	$ajaxNotice = htmlBase::newElement('div')
		->addClass('main')
		->html('<small>*Barcodes are dynamically added and do not require the product to be updated</small>');

	$TableGrid->addAfterButtonBar($barcodeTable->draw() . '<br>' . $ajaxNotice->draw());

	$TableGrid->addButtons(array(
		htmlBase::newElement('button')->setId($PurchaseType->getCode())->usePreset('print')->addClass('printLabels')
			->setText('Print Labels')
	));

	$TableGrid->addHeaderRow(array(
		'columns' => $currentBarcodesTableHeaders
	));

	$Inventory = Doctrine_Core::getTable('ProductsInventory')->findOneByProductsIdAndTrackMethodAndTypeAndController(
		$Product->getId(),
		'barcode',
		$purchaseTypeCode,
		'normal'
	);
	if ($Inventory && $Inventory->count() > 0){
		foreach($Inventory->ProductsInventoryBarcodes->toArray() as $bInfo){
			$currentBarcodesTableBody = array(
				array(
					'align' => 'center',
					'text'  => '<input type="checkbox" name="barcodes[]" value="' . $bInfo['barcode_id'] . '" data-barcode="' . $bInfo['barcode'] . '" class="barcode_' . $purchaseTypeCode . '">'
				),
				array('text' => $bInfo['barcode']),
				array('text' => $purchaseTypeCode),
				array('text' => $barcodeStatuses[$bInfo['status']])
			);

			EventManager::notify('NewProductAddBarcodeListingBody', &$bInfo, &$currentBarcodesTableBody);

			//if (isset($settings['attribute_string'])) {
			//	$buttonData['data-attribute_string'] = $settings['attributeString'];
			//}

			$deleteButton = htmlBase::newElement('icon')
				->addClass('deleteBarcode')
				->setTooltip(sysLanguage::get('TEXT_BUTTON_DELETE'))
				->setType('delete')
				->attr(array(
				'data-barcode_id'    => $bInfo['barcode_id'],
				'data-purchase_type' => $purchaseTypeCode
			));

			$updateButton = htmlBase::newElement('icon')
				->addClass('updateBarcode')
				->setTooltip(sysLanguage::get('TEXT_BUTTON_UPDATE'))
				->setType('save')
				->attr(array(
				'data-barcode_id'    => $bInfo['barcode_id'],
				'data-purchase_type' => $purchaseTypeCode
			));

			$commentButton = htmlBase::newElement('icon')
				->addClass('commentBarcode')
				->setTooltip(sysLanguage::get('TEXT_BUTTON_COMMENT'))
				->setType('comment')
				->attr(array(
				'data-barcode_id'    => $bInfo['barcode_id'],
				'data-purchase_type' => $purchaseTypeCode
			));

			$lastColHtml = $deleteButton->draw() . ' ' . $updateButton->draw() . ' ' . $commentButton->draw();

			EventManager::notify('NewProductAddBarcodeListingButtons', &$lastColHtml, &$buttonData);

			$currentBarcodesTableBody[] = array(
				'css'   => array(
					'white-space' => 'nowrap',
				),
				'align' => 'right',
				'text'  => $lastColHtml
			);

			$TableGrid->addBodyRow(array(
				'addCls'  => 'noHover',
				'columns' => $currentBarcodesTableBody
			));
		}
	}

	return $TableGrid->draw();
}

function buildNormalInventoryQuantityTable(Product $Product, $PurchaseType) {
	global $barcodeStatuses;
	$purchaseTypeCode = $PurchaseType->getCode();

	$quantityTableHeaders = array(
		array(
			'addCls' => 'ui-widget-content ui-state-default ui-grid-cell ui-grid-cell-first',
			'text'   => ''
		)
	);

	$quantityTableBody = array(
		array(
			'addCls' => 'ui-widget-content ui-state-default ui-grid-cell ui-grid-cell-first',
			'text'   => '<b>Standard</b>'
		)
	);

	$QinventoryQuantity = Doctrine_Query::create()
		->from('ProductsInventory i')
		->leftJoin('i.ProductsInventoryQuantity iq')
		->where('i.products_id = ?', $Product->getId())
		->andWhere('track_method = ?', 'quantity')
		->andWhere('type = ?', $purchaseTypeCode)
		->andWhere('controller = ?', 'normal');

	EventManager::notify('AdminEditProductLoadInventoryQuantity', $QinventoryQuantity);

	$Result = $QinventoryQuantity->execute();

	$Quantity = $Result[0]->ProductsInventoryQuantity[0];

	$inventoryColumns = $PurchaseType->getConfigData('INVENTORY_QUANTITY_STATUSES');
	foreach($inventoryColumns as $short){
		$invQty = '0';
		if ($Quantity !== false){
			switch($short){
				case 'A':
					$invQty = $Quantity->available;
					break;
				case 'O':
					$invQty = $Quantity->qty_out;
					break;
				case 'B':
					$invQty = $Quantity->broken;
					break;
				case 'R':
					$invQty = $Quantity->reserved;
					break;
				case 'P':
					$invQty = $Quantity->purchased;
					break;
			}
		}

		if ($short == 'A'){
			$inputObj = htmlBase::newElement('input')
				->setSize(5)
				->setName('inventory_quantity[normal][' . $purchaseTypeCode . '][' . $short . ']')
				->addClass('quantityInput')
				->attr('data-purchase_type', $purchaseTypeCode)
				->attr('data-availability', $short)
				->val($invQty);
			$inputHtml = $inputObj->draw();
		}
		else {
			$inputHtml = $invQty;
		}

		$quantityTableHeaders[] = array(
			'addCls' => 'ui-widget-content ui-state-default ui-grid-cell',
			'text'   => '<b>' . $barcodeStatuses[$short] . '</b>'
		);

		$quantityTableBody[] = array(
			'attr'   => array(
				'data-availability' => $short
			),
			'addCls' => 'ui-widget-content ui-grid-cell centerAlign',
			'text'   => '&nbsp;' . $inputHtml . '&nbsp;'
		);
	}

	$quantityTable = htmlBase::newElement('table')
		->setCellPadding(3)
		->setCellSpacing(0)
		->addClass('ui-grid')
		->addHeaderRow(array(
		'addCls'  => 'ui-grid-row ui-grid-heading-row',
		'columns' => $quantityTableHeaders
	))
		->addBodyRow(array(
		'columns' => $quantityTableBody
	));

	EventManager::notify('NewProductAddQuantityRows', $PurchaseType, $inventoryColumns, $quantityTable);

	return $quantityTable->draw();
}

$purchaseTypeTabsObj = htmlBase::newElement('tabs')
	->setId('purchaseTypeTabs');
PurchaseTypeModules::loadModules();
foreach(PurchaseTypeModules::getModules() as $purchaseType){
	$ProductPurchaseType = $Product->getProductTypeClass()->getPurchaseType($purchaseType->getCode(), true);
	$code = $purchaseType->getCode();

	$Sorted = array();
	$baseClassName = 'PurchaseTypeTab' . ucfirst($code);
	if (is_dir($purchaseType->getPath() . 'admin/ext_app/products/purchaseTypeTabs/')){
		if (file_exists($purchaseType->getPath() . 'admin/ext_app/products/purchaseTypeTabs/language_defines/global.xml')){
			sysLanguage::loadDefinitions($purchaseType->getPath() . 'admin/ext_app/products/purchaseTypeTabs/language_defines/global.xml');
		}

		$Dir = new DirectoryIterator($purchaseType->getPath() . 'admin/ext_app/products/purchaseTypeTabs/');
		foreach($Dir as $d){
			if ($d->isDot() || $d->isDir()) {
				continue;
			}

			$ClassName = $baseClassName . '_' . $d->getBasename('.php');
			if (!class_exists($ClassName)){
				require($d->getPathname());
			}
			$ClassObj = new $ClassName;
			$Sorted[] = $ClassObj;
		}
	}

	EventManager::notify('AdminProductEditAddPurchaseTypeSettingsTab', &$Sorted, $ProductPurchaseType);

	usort($Sorted, function ($a, $b) {
		return ($a->getDisplayOrder() > $b->getDisplayOrder() ? 1 : -1);
	});

	$PurchaseTypeSettingsTabs[$code] = htmlBase::newElement('tabs')
		->addClass('makeVerticalTabs')
		->setId('purchaseType' . ucfirst($code) . 'SettingsTabs');

	foreach($Sorted as $ClassObj){
		$ClassObj->addTab($PurchaseTypeSettingsTabs[$code], $Product, $ProductPurchaseType);
	}

	$purchaseTypeTabsObj->addTabHeader('purchaseTypeTab_' . $code, array('text' => $purchaseType->getTitle()))
		->addTabPage('purchaseTypeTab_' . $code, array('text' => $PurchaseTypeSettingsTabs[$code]->draw()));
}

$contents = EventManager::notifyWithReturn('NewProductPricingTabTop', $Product);
if (!empty($contents)){
	foreach($contents as $content){
		echo $content;
	}
}

echo $purchaseTypeTabsObj->draw();
?>