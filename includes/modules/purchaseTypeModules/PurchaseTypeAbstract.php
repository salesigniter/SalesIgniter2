<?php
if (!class_exists('productInventory')){
	require(sysConfig::getDirFsCatalog() . 'includes/classes/product/Inventory.php');
}

class PurchaseTypeBase extends ModuleBase
{

	public $productInfo;

	/**
	 * @var ProductInventory
	 */
	public $inventoryCls = null;

	private $installed = true;

	private $data = array();

	private $cachedHasInventory = null;

	public function init($code, $forceEnable = false, $moduleDir = false) {
		$this->setModuleType('purchaseType');
		parent::init($code, $forceEnable, $moduleDir);
	}

	/*
	 * Used to load only the purchase type data stored for the product/purchase type
	 */
	public function loadData($productId) {
		if ($productId !== false){
			$Qdata = Doctrine_Query::create()
				->from('ProductsPurchaseTypes pt')
				->where('pt.products_id = ?', $productId)
				->andWhere('pt.type_name = ?', $this->getCode());

			EventManager::notify('PurchaseTypeLoadDataQuery', &$Qdata);

			$Result = $Qdata->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
			if ($Result && sizeof($Result) > 0){
				$data = array(
					'global' => array(
						'status' => $Result[0]['status'],
						'type_name' => $Result[0]['type_name'],
						'price' => $Result[0]['price'],
						'products_id' => $productId,
						'tax_class_id' => $Result[0]['tax_class_id'],
						'inventory_controller' => $Result[0]['inventory_controller'],
						'inventory_track_method' => $Result[0]['inventory_track_method']
					)
				);

				EventManager::notify('PurchaseTypeLoadData', $Result[0], &$data);

				$this->data = $data;
			}
		}
	}

	/*
		 * Used to load only the inventory data stored for the product/purchase type
		 */
	public function loadInventoryData($productId, $invController = false) {
		$this->inventoryCls = new ProductInventory($productId, $this->data['global']);

		EventManager::notify('PurchaseTypeLoadInventoryData', $productId, $invController, $this);
	}

	/*
		 * Used to load everything related to a purchase type ( mainly only used on the catalog side of the cart )
		 */
	public function loadProduct($productId) {
		if ($this->isEnabled() === true){
			$this->loadData($productId);
			$this->loadInventoryData($productId);

			EventManager::notify('PurchaseTypeLoadProduct', $productId, $this);
		}
	}

	public function onReturn() {
	}

	public function onShip() {
	}

	public function setProductInfo($key, $val) {
		$this->productInfo[$key] = $val;
	}

	public function hasData($key, $part = 'global'){
		return isset($this->data[$part][$key]);
	}

	public function getData($key, $part = 'global', $defaultToGlobal = true) {
		if (!isset($this->data[$part]) && $part != 'global' && $defaultToGlobal === true){
			$part = 'global';
		}

		if (isset($this->data[$part][$key])){
			return $this->data[$part][$key];
		}
		return null;
	}

	public function onInstall(&$module, &$moduleConfig) {
	}

	public function check() {
		return ($this->isInstalled() === true);
	}

	public function shoppingCartAfterProductName(&$cartProduct) {
		return '';
	}

	public function checkoutAfterProductName(&$cartProduct) {
		return '';
	}

	public function orderAfterEditProductName(&$orderedProduct) {
		return '';
	}

	public function orderAfterProductName(&$orderedProduct) {
		return '';
	}

	public function processAddToOrder(&$pInfo) {
	}

	public function processAddToCart(&$pInfo) {
		$pInfo['price'] = $this->getData('price');
		$pInfo['final_price'] = $this->getData('price');

		EventManager::notify('PurchaseTypeAddToCart', $this->getCode(), &$pInfo, $this->productInfo);
	}

	public function processUpdateCart(&$pInfo) {
	}

	public function processRemoveFromCart() {
	}

	public function onInsertOrderedProduct($cartProduct, $orderId, &$orderedProduct, &$products_ordered) {
	}

	public function &getInventoryClass() {
		return $this->inventoryCls;
	}

	public function getProductId() {
		return $this->getData('products_id');
	}

	public function getPrice() {
		$return = $this->getData('price');
		EventManager::notify('PurchaseTypeGetPrice', $this, &$return);
		return $return;
	}

