<?php

class ProductTypePackage extends ModuleBase
{

	private $_moduleCode = 'package';

	private $purchaseTypes = array();

	private $cartPurchaseType = '';

	private $checked = array();

	private $info = array(
		'id' => 0,
		'name' => array(),
		'description' => array()
	);

	private $purchaseTypeModules = array();

	public function __construct() {
		/*
		 * Default title and description for modules that are not yet installed
		 */
		$this->setTitle('Package Product Type');
		$this->setDescription('Package Product Type');

		$this->init($this->_moduleCode);
	}

	public function init($forceEnable = false) {
		$this->import(new Installable);

		$this->setModuleType('productType');

		parent::init($this->_moduleCode, $forceEnable);
	}

	public function setProductId($val){
		$this->info['id'] = $val;
	}

	public function setProductName($val, $langId){
		$this->info['name'][$langId] = $val;
	}

	public function setProductDescription($val, $langId){
		$this->info['description'][$langId] = $val;
	}

	public function getProductId(){
		return $this->info['id'];
	}

	public function getProductName($langId = false){
		if ($langId === false){
			$langId = Session::get('languages_id');
		}
		return $this->info['name'][$langId];
	}

	public function getProductDescription($langId = false){
		if ($langId === false){
			$langId = Session::get('languages_id');
		}
		return $this->info['description'][$langId];
	}

	public function getProductPrice($PurchaseType){
		if ($this->purchaseTypeEnabled($PurchaseType) === false) return 0;

		return $this->getPurchaseType($PurchaseType)->getPrice();
	}

	public function getTaxClassId($PurchaseType = false){
		if ($PurchaseType === false){
			$PurchaseType = $this->cartPurchaseType;
		}
		return $this->getPurchaseType($PurchaseType)->getTaxClassId();
	}

	public function getTaxRate($PurchaseType = false){
		if ($PurchaseType === false){
			$PurchaseType = $this->cartPurchaseType;
		}
		return $this->getPurchaseType($PurchaseType)->getTaxRate();
	}

	public function purchaseTypeEnabled($PurchaseType){
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

	public function loadPurchaseType($PurchaseType){
		if ($this->purchaseTypeEnabled($PurchaseType) === false) return;

		if (!isset($this->purchaseTypes[$PurchaseType])){
			$this->purchaseTypes[$PurchaseType] = PurchaseTypeModules::getModule($PurchaseType);
			$this->purchaseTypes[$PurchaseType]->loadData($this->getProductId());
		}
	}

	public function &getPurchaseType($PurchaseType){
		if ($this->purchaseTypeEnabled($PurchaseType) === false) return null;

		if (empty($this->purchaseTypes) || array_key_exists($PurchaseType, $this->purchaseTypes) === false){
			$this->loadPurchaseType($PurchaseType);
		}
		return $this->purchaseTypes[$PurchaseType];
	}

	public function getPurchaseTypes(){
		if (empty($this->purchaseTypes)){
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

	public function addToCartPrepare(&$CartProductData){
		$PurchaseType = $this->getPurchaseType($_POST['purchase_type']);

		$qty = 1;
		if ($PurchaseType->getConfigData('ALLOW_MULTIPLE_IN_CART') == 'True' && isset($_POST['quantity'])){
			if (is_numeric($_POST['quantity'])){
				$qty = $_POST['quantity'];
			}elseif (is_array($_POST['quantity']) && isset($_POST['quantity'][$PurchaseType->getCode()])){
				$qty = $_POST['quantity'][$PurchaseType->getCode()];
			}
		}

		$CartProductData['price'] = $PurchaseType->getPrice();
		$CartProductData['final_price'] = $PurchaseType->getPrice();
		$CartProductData['purchase_type'] = $PurchaseType->getCode();
		$CartProductData['quantity'] = $qty;
		$CartProductData['tax_class_id'] = $PurchaseType->getTaxClassId();
	}

	public function onCartProductLoad($CartProduct){
		$this->loadPurchaseType($CartProduct->getData('purchase_type'));
		$this->cartPurchaseType = $CartProduct->getData('purchase_type');
	}

	public function showShoppingCartProductInfo($CartProduct){
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
}
