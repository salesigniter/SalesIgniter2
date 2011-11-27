<?php
class ProductTypeStandard extends ModuleBase
{

	private $_moduleCode = 'standard';

	private $purchaseTypes = array();

	private $cartPurchaseType = '';

	private $checked = array();

	private $info = array(
		'id' => 0
	);

	private $purchaseTypeModules = array();

	public function __construct() {
		/*
		 * Default title and description for modules that are not yet installed
		 */
		$this->setTitle('Standard Product Type');
		$this->setDescription('Standard Product Type');

		$this->init($this->_moduleCode);
	}

	public function init($forceEnable = false) {
		$this->import(new Installable);

		$this->setModuleType('productType');

		parent::init($this->_moduleCode, $forceEnable);
	}

	public function setProductId($val) {
		$this->info['id'] = $val;
	}

	public function getProductId() {
		return $this->info['id'];
	}

	public function getPurchaseTypeCode($PurchaseType) {
		$return = false;
		if ($PurchaseType !== false && !empty($PurchaseType)){
			$return = $PurchaseType;
		}
		elseif (!empty($this->cartPurchaseType)) {
			$return = $this->cartPurchaseType;
		}
		return $return;
	}

	public function getProductPrice($PurchaseType = false) {
		$return = '';
		if (($PurchaseType = $this->getPurchaseTypeCode($PurchaseType)) !== false){
			$return = $this->getPurchaseType($PurchaseType)->getPrice();
		}
		return $return;
	}

	public function getTaxClassId($PurchaseType = false) {
		$return = 0;
		if (($PurchaseType = $this->getPurchaseTypeCode($PurchaseType)) !== false){
			$return = $this->getPurchaseType($PurchaseType)->getTaxClassId();
		}
		return $return;
	}

	public function getTaxRate($PurchaseType = false) {
		$return = 0;
		if (($PurchaseType = $this->getPurchaseTypeCode($PurchaseType)) !== false){
			$return = $this->getPurchaseType($PurchaseType)->getTaxRate();
		}
		return $return;
	}

	public function purchaseTypeEnabled($PurchaseType) {
		if (!isset($this->checked[$PurchaseType])){
			$QproductTypes = mysql_query(
				'SELECT ' .
					'type_name' .
					' FROM ' .
					'products_purchase_types' .
					' WHERE ' .
					'type_name = "' . $PurchaseType . '"' .
					' AND ' .
					'status = 1' .
					' AND ' .
					'products_id = "' . (int)$this->getProductId() . '"'
			);

			$this->checked[$PurchaseType] = (mysql_num_rows($QproductTypes) > 0);
		}

		return $this->checked[$PurchaseType];
	}

	public function loadPurchaseType($PurchaseType = false) {
		$PurchaseType = $this->getPurchaseTypeCode($PurchaseType);
		if ($PurchaseType === false || $this->purchaseTypeEnabled($PurchaseType) === false){
			return;
		}

		if (!isset($this->purchaseTypes[$PurchaseType])){
			$this->purchaseTypes[$PurchaseType] = PurchaseTypeModules::getModule($PurchaseType);
			$this->purchaseTypes[$PurchaseType]->loadData($this->getProductId());
			$this->purchaseTypes[$PurchaseType]->loadInventoryData($this->getProductId());
		}
	}

	public function &getPurchaseType($PurchaseType = false) {
		$PurchaseType = $this->getPurchaseTypeCode($PurchaseType);
		if ($PurchaseType === false || $this->purchaseTypeEnabled($PurchaseType) === false){
			return null;
		}

		if (empty($this->purchaseTypes) || array_key_exists($PurchaseType, $this->purchaseTypes) === false){
			$this->loadPurchaseType($PurchaseType);
		}
		return $this->purchaseTypes[$PurchaseType];
	}

	public function setPurchaseTypes($val){
		$this->purchaseTypes = $val;
	}

