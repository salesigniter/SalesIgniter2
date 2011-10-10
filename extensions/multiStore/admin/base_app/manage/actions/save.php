<?php
/*
	Multi Stores Extension Version 1.1
	
	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/
$MultiStore = $appExtension->getExtension('multiStore');

$Stores = Doctrine::getTable('Stores');
if (isset($_GET['sID'])){
	$Store = $Stores->findOneByStoresId((int)$_GET['sID']);
}
else {
	$Store = $Stores->create();
}

$Store->stores_name = $_POST['stores_name'];
$Store->stores_domain = $_POST['stores_domain'];
$Store->stores_ssl_domain = $_POST['stores_ssl_domain'];
$Store->stores_email = $_POST['stores_email'];
$Store->stores_template = $_POST['stores_template'];
$Store->stores_street_address = $_POST['stores_street_address'];
$Store->stores_postcode = $_POST['stores_postcode'];

/* Auto Upgrade ( Version 1.0 to 1.1 ) --BEGIN-- */
$Store->stores_owner = $_POST['stores_owner'];
/* Auto Upgrade ( Version 1.0 to 1.1 ) --END-- */

if (isset($_POST['fees'])){
	$Store->StoresFees->fee_royalty = $_POST['fees']['royalty'];
	$Store->StoresFees->fee_management = $_POST['fees']['management'];
	$Store->StoresFees->fee_marketing = $_POST['fees']['marketing'];
	$Store->StoresFees->fee_labor = $_POST['fees']['labor'];
	$Store->StoresFees->fee_parts = $_POST['fees']['parts'];
}

$CategoriesToStores = $Store->CategoriesToStores;
//$ProductsToStores = $Store->ProductsToStores;

if (isset($_GET['sID'])){
	$CategoriesToStores->delete();
	//$ProductsToStores->delete();
}

if (isset($_POST['categories'])){
	$addedProducts = array();
	$addedCategories = array();
	foreach($_POST['categories'] as $categoryId){
		$CategoriesToStores[]->categories_id = $categoryId;
		/*$ProductsToCategories = Doctrine_Query::create()
		   ->select('products_id')
		   ->from('ProductsToCategories')
		   ->where('categories_id = ?', $categoryId)
		   ->execute();
		   if ($ProductsToCategories){
			   foreach($ProductsToCategories->toArray() as $product){
				   $productId = $product['products_id'];
				   if (in_array($productId, $addedProducts) === false){
					   $ProductsToStores[]->products_id = $productId;
				   }
				   $addedProducts[] = $productId;
			   }
		   }*/
	}
}

//print_r($Store->toArray());
$Store->save();

$StoreToStorePaymentSettings = Doctrine_Core::getTable('StoreToStorePaymentSettings');
function parsePaymentSettingPost($Settings){
	global $StoreToStorePaymentSettings;

	$PaymentSettings = $StoreToStorePaymentSettings->findOneByStoresId($Settings['storeId']);
	if (!$PaymentSettings){
		$PaymentSettings = $StoreToStorePaymentSettings->create();
		$PaymentSettings->stores_id = $Settings['storeId'];
	}
	$PaymentSettings->use_global = $Settings['useGlobal'];

	if ($PaymentSettings->use_global == '0'){
		$StoreSettings = array(
			'incomeModules' => $Settings['incomeModules'],
			'expenseModules' => $Settings['expenseModules'],
			'productTypes' => array()
		);

		foreach($Settings['productTypeIncome'] as $productType => $ptInfo){
			if (!isset($StoreSettings['productTypes'][$productType])){
				$StoreSettings['productTypes'][$productType] = array();
			}

			if (is_array($ptInfo)){
				foreach($ptInfo as $purchaseType => $pttInfo){
					$StoreSettings['productTypes'][$productType][$purchaseType]['income'] = $pttInfo;
				}
			}else{
				$StoreSettings['productTypes'][$productType]['income'] = $ptInfo;
			}
		}

		foreach($Settings['productTypeExpense'] as $productType => $ptInfo){
			if (!isset($StoreSettings['productTypes'][$productType])){
				$StoreSettings['productTypes'][$productType] = array();
			}

			if (is_array($ptInfo)){
				foreach($ptInfo as $purchaseType => $pttInfo){
					$StoreSettings['productTypes'][$productType][$purchaseType]['expense'] = $pttInfo;
				}
			}else{
				$StoreSettings['productTypes'][$productType]['expense'] = $ptInfo;
			}
		}
		$PaymentSettings->payment_settings = json_encode($StoreSettings);
	}
	$PaymentSettings->save();
}

if (isset($_GET['sID'])){
	if ($_GET['sID'] == 1){
		foreach($MultiStore->getStoresArray() as $sInfo){
			parsePaymentSettingPost(array(
					'storeId' => $sInfo['stores_id'],
					'useGlobal' => (isset($_POST['store_to_store_payment_use_global'][$sInfo['stores_id']]) ? $_POST['store_to_store_payment_use_global'][$sInfo['stores_id']] : '0'),
					'incomeModules' => $_POST['store_to_store_payments_income_modules'][$sInfo['stores_id']],
					'expenseModules' => $_POST['store_to_store_payments_expense_modules'][$sInfo['stores_id']],
					'productTypeIncome' => $_POST['store_to_store_payments_income_module'][$sInfo['stores_id']],
					'productTypeExpense' => $_POST['store_to_store_payments_expense_module'][$sInfo['stores_id']]
				));
		}
	}else{
		parsePaymentSettingPost(array(
				'storeId' => $Store->stores_id,
				'useGlobal' => (isset($_POST['store_to_store_payment_use_global'][$_GET['sID']]) ? $_POST['store_to_store_payment_use_global'][$_GET['sID']] : '0'),
				'incomeModules' => $_POST['store_to_store_payments_income_modules'][$_GET['sID']],
				'expenseModules' => $_POST['store_to_store_payments_expense_modules'][$_GET['sID']],
				'productTypeIncome' => $_POST['store_to_store_payments_income_module'][$_GET['sID']],
				'productTypeExpense' => $_POST['store_to_store_payments_expense_module'][$_GET['sID']]
			));
	}
}else{
	parsePaymentSettingPost(array(
			'storeId' => $Store->stores_id,
			'useGlobal' => (isset($_POST['store_to_store_payment_use_global']['new']) ? $_POST['store_to_store_payment_use_global']['new'] : '0'),
			'incomeModules' => $_POST['store_to_store_payments_income_modules']['new'],
			'expenseModules' => $_POST['store_to_store_payments_expense_modules']['new'],
			'productTypeIncome' => $_POST['store_to_store_payments_income_module']['new'],
			'productTypeExpense' => $_POST['store_to_store_payments_expense_module']['new']
		));
}

EventManager::attachActionResponse(itw_app_link(tep_get_all_get_params(array('action', 'sID')) . 'sID=' . $Store->stores_id, null, 'default'), 'redirect');
?>