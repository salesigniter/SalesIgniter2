<?php
/*
	Multi Stores Extension Version 1.1
	
	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/
if (isset($_GET['sID'])){
	$Qstore = Doctrine_Core::getTable('Stores')->findOneByStoresId((int)$_GET['sID']);
}

/* Build all store info inputs that are needed --BEGIN-- */
$storeName = htmlBase::newElement('input')->css('width', '100%')->setName('stores_name');
$storeDomain = htmlBase::newElement('input')->css('width', '100%')->setName('stores_domain');
$storeSslDomain = htmlBase::newElement('input')->css('width', '100%')->setName('stores_ssl_domain');
$storeEmail = htmlBase::newElement('input')->css('width', '100%')->setName('stores_email');
$storeAddress = htmlBase::newElement('input')->css('width', '100%')->setName('stores_street_address');
//$storePostcode = htmlBase::newElement('input')->setName('stores_postcode');
$storeFeeRoyalty = htmlBase::newElement('input')->attr('size', '6')->attr('placeholder', 'ex. 1.35')->setName('fees[royalty]');
$storeFeeManagement = htmlBase::newElement('input')->attr('size', '6')->attr('placeholder', 'ex. 1.35')->setName('fees[management]');
$storeFeeMarketing = htmlBase::newElement('input')->attr('size', '6')->attr('placeholder', 'ex. 1.35')->setName('fees[marketing]');
$storeFeeLabor = htmlBase::newElement('input')->attr('size', '6')->attr('placeholder', 'ex. 1.35')->setName('fees[labor]');
$storeFeeParts = htmlBase::newElement('input')->attr('size', '6')->attr('placeholder', 'ex. 1.35')->setName('fees[parts]');

/* Auto Upgrade ( Version 1.0 to 1.1 ) --BEGIN-- */
$storeOwner = htmlBase::newElement('input')->css('width', '100%')->setName('stores_owner');
/* Auto Upgrade ( Version 1.0 to 1.1 ) --END-- */

if (isset($Qstore)){
	$storeName->setValue($Qstore['stores_name']);
	$storeDomain->setValue($Qstore['stores_domain']);
	$storeSslDomain->setValue($Qstore['stores_ssl_domain']);
	$storeEmail->setValue($Qstore['stores_email']);
	$storeAddress->setValue($Qstore['stores_street_address']);
	//$storePostcode->setValue($Qstore['stores_postcode']);

	/* Auto Upgrade ( Version 1.0 to 1.1 ) --BEGIN-- */
	$storeOwner->setValue($Qstore['stores_owner']);
	/* Auto Upgrade ( Version 1.0 to 1.1 ) --END-- */

	$storeFeeRoyalty->setValue($Qstore->StoresFees->fee_royalty);
	$storeFeeManagement->setValue($Qstore->StoresFees->fee_management);
	$storeFeeMarketing->setValue($Qstore->StoresFees->fee_marketing);
	$storeFeeLabor->setValue($Qstore->StoresFees->fee_labor);
	$storeFeeParts->setValue($Qstore->StoresFees->fee_parts);
}

$templatesSet = htmlBase::newElement('selectbox')->setName('stores_template');
$dir = new DirectoryIterator(sysConfig::getDirFsCatalog() . 'templates/');
$ignoreTemplates = array('email', 'help', 'help-text');
$templatesArray = array();
foreach($dir as $fileObj){
	if ($fileObj->isDot() || $fileObj->isDir() === false) {
		continue;
	}
	if (in_array(strtolower($fileObj->getBasename()), $ignoreTemplates)) {
		continue;
	}

	$templatesSet->addOption($fileObj->getBasename(), ucfirst($fileObj->getBasename()));
}

if (isset($Qstore)){
	$templatesSet->selectOptionByValue($Qstore['stores_template']);
}
/* Build all store info inputs that are needed --END-- */

/* Build all categories inputs that are needed --BEGIN-- */
$checkedCats = array();
if (isset($Qstore)){
	$Qcategories = Doctrine_Query::create()
		->select('categories_id')
		->from('CategoriesToStores')
		->where('stores_id = ?', $Qstore['stores_id'])
		->execute();
	if ($Qcategories){
		foreach($Qcategories->toArray() as $cInfo){
			$checkedCats[] = $cInfo['categories_id'];
		}
	}
}
$categoriesList = tep_get_category_tree_list('0', $checkedCats);
/* Build all categories inputs that are needed --END-- */