	public function getTaxId() {
		$return = $this->getData('tax_class_id');
		EventManager::notify('PurchaseTypeGetTaxId', $this, &$return);
		return $return;
	}

	public function getTaxClassId(){
		return $this->getTaxId();
	}

	public function getTaxRate() {
		return tep_get_tax_rate($this->getTaxId());
	}

	public function displayPrice() {
		global $currencies, $appExtension;
		if (isset($this->productInfo['special_price'])){
			$extSpecials = $appExtension->getExtension('specials');
			$display = $currencies->display_price($this->getPrice(), $this->getTaxRate());
			$extSpecials->ProductNewPriceBeforeDisplay($this->productInfo['special_price'], $display);
			return $display;
		}
		else {
			return $currencies->display_price($this->getPrice(), $this->getTaxRate());
		}
	}

	public function canUseSpecial() {
		return true;
	}

	public function canUseInventory(){
		if ($this->isEnabled() === false){
			return false;
		}
		return (is_null($this->inventoryCls) === false);
	}

	public function updateStock($orderId, $orderProductId, &$cartProduct) {
		if ($this->canUseInventory() === false){
			return true;
		}
		return $this->getInventoryClass()->updateStock($orderId, $orderProductId, &$cartProduct);
	}

	public function getTrackMethod() {
		if ($this->canUseInventory() === false){
			return null;
		}
		return $this->getInventoryClass()->getTrackMethod();
	}

	public function getCurrentStock() {
		if ($this->canUseInventory() === false){
			return null;
		}
		return $this->getInventoryClass()->getCurrentStock();
	}

	public function hasInventory() {
		if ($this->canUseInventory() === false){
			return ($this->isEnabled());
		}
		if ($this->cachedHasInventory !== null){
			return $this->cachedHasInventory;
		}
		$this->cachedHasInventory = $this->getInventoryClass()->hasInventory();
		return $this->cachedHasInventory;
	}

	public function getInventoryItems() {
		if ($this->canUseInventory() === false){
			return ($this->isEnabled());
		}
		return $this->getInventoryClass()->getInventoryItems();
	}

	public function getPurchaseHtml($key) {
		global $userAccount;
		$return = null;
		switch($key){
			case 'product_info':
				$button = htmlBase::newElement('button')
					->setType('submit')
					->setName('buy_' . $this->getCode() . '_product')
					->setText(sysLanguage::get('TEXT_BUTTON_BUY'));

				$allowQty = ($this->getConfigData('ALLOW_MULTIPLE_IN_CART') == 'True' && $this->getConfigData('ALLOWED_PRODUCT_INFO_QUANTITY_FIELD') == 'True');
				if ($this->hasInventory() === false){
					$allowQty = false;
					switch($this->getConfigData('OUT_OF_STOCK_PRODUCT_INFO_DISPLAY')){
						case 'Disable Button':
							$button->disable();
							break;
						case 'Out Of Stock Text':
							$button = htmlBase::newElement('span')
								->addClass('outOfStockText')
								->html(sysLanguage::get('TEXT_OUT_OF_STOCK'));
							break;
						case 'Hide Box':
							return null;
							break;
					}
				}

				if ($this->getConfigData('LOGIN_REQUIRED') == 'True'){
					if ($userAccount->isLoggedIn() === false){
						$allowQty = false;
						$button = htmlBase::newElement('button')
							->setHref(itw_app_link(null, 'account', 'login'))
							->setText(sysLanguage::get('TEXT_LOGIN_REQUIRED'));
					}
				}

				$content = htmlBase::newElement('span')
					->css(array(
						'font-size' => '1.5em',
						'font-weight' => 'bold'
					))
					->html($this->displayPrice());

				$return = array(
					'form_action' => itw_app_link(tep_get_all_get_params(array('action'))),
					'purchase_type' => $this->getCode(),
					'allowQty' => $allowQty,
					'header' => $this->getTitle(),
					'content' => $content->draw(),
					'button' => $button
				);
				break;
		}
		return $return;
	}

	public function getOrderedProductBarcode($pInfo){
		return $pInfo['ProductsInventoryBarcodes']['barcode'];
	}

	public function displayOrderedProductBarcode($pInfo){
		return $pInfo['ProductsInventoryBarcodes']['barcode'];
	}
}

?>