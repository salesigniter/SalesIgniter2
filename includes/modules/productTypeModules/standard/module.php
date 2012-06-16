<?php
define('IMPORT_MODE', '');
class ProductTypeStandard extends ProductTypeBase
{

	protected $_moduleCode = 'standard';

	protected $purchaseTypes = array();

	protected $cartPurchaseType = '';

	protected $checked = array();

	protected $info = array(
		'id'          => 0,
		'name'        => array(),
		'description' => array()
	);

	protected $purchaseTypeModules = array();

	protected $cachedInventoryId = array();

	public function __construct() {
		/*
		 * Default title and description for modules that are not yet installed
		 */
		$this->setTitle('Standard Product Type');
		$this->setDescription('Standard Product Type');

		$this->init($this->_moduleCode);
	}

	public function init($code, $forceEnable = false, $moduleDir = false) {
		$this->import(new Installable);

		$this->setModuleType('productType');

		parent::init($code, $forceEnable, $moduleDir);
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
			$ResultSet = Doctrine_Manager::getInstance()
				->getCurrentConnection()
				->fetchAll('SELECT * FROM products_purchase_types WHERE type_name = "' . $PurchaseType . '" AND status = 1 AND products_id = "' . (int)$this->getProductId() . '"');

			$this->checked[$PurchaseType] = (sizeof($ResultSet) > 0);
		}