/* Build the store info table --BEGIN-- */
$storeInfoTable = htmlBase::newElement('table')->css('width', '100%')->setCellPadding(3)->setCellSpacing(0);

$storeInfoTable->addBodyRow(array(
		'columns' => array(
			array('css' => array('width' => '150px'), 'addCls' => 'main', 'text' => sysLanguage::get('TEXT_STORES_NAME')),
			array('addCls' => 'main', 'text' => $storeName->draw())
		)
	));

$storeInfoTable->addBodyRow(array(
		'columns' => array(
			array('addCls' => 'main', 'text' => sysLanguage::get('TEXT_STORES_DOMAIN')),
			array('addCls' => 'main', 'text' => $storeDomain->draw())
		)
	));

$storeInfoTable->addBodyRow(array(
		'columns' => array(
			array('addCls' => 'main', 'text' => sysLanguage::get('TEXT_STORES_SSL_DOMAIN')),
			array('addCls' => 'main', 'text' => $storeSslDomain->draw())
		)
	));

/* Auto Upgrade ( Version 1.0 to 1.1 ) --BEGIN-- */
$storeInfoTable->addBodyRow(array(
		'columns' => array(
			array('addCls' => 'main', 'text' => sysLanguage::get('TEXT_STORES_OWNER')),
			array('addCls' => 'main', 'text' => $storeOwner->draw())
		)
	));
/* Auto Upgrade ( Version 1.0 to 1.1 ) --END-- */

$storeInfoTable->addBodyRow(array(
		'columns' => array(
			array('addCls' => 'main', 'text' => sysLanguage::get('TEXT_STORES_EMAIL')),
			array('addCls' => 'main', 'text' => $storeEmail->draw())
		)
	));

$storeInfoTable->addBodyRow(array(
		'columns' => array(
			array('addCls' => 'main', 'text' => sysLanguage::get('TEXT_STORES_STREET_ADDRESS')),
			array('addCls' => 'main', 'text' => $storeAddress->draw())
		)
	));

/*$storeInfoTable->addBodyRow(array(
		'columns' => array(
			array('addCls' => 'main', 'text' => sysLanguage::get('TEXT_STORES_POSTCODE')),
			array('addCls' => 'main', 'text' => $storePostcode->draw())
		)
	));*/

$storeInfoTable->addBodyRow(array(
		'columns' => array(
			array('addCls' => 'main', 'text' => sysLanguage::get('TEXT_STORES_TEMPLATE')),
			array('addCls' => 'main', 'text' => $templatesSet->draw())
		)
	));

$storeInfoTable->addBodyRow(array(
		'columns' => array(
			array('addCls' => 'main', 'colspan' => 2, 'text' => '<hr><b>Hire Fees</b><hr>')
		)
	));

$storeInfoTable->addBodyRow(array(
		'columns' => array(
			array('addCls' => 'main', 'text' => '<b>Royalty:</b>'),
			array('addCls' => 'main', 'text' => $storeFeeRoyalty->draw() . '%')
		)
	));
$storeInfoTable->addBodyRow(array(
		'columns' => array(
			array('addCls' => 'main', 'text' => '<b>Management:</b>'),
			array('addCls' => 'main', 'text' => $storeFeeManagement->draw() . '%')
		)
	));
$storeInfoTable->addBodyRow(array(
		'columns' => array(
			array('addCls' => 'main', 'text' => '<b>Marketing:</b>'),
			array('addCls' => 'main', 'text' => $storeFeeMarketing->draw() . '%')
		)
	));
$storeInfoTable->addBodyRow(array(
		'columns' => array(
			array('addCls' => 'main', 'text' => '<b>Labor:</b>'),
			array('addCls' => 'main', 'text' => $storeFeeLabor->draw() . '%')
		)
	));
$storeInfoTable->addBodyRow(array(
		'columns' => array(
			array('addCls' => 'main', 'text' => '<b>Parts:</b>'),
			array('addCls' => 'main', 'text' => $storeFeeParts->draw() . '%')
		)
	));
/* Build the store info table --END-- */

/* Build the payment info table --BEGIN-- */
$modulesArr = array();
OrderPaymentModules::loadModules();
foreach(OrderPaymentModules::getModules() as $Module){
	$modulesArr[] = array(
		'labelPosition' => 'after',
		'label' => $Module->getTitle(),
		'value' => $Module->getCode()
	);
}

