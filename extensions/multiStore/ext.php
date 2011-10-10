<?php
/*
	Multi Stores Extension Version 1.1
	
	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class Extension_multiStore extends ExtensionBase
{

	private $storesArray = array();

	private $storeInfoCache = array();

	public function __construct() {
		parent::__construct('multiStore');
	}

	public function init() {
		global $App, $appExtension, $Template;
		if ($this->enabled === false) {
			return;
		}

		EventManager::attachEvents(array(
				'EmailEventSetAllowedVars',
				'OrderQueryBeforeExecute',
				'OrderSingleLoad',
				'ModuleConfigReaderModuleConfigLoad',
				'PurchaseTypeLoadDataQuery',
				'PurchaseTypeLoadData',
				'ProductInventoryBarcodeHasInventoryQueryBeforeExecute',
				'ProductInventoryBarcodeGetInventoryItemsQueryBeforeExecute',
				'ProductInventoryBarcodeGetInventoryItemsArrayPopulate'
			), null, $this);

		if ($appExtension->isCatalog()){
			EventManager::attachEvents(array(
					'CategoryQueryBeforeExecute',
					'CustomerQueryBeforeExecute',
					'ProductQueryBeforeExecute',
					'ProductListingQueryBeforeExecute',
					'SpecialQueryBeforeExecute',
					'FeaturedQueryBeforeExecute',
					'CheckoutProcessPostProcess',
					'SetTemplateName',
					'SeoUrlsInit',
					'ReviewsQueryBeforeExecute',
					'InsertOrderedProductBeforeSave',
					'PurchaseTypeGetPrice',
					'PurchaseTypeGetTaxId'
				), null, $this);
		}

		if ($appExtension->isAdmin()){
			EventManager::attachEvents(array(
					'BoxConfigurationAddLink',
					'AdminHeaderRightAddContent',
					'AdminInventoryCentersListingQueryBeforeExecute',
					'ProductInventoryReportsListingQueryBeforeExecute',
					'NewCustomerAccountBeforeExecute',
					'CustomerInfoAddTableContainer',
					'AdminProductPurchaseTypeOnSave'
				), null, $this);

			if ($App->getAppName() == 'customers'){
				EventManager::attachEvents(array(
						'CustomersListingQueryBeforeExecute'
					), null, $this);
			}

			if ($App->getAppName() == 'orders'){
				EventManager::attachEvents(array(
						'AdminOrdersListingBeforeExecute',
						'OrdersListingAddGridHeader',
						'OrdersListingAddGridBody'
					), null, $this);
			}

			if ($App->getAppName() == 'products'){
				EventManager::attachEvents(array(
						'AdminProductListingQueryBeforeExecute'
					), null, $this);
			}

			if ($App->getAppName() == 'categories'){
				EventManager::attachEvents(array(
						'CategoryListingQueryBeforeExecute'
					), null, $this);
			}

			$App->addJavascriptFile('ext/jQuery/ui/jquery.ui.dropdownchecklist.js');
			$App->addStylesheetFile('ext/jQuery/themes/smoothness/ui.dropdownchecklist.css');
			$App->addJavascriptFile('extensions/multiStore/javascript/main.js');
		}

		$this->loadStoreInfo();
	}

	public function loadStoreInfo() {
		global $App;
		if ($App->getEnv() == 'admin'){
			$this->loadStoreInfoAdmin();
		}
		else {
			$this->loadStoreInfoCatalog();
		}
	}

	private function loadStoreInfoAdmin(){
		if (Session::exists('login_id')){
			$Qadmin = Doctrine_Query::create()
				->from('Admin')
				->where('admin_id = ?', Session::get('login_id'))
				->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

			$Qstore = Doctrine_Query::create()
				->from('Stores')
				->where('stores_id = ?', $Qadmin[0]['admins_main_stores'])
				->execute(array(), Doctrine::HYDRATE_ARRAY);
			$this->storeInfo = $Qstore[0];

			Session::set('admin_allowed_stores', explode(',', $Qadmin[0]['admins_stores']));

			if (Session::exists('admin_showing_stores') === false){
				Session::set('admin_showing_stores', array($Qadmin[0]['admins_main_store']));
			}

			if (isset($_GET['stores_id'])){
				$validStores = array();
				foreach($_GET['stores_id'] as $storeId){
					if ($storeId == 'all') continue;
					if (in_array($storeId, Session::get('admin_allowed_stores')) === false) continue;
					$validStores[] = $storeId;
				}
				Session::set('admin_showing_stores', $validStores);
				tep_redirect(itw_app_link(tep_get_all_get_params(array('action', 'stores_id'))));
			}
		}else{
			$Qstore = Doctrine_Query::create()
				->from('Stores')
				->where('stores_id = ?', 1)
				->execute(array(), Doctrine::HYDRATE_ARRAY);
			$this->storeInfo = $Qstore[0];
		}

		Session::set('tplDir', 'fallback');

		sysLanguage::set('HEAD_TITLE_TAG_DEFAULT', $this->storeInfo['stores_name']);

		sysConfig::set('HTTP_COOKIE_DOMAIN', $this->storeInfo['stores_domain']);
		sysConfig::set('HTTPS_COOKIE_DOMAIN', $this->storeInfo['stores_ssl_domain']);
	}

	private function loadStoreInfoCatalog(){
		global $App;
		if ((getenv('HTTPS') == 'on' && Session::exists('current_store_id')) || isset($_GET['forceStoreId'])) {
			$checkId = Session::get('current_store_id');
			if (isset($_GET['forceStoreId'])){
				$checkId = $_GET['forceStoreId'];
			}
			$Qstore = mysql_query('select * from stores where stores_id = "' . $checkId . '"');
		}
		else {
			$domainCheck = array($_SERVER['HTTP_HOST']);
			if (substr($_SERVER['HTTP_HOST'], 0, 4) != 'www.'){
				$domainCheck[] = 'www.' . $_SERVER['HTTP_HOST'];
			}
			else {
				$domainCheck[] = substr($_SERVER['HTTP_HOST'], 4);
			}

			if (getenv('HTTPS') == 'on'){
				$checkCol = 'stores_ssl_domain';
			}
			else {
				$checkCol = 'stores_domain';
			}
			$Qstore = mysql_query('select * from stores where ' . $checkCol . ' IN("' . implode('", "', $domainCheck) . '")');
		}
		$this->storeInfo = mysql_fetch_assoc($Qstore);
		Session::set('tplDir', $this->storeInfo['stores_template']);

		if (!Session::exists('current_store_id') || (Session::get('current_store_id') != $this->storeInfo['stores_id'])){
			//if (getenv('HTTPS') != 'on'){
				Session::set('current_store_id', $this->storeInfo['stores_id']);
			//}
		}
		else {
			$Qconfig = mysql_query('select configuration_key, configuration_value from stores_configuration where stores_id = "' . Session::get('current_store_id') . '"');
			if (mysql_num_rows($Qconfig)){
				while($cInfo = mysql_fetch_assoc($Qconfig)){
					sysConfig::set($cInfo['configuration_key'], $cInfo['configuration_value']);
				}
			}
		}

		define('HEAD_TITLE_TAG_DEFAULT', $this->storeInfo['stores_name']);
		if ($App->getEnv() == 'catalog'){
			sysConfig::set('HTTP_SERVER', 'http://' . $this->storeInfo['stores_domain']);
			sysConfig::set('HTTPS_SERVER', 'https://' . $this->storeInfo['stores_ssl_domain']);
		}
		sysConfig::set('HTTP_COOKIE_DOMAIN', $this->storeInfo['stores_domain']);
		sysConfig::set('HTTPS_COOKIE_DOMAIN', $this->storeInfo['stores_ssl_domain']);
	}

/* Auto Upgrade ( Version 1.0 to 1.1 ) --BEGIN-- */
	public function EmailEventSetAllowedVars(&$allowedVars) {
		$allowedVars['store_name'] = $this->storeInfo['stores_name'];
		$allowedVars['store_owner'] = $this->storeInfo['stores_owner'];
		$allowedVars['store_owner_email'] = $this->storeInfo['stores_email'];
		$allowedVars['store_url'] = 'http://' . $this->storeInfo['stores_domain'];
	}

