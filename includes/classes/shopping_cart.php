<?php
/*
	Rental Store Version 2

	I.T. Web Experts
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/
require(dirname(__FILE__) . '/shoppingCart/Contents.php');
require(dirname(__FILE__) . '/shoppingCart/Product.php');

class ShoppingCart implements Serializable
{

	/**
	 * @var ShoppingCartContents
	 */
	private $contents;

	/**
	 * @var int
	 */
	private $total = 0;

	/**
	 * @var int
	 */
	private $weight = 0;

	/**
	 * @var string
	 */
	private $content_type = '';

	/**
	 * @var int
	 */
	private $cartID = 0;

	/**
	 * @var int
	 */
	private $total_virtual = 0;

	/**
	 * @var int
	 */
	private $weight_virtual = 0;

	public function __construct() {
		$this->emptyCart();
	}

	public function hasId(){
		return ($this->getId() > 0);
	}

	public function setId(){
		$this->cartID = $this->generateCartId();
	}

	public function getId(){
		return $this->cartID;
	}

	public function hasContents(){
		return ($this->contents->count() > 0);
	}

	public function getContents(){
		return $this->contents;
	}

	public function getProducts(){
		return $this->getContents();
	}

	public function storeCart() {
		$userAccount =& Session::getReference('userAccount');
		if ($userAccount->isLoggedIn() === true){
			$CustomersBasket = Doctrine_Core::getTable('CustomersBasket');

			$CustomerCart = $CustomersBasket->findOneByCustomersId($userAccount->getCustomerId());
			if ($CustomerCart && $this->hasContents() === false){
				$CustomerCart->delete();
			}
			else {
				if (!$CustomerCart){
					$CustomerCart = $CustomersBasket->create();
					$CustomerCart->customers_id = $userAccount->getCustomerId();
				}
				$CustomerCart->cart_data = $this->serialize();
				$CustomerCart->save();
			}
		}
	}

	public function loadCart() {
		$userAccount =& Session::getReference('userAccount');
		if ($userAccount->isLoggedIn() === true){
			$CartData = Doctrine_Query::create()
				->select('cart_data')
				->from('CustomersBasket')
				->where('customers_id = ?', $userAccount->getCustomerId())
				->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

			if ($CartData){
				$this->unserialize($CartData[0]['cart_data']);
			}
		}
	}

	public function initContents() {
		foreach($this->contents as $cartProduct){
			$cartProduct->init();
		}
	}

	public function serialize() {
		return serialize($this->contents);
	}

	public function unserialize($data) {
		$this->contents = unserialize($data);
	}

	private function _notify($pID) {
		Session::set('new_products_id_in_cart', $pID);
	}

	public function generateCartId($length = 5) {
		return tep_create_random_value($length, 'digits');
	}

	public function emptyCart($reset_database = false) {
		$this->contents = new ShoppingCartContents();
		$this->total = 0;
		$this->weight = 0;
		$this->content_type = false;

		if ($reset_database == true){
			$this->storeCart();
		}
		unset($this->cartID);
		if (Session::exists('cartID') === true){
			Session::remove('cartID');
		}
	}

	public function getProductIdList() {
		$product_id_list = '';

		foreach($this->contents as $cartProduct){
			$product_id_list .= ', ' . $cartProduct->getIdString();
		}

		return substr($product_id_list, 2);
	}

	/**
	 * @param array $CartProductData
	 * @param Product $Product
	 * @return bool
	 */
	public function allowAdd($CartProductData, Product $Product) {
		$return = $Product->isActive();
		if ($return === true){
			$return = $Product->allowAddToCart($CartProductData);
			if ($return === true){
				$return = EventManager::notifyWithReturn('ShoppingCart\AddToCartAllow', $CartProductData, $Product);
				foreach($return as $Result){
					if ($Result === false){
						$return = false;
						break;
					}
				}
			}
		}
		return $return;
	}

	public function hashArray($v){
		return md5(serialize($v));
	}

	/**
	 * @param int $productId
	 * @return bool
	 */
	public function add($productId) {
		$Product = new Product($productId);

		$CartProductData = array(
			'hash_id' => null,
			'product_id' => $Product->getId(),
			'id_string' => $Product->getId(),
			'weight' => $Product->getWeight()
		);

		$Product->addToCartPrepare(&$CartProductData);

		EventManager::notify('ShoppingCart\AddToCartPrepare', &$CartProductData);

		$success = false;
		if ($this->allowAdd($CartProductData, $Product)){
			$hashId = $this->hashArray($CartProductData);
			$CartProductData['hash_id'] = $hashId;

			$CartProduct = $this->contents->offsetGet($hashId);
			if ($CartProduct){
				$new = false;
			}
			else {
				$CartProduct = new ShoppingCartProduct($CartProductData);
				$new = true;
			}
			$CartProduct->loadProductClass($Product);

			if ($new === false){
				$CartProduct->updateInfo($CartProductData);
			}
			else {
				$CartProduct->addToCartBeforeAction();
				$this->contents->offsetSet($hashId, $CartProduct);
				$CartProduct->addToCartAfterAction();
			}

			$this->cartID = $this->generateCartId();
			$success = true;

			$this->storeCart();
		}

		return $success;
	}

	public function update($id){
		$CartProduct =& $this->contents->offsetGet($id);
		$CartProduct->updateFromPost();
	}

	public function remove($id) {
		$this->contents->offsetUnset($id);
		$this->cartID = $this->generateCartId();

		$this->storeCart();
	}

	public function calculate() {
		$this->total = 0;
		$this->total_virtual = 0;
		$this->weight = 0;
		$this->weight_virtual = 0;

		if ($this->contents->count() <= 0){
			return 0;
		}

		foreach($this->contents as $cartProduct){
			$addtoTotal = $cartProduct->getFinalPrice() * $cartProduct->getQuantity();
			if ($cartProduct->isTaxable() === true){
				$addtoTotal += tep_calculate_tax($addtoTotal, $cartProduct->getProductClass()->getProductTypeClass()
						->getTaxRate());
			}

			$addToWeight = 0;
			if ($cartProduct->hasWeight() === true){
				$addToWeight += $cartProduct->getProductClass()->getWeight() * $cartProduct->getQuantity();
			}

			$addtoTotalV = $addtoTotal;
			$addToWeightV = $addToWeight;
			if (preg_match('/^GIFT/', $cartProduct->getProductClass()->getModel())){
				$addtoTotalV = 0;
				$addToWeightV = 0;
			}

			if ($cartProduct->hasWeight() === true){
				$this->weight_virtual += $addToWeightV; // ICW CREDIT CLASS;
				$this->weight += $addToWeight;
			}
			$this->total_virtual += $addtoTotalV; // ICW CREDIT CLASS;
			$this->total += $addtoTotal;
		}
	}

	public function countContents() {
		$totalItems = 0;
		foreach($this->contents as $cartProduct){
			$totalItems += $cartProduct->getQuantity();
		}

		EventManager::notify('ShoppingCart\CountContents', &$totalItems);
		return $totalItems;
	}

	public function getQuantity($pID_string, $purchaseType) {
		$cartProduct = $this->contents->find($pID_string, $purchaseType);
		if ($cartProduct){
			return $cartProduct->getQuantity();
		}
		else {
			return 0;
		}
	}

	public function inCart($pID_string, $purchaseType = 'new') {
		$cartProduct = $this->contents->find($pID_string, $purchaseType);
		if ($cartProduct){
			return true;
		}
		return false;
	}

	public function getProduct($id){
		$CartProduct = $this->contents->offsetGet($id);
		if ($CartProduct){
			return $CartProduct;
		}
		return null;
	}

	/*public function getProduct($pID_string, $purchaseType = 'new') {
		$cartProduct = $this->contents->find($pID_string, $purchaseType);
		if ($cartProduct){
			return $cartProduct;
		}
		return null;
	}*/

	public function restoreContents() {
		$this->loadCart();
	}

	public function showTotal() {
		$this->calculate();
		return $this->total;
	}

	public function showWeight() {
		$this->calculate();
		return $this->weight;
	}

	public function getContentType() {
		$this->content_type = false;
		if ($this->countContents() > 0){
			if ($this->showWeight() == 0){
				foreach($this->contents as $cartProduct){
					// @TODO: Get into pay per rental extension
					if ($cartProduct->getWeight() == 0 || $cartProduct->getPurchaseType() == 'reservation'){
						switch($this->content_type){
							case 'physical':
								$this->content_type = 'mixed';
								return $this->content_type;
								break;
							default:
								$this->content_type = 'virtual';
								break;
						}
					}
					else {
						switch($this->content_type){
							case 'virtual':
								$this->content_type = 'mixed';
								return $this->content_type;
								break;
							default:
								$this->content_type = 'physical';
								break;
						}
					}
				}
			}
			else {
				switch($this->content_type){
					case 'virtual':
						$this->content_type = 'mixed';
						return $this->content_type;
						break;
					default:
						$this->content_type = 'physical';
						break;
				}
			}
		}
		else {
			$this->content_type = 'physical';
		}
		return $this->content_type;
	}
}

?>