		return $this->checked[$PurchaseType];
	}

	public function loadPurchaseType($PurchaseType = false, $ignoreStatus = false) {
		$PurchaseType = $this->getPurchaseTypeCode($PurchaseType);
		if ($PurchaseType === false || $this->purchaseTypeEnabled($PurchaseType) === false){
			if ($ignoreStatus === false){
				return null;
			}
		}

		if (!isset($this->purchaseTypes[$PurchaseType])){
			$this->purchaseTypes[$PurchaseType] = PurchaseTypeModules::getModule($PurchaseType);
			if ($this->purchaseTypes[$PurchaseType] === false){
				echo '<pre>';
				debug_print_backtrace();
				echo '</pre>';
				die('Error loading purchase type: ' . $PurchaseType);
			}
			$this->purchaseTypes[$PurchaseType]->loadData($this->getProductId());
			$this->purchaseTypes[$PurchaseType]->loadInventoryData($this->getProductId());
		}
	}

	/**
	 * @param bool $PurchaseType
	 * @param bool $ignoreStatus
	 * @return null
	 */
	public function &getPurchaseType($PurchaseType = false, $ignoreStatus = false) {
		$PurchaseType = $this->getPurchaseTypeCode($PurchaseType);
		if ($PurchaseType === false || $this->purchaseTypeEnabled($PurchaseType) === false){
			if ($ignoreStatus === false){
				$return = null;
				return $return;
			}
		}

		if (empty($this->purchaseTypes) || array_key_exists($PurchaseType, $this->purchaseTypes) === false){
			$this->loadPurchaseType($PurchaseType, $ignoreStatus);
		}
		return $this->purchaseTypes[$PurchaseType];
	}

	public function setPurchaseType($val){
		$this->cartPurchaseType = $val;
	}

	public function setPurchaseTypes($val) {
		$this->purchaseTypes = $val;
	}

	public function getPurchaseTypes($reload = false) {
		//if (empty($this->purchaseTypes) || $reload === true){
		$purchaseTypes = array();
		$ResultSet = Doctrine_Manager::getInstance()
			->getCurrentConnection()
			->fetchAssoc('SELECT type_name FROM products_purchase_types WHERE status = 1 AND products_id = "' . (int)$this->getProductId() . '"');
		foreach($ResultSet as $ptInfo){
			$this->loadPurchaseType($ptInfo['type_name']);
		}
		//}
		return $this->purchaseTypes;
	}

	public function allowAddToCart(&$CartProductData) {
		global $messageStack;
		$allowed = true;
		$PurchaseType = $this->getPurchaseType($CartProductData['purchase_type']);
		if (
			$PurchaseType->getConfigData('INVENTORY_ENABLED') == 'True' &&
			$PurchaseType->getConfigData('INVENTORY_SHOPPING_CART_VERIFY') == 'True'
		){
			$allowed = ($PurchaseType->getCurrentStock() >= $CartProductData['quantity']);
		}

		if ($allowed === true && method_exists($PurchaseType, 'allowAddToCart')){
			$allowed = $PurchaseType->allowAddToCart();
		}else{
			if ($allowed === false){
				$messageStack->addSession('pageStack', 'The Product Does Not Have Enough Inventory.');
			}
		}
		return $allowed;
	}

	public function addToCartPrepare(&$CartProductData) {
		if (isset($CartProductData['purchase_type'])){
			$purType = $CartProductData['purchase_type'];
			$quantity = $CartProductData['quantity'];
		}
		elseif (isset($_POST['purchase_type'])) {
			$purType = $_POST['purchase_type'];
			$quantity = (isset($_POST['quantity']) ? $_POST['quantity'] : 1);
		}
		else {
			$purType = $_GET['purchase_type'];
			$quantity = 1;
		}
		$PurchaseType = $this->getPurchaseType($purType);

		$qty = 1;
		if ($PurchaseType->getConfigData('ALLOW_MULTIPLE_IN_CART') == 'True'){
			if (is_numeric($quantity)){
				$qty = $quantity;
			}
			elseif (is_array($quantity) && isset($quantity[$PurchaseType->getCode()])) {
				$qty = $quantity[$PurchaseType->getCode()];
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

	public function onCartProductLoad(ShoppingCartProduct &$CartProduct) {
		$this->loadPurchaseType($CartProduct->getData('purchase_type'));
		$this->cartPurchaseType = $CartProduct->getData('purchase_type');
	}

	public function showShoppingCartProductInfo(ShoppingCartProduct $CartProduct, $settings = array()) {
		$options = array_merge(array(
			'showPurchaseTypeName' => true
		), $settings);

		$html = '';
		$PurchaseTypeCls = $this->getPurchaseType($CartProduct->getData('purchase_type'));
		if ($options['showPurchaseTypeName'] === true){
			$purchaseTypeHtml = htmlBase::newElement('span')
				->css(array(
				'font-size'  => '.8em',
				'font-style' => 'italic'
			))
				->html(' - Purchase Type: ' . $PurchaseTypeCls->getTitle());

			$html .= $purchaseTypeHtml->draw();
		}
		if (method_exists($PurchaseTypeCls, 'showShoppingCartProductInfo')){
			$html .= $PurchaseTypeCls->showShoppingCartProductInfo($CartProduct, $settings);
		}

		return $html;
	}

	public function showOrderedProductInfo(OrderProduct $OrderedProduct, $showExtraInfo = true) {
		$PurchaseTypeCls = $this->getPurchaseType($OrderedProduct->getInfo('purchase_type'));
		if ($showExtraInfo === true){
			$purchaseTypeHtml = htmlBase::newElement('span')
				->css(array(
				'font-size'  => '.8em',
				'font-style' => 'italic'
			))
				->html(' - Purchase Type: ' . $PurchaseTypeCls->getTitle());

			$html = $purchaseTypeHtml->draw();
		}else{
			$html = '';
		}

		if (method_exists($PurchaseTypeCls, 'showOrderedProductInfo')){
			$html .= $PurchaseTypeCls->showOrderedProductInfo($OrderedProduct, $showExtraInfo);
		}

		return $html;
	}

	public function onInsertOrderedProduct(ShoppingCartProduct &$CartProduct, $orderID, OrdersProducts &$orderedProduct, &$products_ordered) {
		$PurchaseType = $this->getPurchaseType($CartProduct->getInfo('purchase_type'));

		$orderedProduct->purchase_type = $PurchaseType->getCode();
		$orderedProduct->save();

		if (method_exists($PurchaseType, 'onInsertOrderedProduct')){
			$PurchaseType->onInsertOrderedProduct($CartProduct, $orderID, &$orderedProduct, &$products_ordered);
		}
	}

	public function getOrderedProductBarcodes($pInfo) {
		$return = array();
		$PurchaseType = $this->getPurchaseType($pInfo['purchase_type']);
		if (method_exists($PurchaseType, 'getOrderedProductBarcodes')){
			$return = $PurchaseType->getOrderedProductBarcodes($pInfo);
		}
		return $return;
	}

	public function displayOrderedProductBarcodes(OrderProduct $OrderedProduct) {
		$return = '';
		$PurchaseType = $OrderedProduct->getProductTypeClass()->getPurchaseType();
		if (method_exists($PurchaseType, 'displayOrderedProductBarcodes')){
			$return = $PurchaseType->displayOrderedProductBarcodes($OrderedProduct);
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

	public function showProductListing($col, $options = array()) {
        $options = array_merge(array(
            'showBuyButton' => true
        ), $options);
		$return = false;
		switch($col){
			case 'productsPriceNew':
				$tableRow = array();

                if ($options['showBuyButton'] === true){
                    $buyNowButton = htmlBase::newElement('button')
                        ->setText(sysLanguage::get('TEXT_BUTTON_BUY_NOW'));
                }

				foreach($this->getPurchaseTypes() as $k => $pType){
                    if (isset($buyNowButton)){
                        $buyNowButton->setHref(itw_app_link(tep_get_all_get_params(array('action', 'products_id')) . 'action=addCartProduct&purchase_type=' . $pType->getCode() . '&products_id=' . $this->getProductId()), true);
                    }
					if ($k == 'new' && $pType->hasInventory()){
						if (sizeof($tableRow) <= 0){
							$tableRow[] = '<tr>
    	               <td class="main">Buy ' . $pType->getTitle() . ':</td>
    	               <td class="main">' . $pType->displayPrice() . '</td>
    	              </tr>
    	              ' . (isset($buyNowButton) ? '<tr>
    	               <td class="main" colspan="2">' . $buyNowButton->draw() . '</td>
    	              </tr>' : '');
						}
						else {
							array_unshift($tableRow, '<tr>
    	               <td class="main"></td>
    	               <td class="main">' . $pType->getTitle() . ':</td>
    	               <td class="main">' . $pType->displayPrice() . '</td>
    	               ' . (isset($buyNowButton) ? '<td class="main" style="font-size:.8em;">' . $buyNowButton->draw() . '</td>' : '') . '
    	              </tr>');
						}
					}
					elseif ($pType->hasInventory()) {
						$purchaseTypeHtml = $pType->getPurchaseHtml('product_listing_row');
						if (is_null($purchaseTypeHtml) === false){
							$tableRow[] = $purchaseTypeHtml;
						}
						else {
							$tableRow[] = '<tr>
        	   	    <td class="main"></td>
        	   	    <td class="main">' . $pType->getTitle() . ':</td>
        	   	    <td class="main">' . $pType->displayPrice() . '</td>
        	   	    ' . (isset($buyNowButton) ? '<td class="main" style="font-size:.8em;">' . $buyNowButton->draw() . '</td>' : '') . '
        	   	   </tr>';
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
				$return = '';
				foreach($purchaseTypes as $k => $pType){
					if (method_exists($pType, 'showProductListing')){
						$return .= $pType->showProductListing($col, $options);
					}
				}
				break;
		}
		return $return;
	}

	public function processAddToOrder($pInfo) {
		$this->setPurchaseType($pInfo['purchase_type']);
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

	public function onSaveProduct(Products &$Product) {
		if (isset($_POST['purchase_type'])){
			foreach($Product->ProductsPurchaseTypes as $pTypeObj){
				$pTypeObj->status = 0;
			}

			foreach($_POST['purchase_type'] as $pType){
				$PurchaseType = $this->getPurchaseType($pType, true);

				$Product->ProductsPurchaseTypes[$pType]->status = 1;
				$Product->ProductsPurchaseTypes[$pType]->type_name = $PurchaseType->getCode();

				if ($PurchaseType->configExists('INVENTORY_ENABLED') && $PurchaseType->getConfigData('INVENTORY_ENABLED') == 'True'){
					$Product->ProductsPurchaseTypes[$pType]->inventory_controller = 'normal';
					$Product->ProductsPurchaseTypes[$pType]->inventory_track_method = $_POST['track_method']['normal'][$pType];
				}

				if ($PurchaseType->configExists('PRICING_ENABLED') && $PurchaseType->getConfigData('PRICING_ENABLED') == 'True'){
					if (isset($_POST['pricing'][$pType])){
						$Product->ProductsPurchaseTypes[$pType]->price = $_POST['pricing'][$pType]['global']['price'];
						$Product->ProductsPurchaseTypes[$pType]->tax_class_id = $_POST['pricing'][$pType]['global']['tax_class_id'];
					}
				}

				EventManager::notify('AdminProductPurchaseTypeOnSave', $PurchaseType, $Product->ProductsPurchaseTypes[$pType]);
			}
		}
	}

	public function processProductImport(&$Product, $CurrentRow) {
		PurchaseTypeModules::loadModules();
		foreach(PurchaseTypeModules::getModules() as $PurchaseTypeModule){
			$PurchaseTypeModule->processProductImport($Product, $CurrentRow);
		}
	}

	public function addExportQueryConditions(&$QfileLayout) {
		PurchaseTypeModules::loadModules();
		foreach(PurchaseTypeModules::getModules() as $PurchaseTypeModule){
			$PurchaseTypeModule->addExportQueryConditions($this->getCode(), $QfileLayout);
		}
	}

	public function addExportHeaderColumns(&$headerRow) {
		PurchaseTypeModules::loadModules();
		foreach(PurchaseTypeModules::getModules() as $PurchaseTypeModule){
			$PurchaseTypeModule->addExportHeaderColumns($this->getCode(), &$headerRow);
		}
	}

	public function addExportRowColumns(&$CurrentRow, $Product) {
		PurchaseTypeModules::loadModules();
		foreach(PurchaseTypeModules::getModules() as $PurchaseTypeModule){
			$PurchaseTypeModule->addExportRowColumns($this->getCode(), $CurrentRow, $Product);
		}
	}

	public function addInventoryExportHeaders(&$headerCols) {
		if (!in_array('v_purchase_type', $headerCols)){
			$headerCols[] = 'v_purchase_type';
		}
		if (!in_array('v_barcode', $headerCols)){
			$headerCols[] = 'v_barcode';
		}
		if (!in_array('v_barcode_status', $headerCols)){
			$headerCols[] = 'v_barcode_status';
		}
		if (!in_array('v_quantity_available', $headerCols)){
			$headerCols[] = 'v_quantity_available';
		}
		if (!in_array('v_quantity_broken', $headerCols)){
			$headerCols[] = 'v_quantity_broken';
		}
		if (!in_array('v_quantity_out', $headerCols)){
			$headerCols[] = 'v_quantity_out';
		}
		if (!in_array('v_quantity_purchased', $headerCols)){
			$headerCols[] = 'v_quantity_purchased';
		}
		/*if (!in_array('v_quantity_reserved', $headerCols)){
			$headerCols[] = 'v_quantity_reserved';
		}*/
		if (!in_array('v_comments', $headerCols)){
			$headerCols[] = 'v_comments';
		}

		PurchaseTypeModules::loadModules();
		foreach(PurchaseTypeModules::getModules() as $PurchaseTypeModule){
			$PurchaseTypeModule->addInventoryExportHeaders(&$headerCols);
		}
	}

	public function addInventoryExportQueryConditions(&$QProducts) {
	}

	public function addInventoryExportData($ProductId, $ProductModel, &$ExportRows) {
		$Qinventory = Doctrine_Query::create()
			->from('ProductsInventory pi')
			->leftJoin('pi.ProductsInventoryBarcodes pib')
			->leftJoin('pib.ProductsInventoryBarcodesComments pibc')
			->leftJoin('pi.ProductsInventoryQuantity piq')
			->leftJoin('piq.ProductsInventoryQuantityComments piqc')
			->where('pi.products_id = ?', $ProductId)
			->andWhere('pi.controller = ?', 'normal');

		EventManager::notify('InventoryExportStandardInventoryQueryBeforeExecute', $Qinventory);

		$Inventory = $Qinventory->execute();
		foreach($Inventory as $iInfo){
			if ($iInfo->track_method == 'barcode'){
				foreach($iInfo->ProductsInventoryBarcodes as $bInfo){
					$RowData = array(
						'v_products_model' => $ProductModel,
						'v_purchase_type'  => $iInfo->type,
						'v_barcode'		=> $bInfo->barcode,
						'v_barcode_status' => $bInfo->status,
						'v_comments'	   => ''
					);

					if (!empty($bInfo->ProductsInventoryBarcodesComments)){
						foreach($bInfo->ProductsInventoryBarcodesComments as $cInfo){
							$RowData['v_comments'] .= $cInfo->comments . ';';
						}
					}

					EventManager::notify('InventoryExportStandardBarcodeAddRowData', &$RowData, $bInfo);

					$ExportRows[] = $RowData;
				}
			}
			elseif ($iInfo->track_method == 'quantity') {
				foreach($iInfo->ProductsInventoryQuantity as $qInfo){
					if ($qInfo->available == 0 && $qInfo->purchased == 0){
						continue;
					}
					$RowData = array(
						'v_products_model'	 => $ProductModel,
						'v_purchase_type'	  => $iInfo->type,
						'v_quantity_available' => $qInfo->available,
						'v_quantity_broken'	=> $qInfo->broken,
						'v_quantity_out'	   => $qInfo->qty_out,
						'v_quantity_purchased' => $qInfo->purchased,
						'v_comments'		   => ''
					);

					if (!empty($qInfo->ProductsInventoryQuantityComments)){
						foreach($qInfo->ProductsInventoryQuantityComments as $cInfo){
							$RowData['v_comments'] .= $cInfo->comments . ';';
						}
					}

					EventManager::notify('InventoryExportStandardQuantityAddRowData', &$RowData, &$qInfo);

					$ExportRows[] = $RowData;
				}
			}
		}
	}

	public function ImportInventoryParseLine($LineData, &$ParsedArray) {
		if (!empty($LineData['v_barcode']) && !empty($LineData['v_barcode_status'])){
			$ParsedData = array(
				'barcode'  => $LineData['v_barcode'],
				'status'   => $LineData['v_barcode_status'],
				'comments' => $LineData['v_comments']
			);

			EventManager::notify('InventoryImportStandardParseLineBarcode', $LineData, &$ParsedData);

			$ParsedArray['purchase_type'] = $LineData['v_purchase_type'];
			$ParsedArray['barcodes'][$LineData['v_purchase_type']][] = $ParsedData;
		}
		else {
			$ParsedData = array(
				'available' => $LineData['v_quantity_available'],
				'broken'	=> $LineData['v_quantity_broken'],
				'out'	   => $LineData['v_quantity_out'],
				'purchased' => $LineData['v_quantity_purchased'],
				'comments'  => $LineData['v_comments']
			);

			EventManager::notify('InventoryImportStandardParseLineQuantity', $LineData, &$ParsedData);

			$ParsedArray['purchase_type'] = $LineData['v_purchase_type'];
			$ParsedArray['quantities'][$LineData['v_purchase_type']][] = $ParsedData;
		}
	}

	private function getInventoryId($productId, $purchaseType, $trackMethod, $controller = 'normal') {
		if (isset($this->cachedInventoryId[$productId][$purchaseType][$controller][$trackMethod])){
			$InvId = $this->cachedInventoryId[$productId][$purchaseType][$controller][$trackMethod];
			if (IMPORT_MODE == 'debug'){
				echo 'INVENTORY ID FROM CACHE: (' . $productId . ', ' . $purchaseType . ', ' . $trackMethod . ', ' . $controller . ') ::<pre>';
				print_r($InvId);
				echo '</pre>';
			}
		}
		else {
			$QInvId = Doctrine_Query::create()
				->select('inventory_id')
				->from('ProductsInventory')
				->where('products_id = ?', $productId)
				->andWhere('track_method = ?', $trackMethod)
				->andWhere('controller = ?', $controller)
				->andWhere('type = ?', $purchaseType)
				->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
			if ($QInvId && sizeof($QInvId) > 0){
				if (IMPORT_MODE == 'debug'){
					echo 'EXISTING INVENTORY: (' . $productId . ', ' . $purchaseType . ', ' . $trackMethod . ', ' . $controller . ') ::<pre>';
					print_r($QInvId);
					echo '</pre>';
				}
				$InvId = $QInvId[0]['inventory_id'];
			}
			else {
				$Inventory = new ProductsInventory();
				$Inventory->products_id = $productId;
				$Inventory->track_method = $trackMethod;
				$Inventory->controller = $controller;
				$Inventory->type = $purchaseType;
				if (IMPORT_MODE == 'debug'){
					echo 'NEW INVENTORY: (' . $productId . ', ' . $purchaseType . ', ' . $trackMethod . ', ' . $controller . ') ::<pre>';
					print_r($Inventory->toArray());
					echo '</pre>';
					$InvId = 'NEW';
				}
				else {
					$Inventory->save();

					$InvId = $Inventory->inventory_id;
				}
			}
			$this->cachedInventoryId[$productId][$purchaseType][$controller][$trackMethod] = $InvId;
		}
		return $InvId;
	}

	public function getInventoryQuantityRecord($InventoryId, $qInfo) {
		$Query = Doctrine_Query::create()
			->from('ProductsInventoryQuantity')
			->where('inventory_id = ?', $InventoryId);

		EventManager::notify('InventoryImportStandardGetQuantityRecordQueryBeforeExecute', $Query, $qInfo);

		$Result = $Query->execute();
		if ($Result && $Result->count() > 0){
			$QuantityRecord = $Result[0];
		}
		else {
			$QuantityRecord = new ProductsInventoryQuantity();
			$QuantityRecord->inventory_id = $InventoryId;

			EventManager::notify('InventoryImportStandardNewQuantityRecordBeforeSave', $QuantityRecord, $qInfo);

			if (IMPORT_MODE == 'debug'){
				echo 'NEW QUANTITY::<pre>';
				print_r($QuantityRecord->toArray());
				echo '</pre>';
			}
			else {
				$QuantityRecord->save();
			}
		}
		return $QuantityRecord;
	}

	public function ImportInventoryProcessData($ImportData) {
		if (IMPORT_MODE == 'debug'){
			echo 'LINE DATA::<pre>';
			print_r($ImportData);
			echo '</pre>';
		}
		if (isset($ImportData['barcodes'])){
			$Barcodes = Doctrine_Core::getTable('ProductsInventoryBarcodes');
			foreach($ImportData['barcodes'] as $purchaseType => $codes){
				foreach($codes as $bInfo){
					$Barcode = $Barcodes->findOneByBarcode($bInfo['barcode']);
					$newBarcode = false;
					if (!$Barcode){
						$Barcode = new ProductsInventoryBarcodes();
						$Barcode->barcode = $bInfo['barcode'];
						$newBarcode = true;
					}

					//If the barcode is new
					//has been changed to another product
					//has been changed to another purchase type
					//Is assigned to a product that doesn't have a inventory entry yet
					if (
						$newBarcode === true ||
						empty($Barcode->ProductsInventory) ||
						$ImportData['productId'] != $Barcode->ProductsInventory->products_id ||
						$purchaseType != $Barcode->ProductsInventory->type
					){
						$inventoryId = $this->getInventoryId(
							$ImportData['productId'],
							$purchaseType,
							'barcode'
						);
						$Barcode->inventory_id = (int)$inventoryId;
					}

					$Barcode->status = $bInfo['status'];

					//Process Comments Columns

					EventManager::notify('InventoryImportStandardBarcodeBeforeSave', $Barcode, $bInfo, $ImportData);

					if (IMPORT_MODE == 'debug'){
						echo 'BARCODE::<pre>';
						print_r($Barcode->toArray());
						echo '</pre>';
					}
					else {
						$Barcode->save();
					}

					$Barcode->free(true);
				}
			}
		}

		if (isset($ImportData['quantities'])){
			$Inventories = Doctrine_Core::getTable('ProductsInventory');
			foreach($ImportData['quantities'] as $purchaseType => $qtys){
				foreach($qtys as $qInfo){
					$InventoryId = $this->getInventoryId(
						$ImportData['productId'],
						$purchaseType,
						'quantity'
					);

					$Quantity = $this->getInventoryQuantityRecord($InventoryId, $qInfo);
					$Quantity->available = (int)$qInfo['available'];
					$Quantity->broken = (int)$qInfo['broken'];
					$Quantity->qty_out = (int)$qInfo['qty_out'];
					$Quantity->purchased = (int)$qInfo['purchased'];

					//Process Comments Columns

					EventManager::notify('InventoryImportStandardQuantityBeforeSave', $Quantity, $qInfo, $ImportData);

					if (IMPORT_MODE == 'debug'){
						echo 'QUANTITY::<pre>';
						print_r($Quantity->toArray());
						echo '</pre>';
					}
					else {
						$Quantity->save();
					}
				}
			}
		}
	}

	public function TemplateWidgetShow($WidgetSettings){
		global $appExtension;

		$purchaseBoxes = array();
		$purchaseTypes = array();
		$productId = $this->getProductId();
		foreach($this->getPurchaseTypes() as $PurchaseType){
			$typeName = $PurchaseType->getCode();
			$purchaseTypes[$typeName] = $PurchaseType;
			if ($purchaseTypes[$typeName]){
				$settings = $purchaseTypes[$typeName]->getPurchaseHtml('product_info');
				if (is_null($settings) === false){
					EventManager::notify('ProductInfoPurchaseBoxOnLoad', &$settings, $typeName, $purchaseTypes);
					$purchaseBoxes[] = $settings;
				}
			}
		}

		$extDiscounts = $appExtension->getExtension('quantityDiscount');
		$extAttributes = $appExtension->getExtension('attributes');

		$purchaseTable = htmlBase::newElement('table')
			->addClass('ui-widget')
			->css('width', '100%')
			->setCellPadding(5)
			->setCellSpacing(0);

		$columns = array();
		foreach($purchaseBoxes as $boxInfo){
			if ($extAttributes !== false){
				$boxInfo['content'] .= $extAttributes->pagePlugin->drawAttributes(array(
					'purchaseTypeClass' => $boxInfo['purchase_type']
				));
			}

			if ($extDiscounts !== false && $boxInfo['purchase_type']->hasInventory()){
				$boxInfo['content'] .= $extDiscounts->showQuantityTable(array(
					'productTypeClass' => $this,
					'purchase_type' => $boxInfo['purchase_type'],
					'product_id' => $productId
				));
			}

			$boxInfo['content'] .= htmlBase::newElement('input')->attr('type', 'hidden')->setName('products_id')->val($productId)->draw();
			$boxInfo['content'] .= htmlBase::newElement('input')->attr('type', 'hidden')->setName('purchase_type')->val($boxInfo['purchase_type']->getCode())->draw();

			$boxObj = htmlBase::newElement('infobox')
				->setForm(array(
				'name' => 'cart_quantity',
				'action' => $boxInfo['form_action']
			))
				->css('width', 'auto')->removeCss('margin-left')->removeCss('margin-right')
				->setHeader($boxInfo['header'])
				->setButtonBarLocation('bottom')
				->addContentRow($boxInfo['content']);

			if ($boxInfo['allowQty'] === true){
				$qtyInput = htmlBase::newElement('input')
					->css('margin-right', '1em')
					->setSize(3)
					->setName('quantity')
					->setLabel('Quantity:')
					->setValue(1)
					->setLabelPosition('before');

				$boxObj->addButton($qtyInput);
			}

			$boxObj->addButton($boxInfo['button']);

			$columns[] = array(
				'align' => 'center',
				'valign' => 'top',
				'text' => $boxObj->draw()
			);

			if (sizeof($columns) > 1){
				$purchaseTable->addBodyRow(array(
					'columns' => $columns
				));
				$columns = array();
			}
		}

		if (sizeof($columns) > 0){
			$columns[0]['colspan'] = 2;
			$purchaseTable->addBodyRow(array(
				'columns' => $columns
			));
		}


		return $purchaseTable->draw();
	}

	public function hasEnoughInventory(OrderProduct $OrderProduct, $Qty = null){
		$return = true;

		$PurchaseType = $this->getPurchaseType();
		if (method_exists($PurchaseType, 'hasEnoughInventory')){
			$return = $PurchaseType->hasEnoughInventory($OrderProduct, $Qty);
		}
		return $return;
	}

	public function onSaveProgress(OrderProduct $OrderProduct, &$SaleProduct){
		$PurchaseType = $this->getPurchaseType();
		if (method_exists($PurchaseType, 'onSaveProgress')){
			$PurchaseType->onSaveProgress($OrderProduct, $SaleProduct);
		}
	}

	public function onSaveSale(OrderProduct $OrderProduct, &$SaleProduct, $AssignInventory = false){
		$PurchaseType = $this->getPurchaseType();
		if (method_exists($PurchaseType, 'onSaveSale')){
			$PurchaseType->onSaveSale($OrderProduct, $SaleProduct, $AssignInventory);
		}
	}

	public function prepareJsonSave(OrderProduct &$OrderProduct){
		$toEncode = array();
		$PurchaseType = $this->getPurchaseType();
		if (method_exists($PurchaseType, 'prepareJsonSave')){
			$toEncode = $PurchaseType->prepareJsonSave($OrderProduct);
		}
		return $toEncode;
	}

	public function jsonDecodeProduct(OrderProduct &$OrderProduct, $Product){
		$this->cartPurchaseType = $OrderProduct->getInfo('purchase_type');
		$this->loadPurchaseType();

		$PurchaseType = $this->getPurchaseType();
		if (method_exists($PurchaseType, 'jsonDecode')){
			$PurchaseType->jsonDecode($OrderProduct);
		}
	}

	public function jsonDecode(OrderProduct &$OrderProduct, $ProductTypeJson){
		$this->cartPurchaseType = $OrderProduct->getInfo('purchase_type');
		$this->loadPurchaseType();

		$PurchaseType = $this->getPurchaseType();
		if (method_exists($PurchaseType, 'jsonDecode')){
			$PurchaseType->jsonDecode($OrderProduct, $ProductTypeJson);
		}
	}
}