$MultiStore = $appExtension->getExtension('multiStore');
if (isset($_GET['sID'])){
	if ($_GET['sID'] == 1){
		$tabContents = array();
		$storesArr = array();
		foreach($MultiStore->getStoresArray() as $sInfo){
			$paymentData = $sInfo->StoreToStorePaymentSettings->payment_settings;

			$storesArr[] = array(
				'id' => $sInfo['stores_id'],
				'text' => ($sInfo['stores_id'] == 1 ? 'Global' : $sInfo['stores_name']),
				'useGlobalPaymentData' => $sInfo->StoreToStorePaymentSettings->use_global,
				'paymentData' => (!empty($paymentData) ? json_decode($paymentData) : '')
			);
		}
		usort($storesArr, function ($a, $b){
				return ($a['id'] > $b['id'] ? 1 : -1);
			});
	}else{
		$tabContents = '';
		$sInfo = $MultiStore->getStoresArray($_GET['sID']);
		$paymentData = $sInfo->StoreToStorePaymentSettings->payment_settings;
		$storesArr = array(array(
			'id' => $sInfo['stores_id'],
			'text' => $sInfo['stores_name'],
			'useGlobalPaymentData' => $sInfo->StoreToStorePaymentSettings->use_global,
			'paymentData' => (!empty($paymentData) ? json_decode($paymentData) : '')
		));
	}
}else{
	$tabContents = '';
	$storesArr = array(array(
		'id' => 'new',
		'text' => 'New',
		'useGlobalPaymentData' => 1,
		'paymentData' => ''
	));
}

PurchaseTypeModules::loadModules();
foreach($storesArr as $sInfo){
	$storeId = $sInfo['id'];
	$storeName = $sInfo['text'];
	$useGlobal = $sInfo['useGlobalPaymentData'];
	$paymentData = $sInfo['paymentData'];

	$IncomeModules = htmlBase::newElement('checkbox')
		->addGroup(array(
			'name' => 'store_to_store_payments_income_modules[' . $storeId . '][]',
			'checked' => (is_object($paymentData) ? $paymentData->incomeModules : false),
			'data' => $modulesArr
		));

	$ExpenseModules = htmlBase::newElement('checkbox')
		->addGroup(array(
			'name' => 'store_to_store_payments_expense_modules[' . $storeId . '][]',
			'checked' => (is_object($paymentData) ? $paymentData->expenseModules : false),
			'data' => $modulesArr
		));

	$percentageTable = htmlBase::newElement('table')
		->attr('border', 1)
		->setCellPadding(3)
		->setCellSpacing(0);

	$percentageTable->addBodyRow(array(
			'columns' => array(
				array('text' => ''),
				array('text' => 'Owed From Main'),
				array('text' => 'Owed To Main')
			)
		));

	foreach(PurchaseTypeModules::getModules() as $purchaseType){
		$PurchaseTypeCode = $purchaseType->getCode();

		$expenseVal = '';
		$incomeVal = '';
		if (is_object($paymentData)){
			if (isset($paymentData->productTypes)){
				if (isset($paymentData->productTypes->standard)){
					if (isset($paymentData->productTypes->standard->$PurchaseTypeCode)){
						if (isset($paymentData->productTypes->standard->$PurchaseTypeCode->income)){
							$incomeVal = $paymentData->productTypes->standard->$PurchaseTypeCode->income;
						}
						if (isset($paymentData->productTypes->standard->$PurchaseTypeCode->expense)){
							$expenseVal = $paymentData->productTypes->standard->$PurchaseTypeCode->expense;
						}
					}
				}
			}
		}

		$percentageTable->addBodyRow(array(
				'columns' => array(
					array(
						'text' => $purchaseType->getTitle()
					),
					array(
						'align' => 'center',
						'text' => htmlBase::newElement('input')
							->attr('size', 3)
							->setName('store_to_store_payments_income_module[' . $storeId . '][standard][' . $PurchaseTypeCode . ']')
							->val($incomeVal)
							->draw() . '%'
					),
					array(
						'align' => 'center',
						'text' => htmlBase::newElement('input')
							->attr('size', 3)
							->setName('store_to_store_payments_expense_module[' . $storeId . '][standard][' . $PurchaseTypeCode . ']')
							->val($expenseVal)
							->draw() . '%'
					)
				)
			));
	}

	$paymentInfoTable = htmlBase::newElement('table')->css('width', '100%')->setCellPadding(3)->setCellSpacing(0);

	$paymentInfoTable->addBodyRow(array(
			'columns' => array(
				array(
					'addCls' => 'main',
					'text' => '<b>Payments Made With These Modules Require Payment To The Main Store</b><br>' . $IncomeModules->draw()
				)
			)
		));

	$paymentInfoTable->addBodyRow(array(
			'columns' => array(
				array(
					'addCls' => 'main',
					'text' => '<b>Payments Made With These Modules Require Payment From The Main Store</b><br>' . $ExpenseModules->draw()
				)
			)
		));

	$paymentInfoTable->addBodyRow(array(
			'columns' => array(
				array(
					'addCls' => 'main',
					'text' => '<br><b>Percentage To Be Paid Based On Payment Method Used</b><br>' . $percentageTable->draw()
				)
			)
		));

	if (isset($_GET['sID']) && $_GET['sID'] == 1){
		if ($storeId > 1){
			$globalOrStore = htmlBase::newElement('radio')
				->addGroup(array(
					'name' => 'store_to_store_payment_use_global[' . $storeId . ']',
					'addCls' => 'storePaymentSelect',
					'checked' => $useGlobal,
					'data' => array(
						array('labelPosition' => 'after', 'label' => 'Use Global', 'value' => '1'),
						array('labelPosition' => 'after', 'label' => 'Use Store', 'value' => '0')
					)
				));

			$tabContents[$storeId] = array(
				'header' => $storeName,
				'html' => $globalOrStore->draw() .
					'<div class="storePaymentSettings" style="display:none">' . 
						$paymentInfoTable->draw() .
					'</div>'
			);
		}else{
			$tabContents[$storeId] = array(
				'header' => $storeName,
				'html' => $paymentInfoTable->draw()
			);
		}
	}else{
		$tabContents = $paymentInfoTable->draw();
	}
}

