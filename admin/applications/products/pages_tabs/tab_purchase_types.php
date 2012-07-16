<?php
function buildNormalInventoryTabs(Product $Product, PurchaseTypeBase $PurchaseType)
{
	//$PurchaseType->loadData($Product->getId());
	$purchaseTypeCode = $PurchaseType->getCode();

	$inputTable = htmlBase::newElement('table')
		->setCellPadding(2)
		->setCellSpacing(0)
		->css('width', '100%');

	$QuantityGrid = htmlBase::newGrid()
		->addClass('quantityGrid');

	$SerialsGrid = htmlBase::newGrid()
		->allowMultipleRowSelect(true)
		->addClass('serialsGrid')
		->attr(
		array(
			'data-purchase_type'  => $PurchaseType->getCode(),
			'data-default_status' => $PurchaseType->getConfigData('INVENTORY_STATUS_AVAILABLE')
		));

	$QuantityGridHeader = array(
		'columns' => array()
	);

	$QuantityGridBody = array(
		'addCls'  => 'noHover noSelect',
		'columns' => array()
	);

	$SerialsGridHeader = array(
		'columns' => array(
			array('text' => 'Serial Number'),
			array('text' => 'Status')
		)
	);

	$availableStatuses = array();
	$inventoryColumns = $PurchaseType->getConfigData('INVENTORY_QUANTITY_STATUSES');
	$inventoryItems = $PurchaseType->getInventoryItems();
	foreach($inventoryColumns as $id){
		$StatusName = itw_get_status_name($id);
		$availableStatuses[] = array(
			'id'   => $id,
			'text' => $StatusName
		);

		$QuantityGridHeader['columns'][] = array(
			'text' => $StatusName
		);

		$total = 0;
		if (isset($inventoryItems[$id])){
			$total = $inventoryItems[$id]['total'];

			if (isset($inventoryItems[$id]['serials'])){
				foreach($inventoryItems[$id]['serials'] as $Serial){
					$SerialsGrid->addBodyRow(array(
						'columns' => array(
							array(
								'align' => 'center',
								'text'  => '<input type="hidden" name="inventory_serial[' . $purchaseTypeCode . '][number][]" value="' . $Serial . '">' . $Serial
							),
							array(
								'align' => 'center',
								'text'  => itw_get_status_name($id)
							)
						)
					));
				}
			}
		}

		if ($id == $PurchaseType->getConfigData('INVENTORY_STATUS_AVAILABLE')){
			$QtyInput = htmlBase::newInput()
				->addClass('availableQuantity')
				->setSize(8)
				->setName('inventory[' . $PurchaseType->getCode() . '][' . $id . ']')
				->setValue($total);
		}
		else {
			$QtyInput = $total;
		}

		$QuantityGridBody['columns'][] = array(
			'align' => 'center',
			'text'  => $QtyInput
		);
	}

	EventManager::notify('NewProductInventoryTabBottom', $Product, &$QuantityGridHeader, &$QuantityGridBody, &$PurchaseType);

	$QuantityGrid->addHeaderRow($QuantityGridHeader);
	$QuantityGrid->addBodyRow($QuantityGridBody);

	$SerialsGrid->attr('data-available_statuses', urlencode(json_encode($availableStatuses)));
	$SerialsGrid->addButtons(array(
		htmlBase::newButton()
			->addClass('genSerialButton')
			->usePreset('install')
			->setText('Auto Generate'),
		htmlBase::newButton()
			->addClass('addSerialButton')
			->usePreset('new')
			->setText('Add'),
		htmlBase::newButton()
			->addClass('deleteSerialButton')
			->usePreset('delete')
			->disable(),
	));
	$SerialsGrid->addHeaderRow($SerialsGridHeader);

	$inputTable->addBodyRow(array(
		'columns' => array(
			array('text' => $QuantityGrid)
		)
	));

	$UseSerialsCheckbox = htmlBase::newCheckbox()
		->setName('use_serials[' . $PurchaseType->getCode() . ']')
		->setLabel('Use Serial Numbers')
		->setLabelPosition('right')
		->setChecked($PurchaseType->getData('use_serials') == 1);

	$inputTable->addBodyRow(array(
		'columns' => array(
			array('text' => '<br><hr><br>' . $UseSerialsCheckbox->draw() . '<br>')
		)
	));

	$inputTable->addBodyRow(array(
		'columns' => array(
			array('text' => $SerialsGrid)
		)
	));

	return $inputTable->draw();
}

$purchaseTypeTabsObj = htmlBase::newElement('tabs')
	->setId('purchaseTypeTabs');
PurchaseTypeModules::loadModules();
foreach(PurchaseTypeModules::getModules() as $purchaseType){
	$ProductPurchaseType = $Product
		->getProductTypeClass()
		->getPurchaseType($purchaseType->getCode(), true);
	//echo '<pre>';print_r($ProductPurchaseType);
	$code = $purchaseType->getCode();

	$Sorted = array();
	$baseClassName = 'PurchaseTypeTab' . ucfirst($code);
	if (is_dir($purchaseType->getPath() . 'admin/ext_app/products/purchaseTypeTabs/')){
		if (file_exists($purchaseType->getPath() . 'admin/ext_app/products/purchaseTypeTabs/language_defines/global.xml')){
			sysLanguage::loadDefinitions($purchaseType->getPath() . 'admin/ext_app/products/purchaseTypeTabs/language_defines/global.xml');
		}

		$Dir = new DirectoryIterator($purchaseType->getPath() . 'admin/ext_app/products/purchaseTypeTabs/');
		foreach($Dir as $d){
			if ($d->isDot() || $d->isDir()){
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

	usort($Sorted, function ($a, $b)
	{
		return ($a->getDisplayOrder() > $b->getDisplayOrder() ? 1 : -1);
	});

	$PurchaseTypeSettingsTabs[$code] = htmlBase::newElement('tabs')
		->addClass('makeVerticalTabs')
		->setId('purchaseType' . ucfirst($code) . 'SettingsTabs');

	foreach($Sorted as $ClassObj){
		$ClassObj->addTab($PurchaseTypeSettingsTabs[$code], $Product, $ProductPurchaseType);
	}

	$purchaseTypeTabsObj
		->addTabHeader('purchaseTypeTab_' . $code, array('text' => $purchaseType->getTitle()))
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