/* Auto Upgrade ( Version 1.0 to 1.1 ) --END-- */

	public function ReviewsQueryBeforeExecute(&$Qreviews) {
		$Qreviews->leftJoin('p.ProductsToStores p2s')
			->andWhere('p2s.stores_id = ?', Session::get('current_store_id'));
	}

	public function AdminProductListingQueryBeforeExecute(&$Qproducts) {
		if (isset($this->adminStoreId)){
			$Qproducts->leftJoin('p.ProductsToStores p2s')
				->andWhereIn('p2s.stores_id', Session::get('admin_showing_stores'));
		}
	}

	public function AdminInventoryCentersListingQueryBeforeExecute(&$Qcenter) {
		if (isset($this->adminStoreId)){
			$Qcenter->andWhereIn('inventory_center_stores', Session::get('admin_showing_stores'));
		}
	}

	public function CategoryListingQueryBeforeExecute(&$Qcategories) {
		$Qcategories
			->leftJoin('c.CategoriesToStores c2s')
			->whereIn('c2s.stores_id', Session::get('admin_showing_stores'));
	}

	public function AdminOrdersListingBeforeExecute(&$Qorders) {
		$Qorders
			->leftJoin('o.OrdersToStores order2store')
			->leftJoin('order2store.Stores store')
			->addSelect('store.stores_name, order2store.stores_id')
			->whereIn('order2store.stores_id', Session::get('admin_showing_stores'));
	}

	public function SeoUrlsInit(&$seoUrl) {
		$seoUrl->base_url = 'http://' . $this->storeInfo['stores_domain'] . sysConfig::getDirWsCatalog('NONSSL');
		$seoUrl->base_url_ssl = 'https://' . $this->storeInfo['stores_ssl_domain'] . sysConfig::getDirWsCatalog('SSL');
	}

	public function getStoresArray($storesId = false) {
		global $appExtension;
		if ($storesId !== false){
			if (!isset($this->storeInfoCache[$storesId])){
				$Qstores = Doctrine_Query::create()
					->from('Stores')
					->where('stores_id = ?', $storesId)
					->execute();
				$this->storeInfoCache[$storesId] = $Qstores[0];
			}
			return $this->storeInfoCache[$storesId];
		}else{
			if (empty($this->storesArray)){
				$Qstores = Doctrine_Query::create()
					->from('Stores')
					->orderBy('stores_name');

				if ($appExtension->isAdmin() === true){
					$Qstores->whereIn('stores_id', Session::get('admin_allowed_stores'));
				}

				$this->storesArray = $Qstores->execute();
			}
		}
		return $this->storesArray;
	}

	public function AdminHeaderRightAddContent() {
		$Result = $this->getStoresArray();
		if ($Result){
			$form = htmlBase::newElement('form')
				->attr('name', 'storeSelector')
				->attr('method', 'get')
				->attr('action', itw_app_link(tep_get_all_get_params(array('action', 'stores_id'))));

			$selectBox = htmlBase::newElement('selectbox')
				->setName('stores_id[]')
				->attr('id', 'storeSelect')
				->attr('multiple', 'multiple')/*
			->attr('onchange', 'this.form.submit()')*/
			;

			$selectBox->addOption('all', 'All Allowed Stores', false);
			foreach($Result as $sInfo){
				$selectBox->addOption(
					$sInfo['stores_id'],
					$sInfo['stores_name'],
					(in_array($sInfo['stores_id'], Session::get('admin_showing_stores')) ? true : false)
				);
			}
			$form->append($selectBox);
			$form->append(htmlBase::newElement('button')->setText('GO')->setType('submit'));
			return '<span style="vertical-align:middle;">Showing Store(s): </span>' . $form->draw();
		}
		return '';
	}

	public function SetTemplateName() {
		Session::set('tplDir', $this->storeInfo['stores_template']);
	}

	public function BoxConfigurationAddLink(&$contents) {
		$contents['children'][] = array(
			'link' => false,
			'text' => $this->getExtensionName(),
			'children' => array(
				array(
					'link' => itw_app_link('appExt=multiStore', 'manage', 'default', 'SSL'),
					'text' => 'Setup Stores'
				)
			)
		);
	}

	public function CategoryQueryBeforeExecute(&$categoryQuery) {
		$categoryQuery->leftJoin('c.CategoriesToStores c2s')
			->andWhere('c2s.stores_id = ?', $this->storeInfo['stores_id']);
	}

	public function CustomerQueryBeforeExecute(&$customerQuery) {
		$customerQuery->leftJoin('c.CustomersToStores c2s')
			->andWhere('c2s.stores_id = ?', $this->storeInfo['stores_id']);
	}

	public function OrderQueryBeforeExecute(&$orderQuery) {
		global $appExtension;
		if ($appExtension->isAdmin()){
			$orderQuery->leftJoin('o.OrdersToStores o2s');
		}
		else {
			$orderQuery->leftJoin('o.OrdersToStores o2s')
				->andWhere('o2s.stores_id = ?', $this->storeInfo['stores_id']);
		}
	}

	public function ProductQueryBeforeExecute(&$productQuery) {
		$productQuery->leftJoin('p.ProductsToStores p2s')
			->andWhere('p2s.stores_id = ?', $this->storeInfo['stores_id']);
	}

	public function ProductListingQueryBeforeExecute(&$productQuery) {
		$productQuery->leftJoin('p.ProductsToStores p2s')
			->andWhere('p2s.stores_id = ?', $this->storeInfo['stores_id']);
	}

	public function FeaturedQueryBeforeExecute(&$productQuery) {
		$productQuery->leftJoin('p.ProductsToStores p2s')
			->andWhere('p2s.stores_id = ?', $this->storeInfo['stores_id']);
	}

	public function SpecialQueryBeforeExecute(&$productQuery) {
		$productQuery->leftJoin('p.SpecialsToStores s2s')
			->andWhere('s2s.stores_id = ?', $this->storeInfo['stores_id']);
	}

	public function CheckoutProcessPostProcess(&$order) {
		$OrdersToStores = new OrdersToStores();
		$OrdersToStores->orders_id = $order->newOrder['orderID'];
		$OrdersToStores->stores_id = $this->getStoreId();
		$OrdersToStores->save();
	}

	public function OrdersListingAddGridHeader(&$gridHeaders) {
		$gridHeaders[] = array(
			'text' => 'Store Name'
		);
	}

	public function OrdersListingAddGridBody(&$order, &$gridBody) {
		$gridBody[] = array(
			'text' => (isset($order['OrdersToStores']) ? $order['OrdersToStores']['Stores']['stores_name'] : 'N/A'),
			'align' => 'center'
		);
	}

	public function OrderSingleLoad(&$orderClass, $Order) {
		$orderClass->info['store_id'] = $Order['OrdersToStores']['stores_id'];
	}

	public function getStoreId() {
		return $this->storeInfo['stores_id'];
	}

	public function getStoreName() {
		return $this->storeInfo['stores_name'];
	}

	public function getStoreEmail() {
		return $this->storeInfo['stores_email'];
	}

	public function getStoreDomain() {
		return $this->storeInfo['stores_domain'];
	}

	public function getStoreSslDomain() {
		return $this->storeInfo['stores_ssl_domain'];
	}

	public function getStoreTemplate() {
		return $this->storeInfo['stores_template'];
	}

	public function ProductInventoryReportsListingQueryBeforeExecute(&$Qproducts) {
		global $appExtension;
		$isInventory = $appExtension->isInstalled('inventoryCenters') && $appExtension->isEnabled('inventoryCenters');
		$extInventoryCenters = $appExtension->getExtension('inventoryCenters');
		if ($isInventory && $extInventoryCenters->stockMethod == 'Store'){
			if (!Session::exists('all_stores')){
				$Qproducts->leftJoin('pib.ProductsInventoryBarcodesToStores b2s')
					->leftJoin('b2s.Stores s')
					->andWhere('s.stores_id = ?', (int)Session::get('current_store_id'));
			}
		}
	}

	public function ModuleConfigReaderModuleConfigLoad(&$configData, $moduleCode, $moduleType) {
		/*
		 * @TODO: Figure out selected stores?
		 */
		$Query = mysql_query('select ' .
				'configuration_key, ' .
				'configuration_value' .
				' from ' .
				'stores_modules_configuration' .
				' where ' .
				'module_code = "' . $moduleCode . '"' .
				' and ' .
				'module_type = "' . $moduleType . '"' .
				' and ' .
				'store_id = "' . $this->storeInfo['stores_id'] . '"');
		if (mysql_num_rows($Query)){
			$cfgData = array();
			while($Result = mysql_fetch_assoc($Query)){
				$cfgData[$Result['configuration_key']] = $Result;
			}

			foreach($cfgData as $configKey => $cInfo){
				if (isset($configData[$configKey])){
					$configData[$configKey]['value'] = $cInfo['configuration_value'];
				}
			}
		}
	}

	public function CustomersListingQueryBeforeExecute(&$Qcustomers){
		$Qcustomers
			->leftJoin('c.CustomersToStores c2s')
			->whereIn('stores_id', Session::get('admin_showing_stores'));
	}

	public function ProductInventoryBarcodeHasInventoryQueryBeforeExecute($invData, &$Qcheck){
		global $appExtension, $Editor;
		if ($invData['use_store_inventory'] == '1'){
			$Qcheck->leftJoin('ib.ProductsInventoryBarcodesToStores b2s')
				->leftJoin('b2s.Stores');
			if ($appExtension->isAdmin()){
				if (is_object($Editor)){
					$Qcheck->andWhere('b2s.inventory_store_id = ?', $Editor->getData('store_id'));
				}else{
					$Qcheck->andWhereIn('b2s.inventory_store_id', Session::get('admin_showing_stores'));
				}
			}else{
				$Qcheck->andWhere('b2s.inventory_store_id = ?', Session::get('current_store_id'));
			}
		}
	}

	public function ProductInventoryBarcodeGetInventoryItemsQueryBeforeExecute($invData, &$Qcheck){
		$Qcheck->leftJoin('ib.ProductsInventoryBarcodesToStores ib2s');
	}

	public function ProductInventoryBarcodeGetInventoryItemsArrayPopulate($bInfo, &$barcodeArr){
		$barcodeArr['store_id'] = $bInfo['ProductsInventoryBarcodesToStores']['stores_id'];
	}

	public function InsertOrderedProductBeforeSave(&$newOrdersProduct, ShoppingCartProduct $cartProduct){
		$Store = $this->getStoresArray(Session::get('current_store_id'));
		if ($Store->StoreToStorePaymentSettings->use_global == '1'){
			$Store = $this->getStoresArray(1);
		}

		$PaymentInfo = json_decode($Store->StoreToStorePaymentSettings->payment_settings);
		$paymentType = 'expense';
		$ProductType = $cartProduct->getProductClass()->getProductTypeClass()->getCode();

		$ProductSettings = $PaymentInfo->productTypes->$ProductType->{$cartProduct->getData('purchase_type')};

		$NewPayment =& $newOrdersProduct->StoreToStorePayments;
		$NewPayment->payment_status = '0';

		switch($paymentType){
			case 'expense':
				$NewPayment->to_store_id = 1;
				$NewPayment->from_store_id = Session::get('current_store_id');
				$percent = ($ProductSettings->expense / 100);
				break;
			case 'income':
				$NewPayment->to_store_id = Session::get('current_store_id');
				$NewPayment->from_store_id = 1;
				$percent = ($ProductSettings->income / 100);
				break;
		}
		$NewPayment->payment_amount = ($cartProduct->getFinalPrice() * $percent);
	}

	public function CustomerInfoAddTableContainer($Customer){
		$storeDrop = htmlBase::newElement('selectbox')
			->setName('customers_store_id')
			->addOption('', sysLanguage::get('TEXT_PLEASE_SELECT'))
			->selectOptionByValue($Customer->CustomersToStores->stores_id);
		foreach($this->getStoresArray() as $sInfo){
			$storeDrop->addOption($sInfo['stores_id'], $sInfo['stores_name']);
		}

		return '<div class="main" style="margin-top:.5em;font-weight:bold;">Customers Store</div><div class="ui-widget ui-widget-content ui-corner-all" style="padding:.5em;">'.$storeDrop->draw().'</div>';
	}

	public function NewCustomerAccountBeforeExecute(&$newUser){
		$newUser->CustomersToStores->stores_id = $_POST['customers_store_id'];
	}

	public function PurchaseTypeLoadDataQuery(&$Query){
		$Query->leftJoin('pt.ProductsPurchaseTypesToStores pt2s');
	}

	public function PurchaseTypeLoadData($pInfo, &$data){
		foreach($pInfo['ProductsPurchaseTypesToStores'] as $sInfo){
			$data[$sInfo['stores_id']] = array(
				'price' => $sInfo['price'],
				'tax_class_id' => $sInfo['tax_class_id']
			);
		}
	}

	public function PurchaseTypeGetPrice($PurchaseType, &$return){
		$return = $PurchaseType->getData('price', Session::get('current_store_id'));
	}

	public function PurchaseTypeGetTaxId($PurchaseType, &$return){
		$return = $PurchaseType->getData('tax_class_id', Session::get('current_store_id'));
	}

	public function AdminProductPurchaseTypeOnSave($PurchaseType, &$pInfo){
		foreach($_POST['pricing'][$PurchaseType->getCode()] as $storeId => $pricing){
			if ($storeId == 'global') continue;

			if ($pricing['use_global'] == '0'){
				$pInfo->ProductsPurchaseTypesToStores[$storeId]->stores_id = $storeId;
				$pInfo->ProductsPurchaseTypesToStores[$storeId]->price = $pricing['price'];
				$pInfo->ProductsPurchaseTypesToStores[$storeId]->tax_class_id = $pricing['tax_class_id'];
			}else{
				if (isset($pInfo->ProductsPurchaseTypesToStores[$storeId])){
					$pInfo->ProductsPurchaseTypesToStores[$storeId]->delete();
				}
			}
		}
	}

	public function runCronJob(){
		
	}
}

?>