if (is_array($tabContents)){
	$storeToStorePaymentsTabs = htmlBase::newElement('tabs')
		->setId('storeToStorePaymentsTabs');
	foreach($tabContents as $storeId => $tInfo){
		$storeToStorePaymentsTabs
			->addTabHeader('tab_store_to_store_payments_' . $storeId, array('text' => $tInfo['header']))
			->addTabPage('tab_store_to_store_payments_' . $storeId, array('text' => $tInfo['html']));
	}
	$storeToStorePaymentsTabContent = $storeToStorePaymentsTabs->draw();
}else{
	$storeToStorePaymentsTabContent = $tabContents;
}
/* Build the payment info table --END-- */

/* Build the tabbed interface --BEGIN-- */
$tabsObj = htmlBase::newElement('tabs')
	->setId('storeTabs')
	->addTabHeader('tab_store_info', array('text' => 'Store Info'))
	->addTabPage('tab_store_info', array('text' => $storeInfoTable->draw()))
	->addTabHeader('tab_store_to_store_payments', array('text' => 'Store To Store Payments'))
	->addTabPage('tab_store_to_store_payments', array('text' => $storeToStorePaymentsTabContent))
	->addTabHeader('tab_categories', array('text' => 'Categories'))
	->addTabPage('tab_categories', array('text' => /*'<div style="color:red;">Note: All products inside the categories will be added to this store also.</div><br />' . */
	$categoriesList));
/* Build the tabbed interface --END-- */

EventManager::notify('NewStoreAddTab', &$tabsObj);

$saveButton = htmlBase::newElement('button')->setType('submit')->usePreset('save');
$cancelButton = htmlBase::newElement('button')->usePreset('cancel')
	->setHref(itw_app_link(tep_get_all_get_params(array('action')), null, 'default'));

$buttonContainer = new htmlElement('div');
$buttonContainer->append($saveButton)->append($cancelButton)->css(array(
		'float' => 'right',
		'width' => 'auto'
	))->addClass('ui-widget');

$pageForm = htmlBase::newElement('form')
	->attr('name', 'new_store')
	->attr('action', itw_app_link(tep_get_all_get_params(array('action')) . 'action=save'))
	->attr('enctype', 'multipart/form-data')
	->attr('method', 'post')
	->html($tabsObj->draw() . '<br />' . $buttonContainer->draw());

$headingTitle = htmlBase::newElement('div')
	->addClass('pageHeading')
	->html(sysLanguage::get('HEADING_TITLE'));

echo $headingTitle->draw() . '<br />' . $pageForm->draw();
?>