	public function getPurchaseTypes($reload = false) {
		if (empty($this->purchaseTypes) || $reload === true){
			$purchaseTypes = array();
			$QproductTypes = mysql_query(
				'SELECT ' .
					'type_name' .
					' FROM ' .
					'products_purchase_types' .
					' WHERE ' .
					'status = 1' .
					' AND ' .
					'products_id = "' . (int)$this->getProductId() . '"'
			);
			while($ptInfo = mysql_fetch_assoc($QproductTypes)){
				$this->loadPurchaseType($ptInfo['type_name']);
			}
		}
		return $this->purchaseTypes;
	}

	public function allowAddToCart(&$CartProductData) {
		$allowed = true;
		$PurchaseType = $this->getPurchaseType($CartProductData['purchase_type']);
		if (
			$PurchaseType->getConfigData('INVENTORY_ENABLED') == 'True' &&
			$PurchaseType->getConfigData('INVENTORY_SHOPPING_CART_VERIFY') == 'True'
		){
			$allowed = ($PurchaseType->getCurrentStock() > $CartProductData['quantity']);
		}

		if (method_exists($PurchaseType, 'allowAddToCart')){
			$allowed = $PurchaseType->allowAddToCart(&$CartProductData);
		}
		return $allowed;
	}

	public function addToCartPrepare(&$CartProductData) {
		$PurchaseType = $this->getPurchaseType($_POST['purchase_type']);

		$qty = 1;
		if ($PurchaseType->getConfigData('ALLOW_MULTIPLE_IN_CART') == 'True' && isset($_POST['quantity'])){
			if (is_numeric($_POST['quantity'])){
				$qty = $_POST['quantity'];
			}
			elseif (is_array($_POST['quantity']) && isset($_POST['quantity'][$PurchaseType->getCode()])) {
				$qty = $_POST['quantity'][$PurchaseType->getCode()];
			}
		}

		$CartProductData['price'] = $PurchaseType->getPrice();
		$CartProductData['final_price'] = $PurchaseType->getPrice();
		$CartProductData['purchase_type'] = $PurchaseType->getCode();
		$CartProductData['quantity'] = $qty;
		$CartProductData['tax_class_id'] = $PurchaseType->getTaxClassId();

		if (method_exists($PurchaseType, 'addToCartPrepare')){
			$PurchaseType->addToCartPrepare(&$CartProductData);
		}
	}

	public function onCartProductLoad($CartProduct) {
		$this->loadPurchaseType($CartProduct->getData('purchase_type'));
		$this->cartPurchaseType = $CartProduct->getData('purchase_type');
	}

	public function showShoppingCartProductInfo($CartProduct) {
		$PurchaseTypeCls = $this->getPurchaseType($CartProduct->getData('purchase_type'));
		$purchaseTypeHtml = htmlBase::newElement('span')
			->css(array(
				'font-size' => '.8em',
				'font-style' => 'italic'
			))
			->html(' - Purchase Type: ' . ucfirst($CartProduct->getData('purchase_type')));

		$html = $purchaseTypeHtml->draw() . '<br>';
		if (method_exists($PurchaseTypeCls, 'showShoppingCartProductInfo')){
			$html .= $PurchaseTypeCls->showShoppingCartProductInfo($CartProduct);
		}

		return $html;
	}

	public function showOrderedProductInfo($OrderedProduct, $showExtraInfo = true) {
		$PurchaseTypeCls = $this->getPurchaseType($OrderedProduct->getInfo('purchase_type'));
		if(is_object($PurchaseTypeCls)){
			$purchaseTypeHtml = htmlBase::newElement('span')
				->css(array(
					'font-size' => '.8em',
					'font-style' => 'italic'
				))
				->html(' - Purchase Type: ' . $PurchaseTypeCls->getTitle());

			$html = $purchaseTypeHtml->draw() . '<br>';
			if (method_exists($PurchaseTypeCls, 'showOrderedProductInfo')){
				$html .= $PurchaseTypeCls->showOrderedProductInfo($OrderedProduct, $showExtraInfo);
			}
		}else{
			$purchaseTypeHtml = htmlBase::newElement('span')
				->css(array(
					'font-size' => '.8em',
					'font-style' => 'italic'
				))
				->html(' - Purchase Type: ' . $OrderedProduct->getInfo('purchase_type'));

			$html = $purchaseTypeHtml->draw() . '<br>';
		}

		return $html;
	}

