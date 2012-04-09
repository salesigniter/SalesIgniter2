<?php
if (!class_exists('productInventory')){
	require(sysConfig::getDirFsCatalog() . 'includes/classes/product/Inventory.php');
}

class PurchaseTypeBase extends ModuleBase
{

	/**
	 * @var array
	 */
	public $productInfo = array();

	/**
	 * @var ProductInventory
	 */
	public $inventoryCls = null;

	/**
	 * @var bool
	 */
	private $installed = true;

	/**
	 * @var array
	 */
	private $data = array();

	/**
	 * @var bool
	 */
	private $cachedHasInventory = null;

	/**
	 * @param string $code
	 * @param bool $forceEnable
	 * @param bool|string $moduleDir
	 */
	public function init($code, $forceEnable = false, $moduleDir = false) {
		$this->import(new Installable);

		$this->setModuleType('purchaseType');
		parent::init($code, $forceEnable, $moduleDir);
	}

	/**
	 * @param int $productId
	 *
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

	/**
	 * @param int $productId
	 * @param bool $invController
	 *
	 * Used to load only the inventory data stored for the product/purchase type
	 */
	public function loadInventoryData($productId, $invController = false) {
		$this->inventoryCls = new ProductInventory($productId, $this->data['global']);

		EventManager::notify('PurchaseTypeLoadInventoryData', $productId, $invController, $this);
	}

	/**
	 * @param int $productId
	 *
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

	/**
	 * @param string $key
	 * @param mixed $val
	 */
	public function setProductInfo($key, $val) {
		$this->productInfo[$key] = $val;
	}

	/**
	 * @param string $key
	 * @param string $part
	 * @return bool
	 */
	public function hasData($key, $part = 'global'){
		return isset($this->data[$part][$key]);
	}

	/**
	 * @param string $key
	 * @param string $part
	 * @param bool $defaultToGlobal
	 * @return array|null
	 */
	public function getData($key, $part = 'global', $defaultToGlobal = true) {
		if (!isset($this->data[$part]) && $part != 'global' && $defaultToGlobal === true){
			$part = 'global';
		}

		if (isset($this->data[$part][$key])){
			return $this->data[$part][$key];
		}
		return null;
	}

	/**
	 * @param $module
	 * @param $moduleConfig
	 */
	public function onInstall(&$module, &$moduleConfig) {
	}

	/**
	 * @return bool
	 */
	public function check() {
		return ($this->isInstalled() === true);
	}

	/**
	 * @param ShoppingCartProduct $cartProduct
	 * @return string
	 */
	public function shoppingCartAfterProductName(ShoppingCartProduct &$cartProduct) {
		return '';
	}

	/**
	 * @param ShoppingCartProduct $cartProduct
	 * @return string
	 */
	public function checkoutAfterProductName(ShoppingCartProduct &$cartProduct) {
		return '';
	}

	/**
	 * @param OrderedProduct $orderedProduct
	 * @return string
	 */
	public function orderAfterEditProductName(OrderedProduct &$orderedProduct) {
		return '';
	}

	/**
	 * @param OrderedProduct $orderedProduct
	 * @return string
	 */
	public function orderAfterProductName(OrderedProduct &$orderedProduct) {
		return '';
	}

	/**
	 * @param array $pInfo
	 */
	public function processAddToOrder(array &$pInfo) {
	}

	/**
	 * @param array $CartProductData
	 */
	public function addToCartPrepare(array &$CartProductData){
	}

	/**
	 * @param array $pInfo
	 */
	public function processUpdateCart(array &$pInfo) {
	}

	public function processRemoveFromCart() {
	}

	/**
	 * @param ShoppingCartProduct $cartProduct
	 * @param int $orderId
	 * @param OrdersProducts $orderedProduct
	 * @param string $products_ordered
	 */
	public function onInsertOrderedProduct(ShoppingCartProduct $cartProduct, $orderId, OrdersProducts &$orderedProduct, &$products_ordered) {
	}

	/**
	 * @return null|ProductInventory
	 */
	public function &getInventoryClass() {
		return $this->inventoryCls;
	}

	/**
	 * @return int|null
	 */
	public function getProductId() {
		return $this->getData('products_id');
	}

	/**
	 * @return float|null
	 */
	public function getPrice() {
		$return = $this->getData('price');
		EventManager::notify('PurchaseTypeGetPrice', $this, &$return);
		return $return;
	}

	/**
	 * @return int|null
	 */
	public function getTaxId() {
		$return = $this->getData('tax_class_id');
		EventManager::notify('PurchaseTypeGetTaxId', $this, &$return);
		return $return;
	}

	/**
	 * @return int|null
	 */
	public function getTaxClassId(){
		return $this->getTaxId();
	}

	/**
	 * @return float
	 */
	public function getTaxRate() {
		return tep_get_tax_rate($this->getTaxId());
	}

