<?php
/*
	Rental Store Version 2

	I.T. Web Experts
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

	class ShoppingCartProduct implements Serializable {

		/**
		 * @var Product
		 */
		private $ProductCls;

		/**
		 * @var array
		 */
		private $pInfo = array(
			'hash_id' => null,
			'product_id' => 0,
			'id_string' => '',
			'tax_class_id' => 0,
			'quantity' => 0,
			'price' => 0,
			'final_price' => 0,
			'weight' => 0
		);
		
		public function __construct($productData){
			$this->pInfo = $productData;
		}

		/**
		 * @return Product
		 */
		public function &getProductClass(){
			return $this->ProductCls;
		}

		public function loadProductClass($Product = false){
			if ($Product !== false){
				$this->ProductCls = $Product;
			}else{
				$this->ProductCls = new Product($this->getData('product_id'));
			}

			$ProductType = $this->getProductClass()->getProductTypeClass();
			if (method_exists($ProductType, 'onCartProductLoad')){
				$ProductType->onCartProductLoad($this);
			}

			EventManager::notify('ShoppingCart\LoadProductClass', $this);
		}

		public function addToCartBeforeAction(){
			$ProductType = $this->getProductClass()->getProductTypeClass();
			if (method_exists($ProductType, 'addToCartBeforeAction')){
				$ProductType->addToCartBeforeAction($this);
			}

			EventManager::notify('ShoppingCart\AddToCartBeforeAction', $this);
		}

		public function addToCartAfterAction(){
			$ProductType = $this->getProductClass()->getProductTypeClass();
			if (method_exists($ProductType, 'addToCartAfterAction')){
				$ProductType->addToCartAfterAction($this);
			}
			EventManager::notify('ShoppingCart\AddToCartAfterAction', $this);
		}

		public function updateFromPost(){
			$ProductType = $this->getProductClass()->getProductTypeClass();
			if (method_exists($ProductType, 'onUpdateCartFromPost')){
				$ProductType->onUpdateCartFromPost($this);
			}

			EventManager::notify('ShoppingCart\OnUpdateCart', $this);
		}

		public function processRemoveFromCart(){
			$ProductType = $this->getProductClass()->getProductTypeClass();
			if (method_exists($ProductType, 'processRemoveFromCart')){
				$ProductType->processRemoveFromCart();
			}

			EventManager::notify('ShoppingCart\ProcessRemoveFromCart', $this);
		}

		public function setData($k, $v){
			$this->pInfo[$k] = $v;
		}

		public function updateData($k, $v){
			$this->pInfo[$k] = $v;
		}

		public function getData($k){
			return $this->pInfo[$k];
		}
		
		public function serialize(){
			return serialize($this->pInfo);
		}
		
		public function unserialize($data){
			$this->pInfo = unserialize($data);
		}
		
		public function init(){
			$this->loadProductClass();
			
			/*if (isset($this->pInfo['aID_string'])){
				$this->purchaseTypeClass->inventoryCls->invMethod->trackMethod->aID_string = $this->pInfo['aID_string'];
			}*/
		}

		public function getName(){
			return $this->getProductClass()->getName();
		}

		public function getShortDescription(){
			return $this->getProductClass()->getShortDescription();
		}

		public function getImage(){
			return $this->getProductClass()->getImage();
		}
		
		public function getModel(){
			return $this->getProductClass()->getModel();
		}
		
		public function getWeight(){
			return $this->getProductClass()->getWeight();
		}
		
		public function getQuantity(){
			return $this->pInfo['quantity'];
		}
		
		public function getIdString(){
			return $this->pInfo['id_string'];
		}
		
		public function getTaxClassId(){
			return $this->pInfo['tax_class_id'];
		}

		public function getId(){
			return $this->pInfo['hash_id'];
		}

		public function isTaxable(){
			return true;
		}

		public function hasWeight(){
			return ($this->getWeight() > 0);
		}
		
		private function getTaxAddressInfo(){
			global $order, $userAccount;
			$zoneId = null;
			$countryId = null;

			if (sysConfig::get('BASE_TAX_RATE') == 'Billing') {
				$taxAddress = $userAccount->plugins['addressBook']->getAddress('billing');
			} else {
				$taxAddress = $userAccount->plugins['addressBook']->getAddress('delivery');
			}
			$zoneId = $taxAddress['entry_zone_id'];
			$countryId = (isset($taxAddress['entry_country_id']) ? $taxAddress['entry_country_id'] : (isset($taxAddress['entry_country']) ? $taxAddress['entry_country'] : 0));
			EventManager::notify('ProductBeforeTaxAddress', &$zoneId, &$countryId, $this, $order, $userAccount);
			return array(
				'zoneId'    => $zoneId,
				'countryId' => $countryId
			);
		}

		/**
		 * @param null $countryId
		 * @param null $zoneId
		 * @return float
		 */
		public function getTaxRate($countryId = null, $zoneId = null){
			if (is_null($countryId) && is_null($zoneId)){
				$taxAddress = $this->getTaxAddressInfo();
				$countryId = $taxAddress['countryId'];
				$zoneId = $taxAddress['zoneId'];
			}
			return tep_get_tax_rate($this->getTaxClassId(), $countryId, $zoneId);
		}

		/**
		 * @param null $countryId
		 * @param null $zoneId
		 * @return string
		 */
		public function getTaxDescription($countryId = null, $zoneId = null){
			if (is_null($countryId) && is_null($zoneId)){
				$taxAddress = $this->getTaxAddressInfo();
				$countryId = $taxAddress['countryId'];
				$zoneId = $taxAddress['zoneId'];
			}
			return tep_get_tax_description($this->getTaxClassId(), $countryId, $zoneId);
		}

		public function getPrice($wTax = false){
			if ($wTax === true){
				return tep_add_tax($this->pInfo['price'], $this->getTaxRate());
			}
			return $this->pInfo['price'];
		}
		
		public function setPrice($val){
			$this->pInfo['price'] = $val;
		}
		
		public function addToPrice($val){
			$this->pInfo['price'] += $val;
		}
		
		public function subtractFromPrice($val){
			$this->pInfo['price'] -= $val;
		}
		
		public function getFinalPrice($wTax = false, $wQty = false){
			$price = $this->pInfo['price'];
			//echo $price . ' :: ' . __LINE__ . '<br>';
			if ($wQty === true){
				$price *= $this->getQuantity();
				//echo $price . ' :: ' . __LINE__ . '<br>';
			}
			if ($wTax === true){
				$price += tep_calculate_tax($price, $this->getTaxRate());
				//echo $price . ' :: ' . __LINE__ . '<br>';
			}
			//echo $price . ' :: ' . __LINE__ . '<br>';
			return $price;
		}

		public function displayFinalPrice($wTax = false, $wQty = false){
			global $currencies;
			return $currencies->format($this->getFinalPrice($wTax, $wQty));
		}
		
		public function setFinalPrice($val){
			$this->pInfo['final_price'] = $val;
		}
		
		public function addToFinalPrice($val){
			$this->pInfo['final_price'] += $val;
		}
		
		public function subtractFromFinalPrice($val){
			$this->pInfo['final_price'] -= $val;
		}
		
		public function getNameHtml($settings = array()){
			$options = array_merge(array(
				'showProductName' => true
			), $settings);

			if (Session::get('layoutType') == 'smartphone'){
				$link = itw_app_link('products_id=' . $this->pInfo['id_string'] . '&cart_id=' . $this->getId(), 'mobile', 'productInfo');
			}else{
				$link = itw_app_link('products_id=' . $this->pInfo['id_string'] . '&cart_id=' . $this->getId(), 'product', 'info');
			}
			$nameHref = htmlBase::newElement('a')
			->setHref($link)
			->css(array(
				'font-weight' => 'bold'
			))
			->html($this->getName());

			$name = $nameHref->draw() . '<br />';

			$ProductType = $this->getProductClass()->getProductTypeClass();
			if (method_exists($ProductType, 'showShoppingCartProductInfo')){
				$name .= $ProductType->showShoppingCartProductInfo($this, $options);
			}

			$Result = EventManager::notifyWithReturn('ShoppingCartProduct\ProductNameAppend', &$this);
			foreach($Result as $html){
				$name .= $html;
			}
			
			return $name;
		}
		
		public function getImageHtml(){
			$image = $this->getImage();
			
			EventManager::notify('ShoppingCartProduct\ProductImageBeforeShow', &$image, &$this);
		
			$imageHtml = htmlBase::newElement('image')
			->setSource('images/' . $image)
			->setWidth(sysConfig::get('SMALL_IMAGE_WIDTH'))
			->setHeight(sysConfig::get('SMALL_IMAGE_HEIGHT'))
			->thumbnailImage(true);

			if (Session::get('layoutType') == 'smartphone'){
				$link = itw_app_link('products_id=' . $this->pInfo['id_string'] . '&cart_id=' . $this->getId(), 'mobile', 'productInfo');
			}else{
				$link = itw_app_link('products_id=' . $this->pInfo['id_string'] . '&cart_id=' . $this->getId(), 'product', 'info');
			}
			$imageHref = htmlBase::newElement('a')
			->setHref($link)
			->css(array(
				'font-weight' => 'bold'
			))
			->append($imageHtml);
			return $imageHref->draw();
		}

		public function getCartQuantityHtml(){
			$ProductType = $this->getProductClass()->getProductTypeClass();
			if (method_exists($ProductType, 'getCartQuantityHtml')){
				$qty = $ProductType->getCartQuantityHtml($this);
			}else{
				$qty = htmlBase::newElement('input')
					->addClass('quantity')
					->setName('cart_quantity[' . $this->getId() . ']')
					->val($this->getQuantity())
					->attr('size', 4)
					->attr('data-id', $this->getId())
					->draw();
			}
			return $qty;
		}
		
		public function hasInfo($key){
			return (isset($this->pInfo[$key]));
		}
		
		public function getInfo($key = null){
			if (is_null($key)){
				return $this->pInfo;
			}else{
				if (isset($this->pInfo[$key])){
					return $this->pInfo[$key];
				}else{
					return false;
				}
			}
		}
		
		public function updateInfo($newInfo){
			$newProductInfo = $this->pInfo;
			foreach($newInfo as $k => $v){
				$newProductInfo[$k] = $v;
			}
			$this->pInfo = $newProductInfo;
			//$this->purchaseTypeClass->processUpdateCart(&$this->pInfo);
		}
	}
?>