	public function onInsertOrderedProduct(&$CartProduct, $orderID, &$orderedProduct, &$products_ordered) {
		$PurchaseType = $this->getPurchaseType($CartProduct->getInfo('purchase_type'));
		if (method_exists($PurchaseType, 'onInsertOrderedProduct')){
			$PurchaseType->onInsertOrderedProduct($CartProduct, $orderID, &$orderedProduct, &$products_ordered);
		}
	}

	public function getOrderedProductBarcode($pInfo) {
		$PurchaseType = $this->getPurchaseType($pInfo['purchase_type']);
		if (method_exists($PurchaseType, 'getOrderedProductBarcode')){
			$return = $PurchaseType->getOrderedProductBarcode($pInfo);
		}
		else {
			$return = $pInfo['barcode_id'];
		}
		return $return;
	}

	public function displayOrderedProductBarcode($pInfo) {
		$PurchaseType = $this->getPurchaseType($pInfo['purchase_type']);
		if (method_exists($PurchaseType, 'displayOrderedProductBarcode')){
			$return = $PurchaseType->displayOrderedProductBarcode($pInfo);
		}
		else {
			$return = $pInfo['barcode_id'];
		}
		return $return;
	}

	public function canShowProductListing() {
		$result = true;
		if (sysConfig::get('PRODUCT_LISTING_HIDE_NO_INVENTORY') == 'True'){
			$result = false;
			foreach($this->getPurchaseTypes() as $k => $pType){
				if ($pType->hasInventory() === true){
					$result = true;
					break;
				}
			}
		}
		return $result;
	}