	/**
	 * @return string
	 */
	public function displayPrice() {
		global $currencies;
		return $currencies->display_price($this->getPrice(), $this->getTaxRate());
	}

	/**
	 * @return bool
	 */
	public function canUseInventory(){
		if ($this->isEnabled() === false){
			return false;
		}
		return (is_null($this->inventoryCls) === false);
	}

	/**
	 * @param int $orderId
	 * @param int $orderProductId
	 * @param ShoppingCartProduct $cartProduct
	 * @return bool
	 */
	public function updateStock($orderId, $orderProductId, ShoppingCartProduct &$cartProduct) {
		if ($this->canUseInventory() === false){
			return true;
		}
		return $this->getInventoryClass()->updateStock($orderId, $orderProductId, &$cartProduct);
	}

	/**
	 * @return bool|null
	 */
	public function getTrackMethod() {
		if ($this->canUseInventory() === false){
			return null;
		}
		return $this->getInventoryClass()->getTrackMethod();
	}

	/**
	 * @return int|null
	 */
	public function getCurrentStock() {
		if ($this->canUseInventory() === false){
			return null;
		}
		return $this->getInventoryClass()->getCurrentStock();
	}

	/**
	 * @return bool
	 */
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

	/**
	 * @param bool $includeUnavailable
	 * @return array|bool
	 */
	public function getInventoryItems($includeUnavailable = false) {
		if ($this->canUseInventory() === false){
			return array();
		}
		return $this->getInventoryClass()->getInventoryItems($includeUnavailable);
	}

	/**
	 * @return mixed
	 */
	public function getInvUnavailableStatus() {
		return $this->getInventoryClass()->getInvUnavailableStatus();
	}

	/**
	 * @param $val
	 * @return mixed
	 */
	public function setInvUnavailableStatus($val) {
		return $this->getInventoryClass()->setInvUnavailableStatus($val);
	}
	
	/**
	 * @param string $key
	 * @return array|null
	 */
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

	/**
	 * @param array $pInfo
	 * @return array
	 */
	public function getOrderedProductBarcodes(array $pInfo){
		return $pInfo['Barcodes'];
	}

	/**
	 * @param array $pInfo
	 * @return string
	 */
	public function displayOrderedProductBarcodes(array $pInfo){
		$return = '';
		foreach($pInfo['Barcodes'] as $bInfo){
			$return .= ' - ' . $bInfo['ProductsInventoryBarcodes']['barcode'] . '<br>';
		}
		return $return;
	}

	public function processProductImport($ProductType, &$Product, $item){
		$PurchaseTypes =& $Product->ProductsPurchaseTypes;
		if (
			isset($item['v_' . $ProductType . '_' . $this->getCode() . '_status']) &&
			$item['v_' . $ProductType . '_' . $this->getCode() . '_status'] == '1'
		){
			foreach($this->getExportTableColumns() as $k){
				$PurchaseTypes[$this->getCode()]->$k = $item['v_' . $ProductType . '_' . $this->getCode() . '_' . $k];
			}
		}elseif (isset($PurchaseTypes[$this->getCode()])){
			$PurchaseTypes[$this->getCode()]->delete();
		}
	}

	public function getExportTableColumns(){
		return array(
			'status',
			'price',
			'inventory_controller',
			'inventory_track_method',
			'tax_class_id'
		);
	}

	public function addExportQueryConditions($ProductType, &$QfileLayout){
		foreach($this->getExportTableColumns() as $k => $col){
			$tableAlias = 'pt_' . $this->getCode() . '_' . $k;

			$QfileLayout->addSelect('(SELECT
				' . $tableAlias . '.' . $col . '
			FROM
				ProductsPurchaseTypes ' . $tableAlias . '
			WHERE
				' . $tableAlias . '.products_id = p.products_id
			AND
				' . $tableAlias . '.type_name = "' . $this->getCode() . '"
			) as v_' . $ProductType . '_' . $this->getCode() . '_' . $col);
		}
	}

	public function addExportHeaderColumns($ProductType, &$HeaderRow){
		foreach($this->getExportTableColumns() as $k){
			$HeaderRow->addColumn('v_' . $ProductType . '_' . $this->getCode() . '_' . $k);
		}
	}

	public function addExportRowColumns($ProductType, &$CurrentRow, $pInfo){
		foreach($this->getExportTableColumns() as $k){
			$CurrentRow->addColumn(
				$pInfo['v_' . $ProductType . '_' . $this->getCode() . '_' . $k],
				'v_' . $ProductType . '_' . $this->getCode() . '_' . $k
			);
		}
	}

	public function addInventoryExportHeaders(&$headerCols){
	}

	public function addInventoryExportData(&$Data, $Product){

	}

	public function OrderCreatorOnAddToContents(OrderCreatorProduct $orderProduct){
	}

	public function showProductListing($col, $options = array()){
	}
}
?>