	public function showProductListing($col) {
		$return = false;
		switch($col){
			case 'productsPriceNew':
				$tableRow = array();

				$buyNowButton = htmlBase::newElement('button')
					->setText(sysLanguage::get('TEXT_BUTTON_BUY_NOW'))
					->setHref(itw_app_link(tep_get_all_get_params(array('action', 'products_id')) . 'action=addCartProduct&products_id=' . $this->getProductId()), true);

				foreach($this->getPurchaseTypes() as $k => $pType){
					if ($k == 'new' && $pType->hasInventory()){
						if (sizeof($tableRow) <= 0){
							$tableRow[] = '<tr>
    	               <td class="main">Buy ' . $pType->getTitle() . ':</td>
    	               <td class="main">' . $pType->displayPrice() . '</td>
    	              </tr>
    	              <tr>
    	               <td class="main" colspan="2">' . $buyNowButton->draw() . '</td>
    	              </tr>';
						}
						else {
							array_unshift($tableRow, '<tr>
    	               <td class="main"></td>
    	               <td class="main">' . $pType->getTitle() . ':</td>
    	               <td class="main">' . $pType->displayPrice() . '</td>
    	               <td class="main" style="font-size:.8em;">' . $buyNowButton->draw() . '</td>
    	              </tr>');
						}
					}
					elseif ($pType->hasInventory()) {
						$purchaseTypeHtml = $pType->getPurchaseHtml('product_listing_row');
						if (is_null($purchaseTypeHtml) === false){
							$tableRow[] = $purchaseTypeHtml;
						}
						else {
							/*$tableRow[] = '<tr>
        	   	    <td class="main"></td>
        	   	    <td class="main">' . $pType->getTitle() . ':</td>
        	   	    <td class="main">' . $pType->displayPrice() . '</td>
        	   	    <td class="main" style="font-size:.8em;">' . $buyNowButton->draw() . '</td>
        	   	   </tr>';*/
						}
					}
				}
				ksort($tableRow);

				if (sizeof($tableRow) > 0){
					$return = '<table cellpadding="2" cellspacing="0" border="0">' .
						implode('', $tableRow) .
						'</table>';
				}
				break;
			default:
				$purchaseTypes = $this->getPurchaseTypes();
				foreach($purchaseTypes as $k => $pType){
					if (method_exists($pType, 'showProductListing')){
						$return .= $pType->showProductListing($col);
					}
				}
				break;
		}
		return $return;
	}

	public function getExcludedPurchaseTypes(OrderCreatorProduct $OrderedProduct) {
		global $Editor;
		$excludedTypes = array();
		/*foreach($Editor->ProductManager->getContents() as $Product){
			if ($OrderedProduct->getProductsId() == $Product->getProductsId() && $OrderedProduct->getId() != $Product->getId()){
				if ($Product->getPurchaseType() == 'reservation'){
					$excludedTypes[] = 'reservation';
				}
			}
		} */
		return $excludedTypes;
	}

	public function processAddToOrder($pInfo) {
		$this->cartPurchaseType = $pInfo['purchase_type'];
	}

	public function OrderProductOnInit($pInfo) {
		$this->cartPurchaseType = $pInfo['purchase_type'];
	}

	public function OrderCreatorBarcodeEdit(OrderCreatorProduct $OrderedProduct) {
		$return = '';
		$PurchaseType = PurchaseTypeModules::getModule('reservation');
		$PurchaseType->loadProduct($OrderedProduct->getProductsId());
		if ($PurchaseType && $PurchaseType->getTrackMethod() == 'barcode' && $PurchaseType->hasInventory()){
			$barcodeInput = htmlBase::newElement('selectbox')
				->addClass('ui-widget-content barcode')
				->setName('product[' . $OrderedProduct->getId() . '][barcode_id]');
			$barcodeInput->addOption('', sysLanguage::get('TEXT_PLEASE_SELECT'));

			EventManager::attachEvent('ProductInventoryBarcodeGetInventoryItemsQueryBeforeExecute', function ($invData, &$Qcheck){
				global $appExtension, $Editor;
					$MultiStore = $appExtension->getExtension('multiStore');
					if ($MultiStore && $MultiStore->isEnabled() === true){
						$Qcheck->andWhere('ib2s.inventory_store_id = ?', $Editor->getData('store_id'));
					}
				});
			foreach($PurchaseType->getInventoryItems() as $k => $bInfo){
				$barcodeInput->addOption($bInfo['id'], $bInfo['barcode']);
			}
			$barcodeInput->selectOptionByValue($OrderedProduct->getInfo('barcode_id'));

			$return = $barcodeInput->draw();
		}
		return $return;
	}

	public function OrderCreatorAfterProductName(OrderCreatorProduct $OrderedProduct) {
		$return = '';
		$PurchaseType = $this->getPurchaseTypeCode(false);
		if ($PurchaseType != 'membership'){
			$PurchaseTypes = $this->getPurchaseTypes(true);
			$excludedPurchaseTypes = $this->getExcludedPurchaseTypes($OrderedProduct);

			$purchaseTypeInput = htmlBase::newElement('selectbox')
				->addClass('ui-widget-content purchaseType')
				->setName('product[' . $OrderedProduct->getId() . '][purchase_type]');
			$purchaseTypeInput->addOption('', sysLanguage::get('TEXT_PLEASE_SELECT'));
			foreach($PurchaseTypes as $k => $pType){
				if (!in_array($k, $excludedPurchaseTypes)){
					$purchaseTypeInput->addOption($pType->getCode(), $pType->getTitle());
				}
			}
			$purchaseTypeInput->selectOptionByValue($PurchaseType);

			$return = '<br><nobr><small>&nbsp;<i> - Purchase Type: ' . $purchaseTypeInput->draw() . '</i></small></nobr>';
			if ($PurchaseType != ''){
				$PurchaseTypeClass = $this->getPurchaseType();
				if (method_exists($PurchaseTypeClass, 'OrderCreatorAfterProductName')){
					$return .= $PurchaseTypeClass->OrderCreatorAfterProductName($OrderedProduct);
				}
			}
		}
		return $return;
	}

	public function updateOrderCreatorProductInfo(&$pInfo) {
		if (isset($_GET['purchase_type']) && !empty($_GET['purchase_type'])){
			$pInfo['purchase_type'] = $_GET['purchase_type'];

			$this->cartPurchaseType = $pInfo['purchase_type'];
			$PurchaseType = PurchaseTypeModules::getModule($pInfo['purchase_type']);
			$PurchaseType->loadProduct($pInfo['products_id']);
			$pInfo['products_price'] = $PurchaseType->getPrice();
			$pInfo['final_price'] = $PurchaseType->getPrice();

			$PurchaseType->processAddToOrder(&$pInfo);
		}
	}

	public function addToOrdersProductCollection(OrderCreatorProduct $OrderProduct, &$OrderedProduct) {
		$OrderedProduct->purchase_type = $OrderProduct->getInfo('purchase_type');

		$PurchaseType =& $this->getPurchaseType();
		if (method_exists($PurchaseType, 'addToOrdersProductCollection')){
			$PurchaseType->addToOrdersProductCollection($OrderProduct, $OrderedProduct);
		}
	}

	public function OrderCreatorAllowAddToContents(OrderCreatorProduct $OrderProduct) {
		$return = true;
		if ($OrderProduct->hasInfo('purchaseType')){
			$PurchaseType = $this->getPurchaseType($OrderProduct->getInfo('purchaseType'));
			if (method_exists($PurchaseType, 'OrderCreatorAllowAddToContents')){
				$return = $PurchaseType->OrderCreatorAllowAddToContents($OrderProduct);
			}
		}
		return $return;
	}

	public function OrderCreatorAllowProductUpdate(OrderCreatorProduct $OrderProduct) {
		$return = true;
		if (isset($_GET['purchase_type']) && !empty($_GET['purchase_type'])){
			$PurchaseType = $this->getPurchaseType($_GET['purchase_type']);
			if (method_exists($PurchaseType, 'OrderCreatorAllowProductUpdate')){
				$return = $PurchaseType->OrderCreatorAllowProductUpdate($OrderProduct);
			}
		}
		return $return;
	}

	public function onUpdateCartFromPost(ShoppingCartProduct &$CartProduct) {
		$PurchaseType = $this->getPurchaseType();
		$desiredQty = $_POST['cart_quantity'][$CartProduct->getId()];

		$allowUpdate = true;
		if ($PurchaseType->getConfigData('ALLOW_MULTIPLE_IN_CART') == 'False'){
			if ($desiredQty > 1){
				$allowUpdate = false;
			}
		}

		if ($allowUpdate === true){
			if ($PurchaseType->getConfigData('INVENTORY_ENABLED') == 'True'){
				if ($PurchaseType->getConfigData('INVENTORY_SHOPPING_CART_VERIFY') == 'True'){
					$allowUpdate = ($PurchaseType->getCurrentStock() >= $desiredQty);
				}
			}
		}

		if ($allowUpdate === true){
			$CartProduct->updateData('quantity', $desiredQty);
		}
	}

	public function getCartQuantityHtml(ShoppingCartProduct &$CartProduct) {
		$html = '';
		$PurchaseType = $this->getPurchaseType();

		if (method_exists($PurchaseType, 'getCartQuantityHtml')){
			$html = $PurchaseType->getCartQuantityHtml($CartProduct);
		}
		else {
			if ($PurchaseType->getConfigData('ALLOW_MULTIPLE_IN_CART') == 'True'){
				$html = htmlBase::newElement('input')
					->addClass('quantity')
					->attr('data-id', $CartProduct->getId())
					->attr('size', 4)
					->setName('cart_quantity[' . $CartProduct->getId() . ']')
					->val($CartProduct->getQuantity())
					->draw();
			}
			else {
				$html = htmlBase::newElement('input')
					->setType('hidden')
					->setName('cart_quantity[' . $CartProduct->getId() . ']')
					->val($CartProduct->getQuantity())
					->draw() . $CartProduct->getQuantity();
			}
		}
		return $html;
	}
}
