<?php
/*
	Rental Store Version 2

	I.T. Web Experts
	http://www.itwebexperts.com

	Copyright (c) 2011 I.T. Web Experts

	This script and it's source is not redistributable
*/

class RentalQueueProduct implements Serializable
{

	/**
	 * @var Product
	 */
	private $ProductCls;

	/**
	 * @var array
	 */
	private $pInfo = array(
		'hash_id'    => null,
		'product_id' => 0,
		'id_string'  => '',
		'quantity'   => 0,
		'priority'   => 999999999
	);

	public function __construct($productData) {
		$this->pInfo = $productData;
	}

	/**
	 * @return Product
	 */
	public function &getProductClass() {
		return $this->ProductCls;
	}

	public function loadProductClass(Product $Product = null) {
		if (is_null($Product) === false){
			$this->ProductCls = $Product;
		}
		else {
			$this->ProductCls = new Product($this->getData('product_id'));
		}

		$ProductType = $this->getProductClass()->getProductTypeClass();
		if (method_exists($ProductType, 'onQueueProductLoad')){
			$ProductType->onQueueProductLoad($this);
		}

		EventManager::notify('RentalQueue\LoadProductClass', $this);
	}

	public function addToQueueBeforeAction() {
		$ProductType = $this->getProductClass()->getProductTypeClass();
		if (method_exists($ProductType, 'addToQueueBeforeAction')){
			$ProductType->addToQueueBeforeAction();
		}

		EventManager::notify('RentalQueue\AddToQueueBeforeAction', $this);
	}

	public function addToQueueAfterAction() {
		$ProductType = $this->getProductClass()->getProductTypeClass();
		if (method_exists($ProductType, 'addToQueueAfterAction')){
			$ProductType->addToQueueAfterAction();
		}

		EventManager::notify('RentalQueue\AddToQueueAfterAction', $this);
	}

	public function updateFromPost() {
		$ProductType = $this->getProductClass()->getProductTypeClass();
		if (method_exists($ProductType, 'onUpdateQueueFromPost')){
			$ProductType->onUpdateQueueFromPost($this);
		}

		EventManager::notify('RentalQueue\OnUpdateQueue', $this);
	}

	public function processRemoveFromQueue() {
		$ProductType = $this->getProductClass()->getProductTypeClass();
		if (method_exists($ProductType, 'processRemoveFromQueue')){
			$ProductType->processRemoveFromQueue();
		}

		EventManager::notify('RentalQueue\ProcessRemoveFromQueue', $this);
	}

	public function setData($k, $v) {
		$this->pInfo[$k] = $v;
	}

	public function updateData($k, $v) {
		$this->pInfo[$k] = $v;
	}

	public function getData($k) {
		return (isset($this->pInfo[$k]) ? $this->pInfo[$k] : null);
	}

	public function serialize() {
		return serialize($this->pInfo);
	}

	public function unserialize($data) {
		$this->pInfo = unserialize($data);
	}

	public function init() {
		$this->loadProductClass();
		/*if (isset($this->pInfo['aID_string'])){
			   $this->purchaseTypeClass->inventoryCls->invMethod->trackMethod->aID_string = $this->pInfo['aID_string'];
		   }*/
	}

	public function getName($langId = 0) {
		return $this->getProductClass()->getName($langId);
	}

	public function getDescription($langId = 0){
		return $this->getProductClass()->getDescription($langId);
	}

	public function getImage() {
		return $this->getProductClass()->getImage();
	}

	public function getModel() {
		return $this->getProductClass()->getModel();
	}

	public function getQuantity() {
		return $this->pInfo['quantity'];
	}

	public function getIdString() {
		return $this->pInfo['id_string'];
	}

	public function getId() {
		return $this->pInfo['hash_id'];
	}

	public function getPriority() {
		return (int) $this->pInfo['priority'];
	}

	public function getNameHtml() {
		$nameHref = htmlBase::newElement('a')
			->setHref(itw_app_link('products_id=' . $this->pInfo['id_string'] . '&queue_id=' . $this->getId(), 'product', 'info'))
			->css(array(
			'font-weight' => 'bold'
		))
			->html($this->getName());

		$name = $nameHref->draw() . '<br />';

		$ProductType = $this->getProductClass()->getProductTypeClass();
		if (method_exists($ProductType, 'showRentalQueueProductInfo')){
			$name .= $ProductType->showRentalQueueProductInfo($this);
		}

		$Result = EventManager::notifyWithReturn('RentalQueueProduct\ProductNameAppend', &$this);
		foreach($Result as $html){
			$name .= $html;
		}

		return $name;
	}

	public function getImageHtml() {
		$image = $this->getImage();

		EventManager::notify('RentalQueueProduct\ProductImageBeforeShow', &$image, &$this);

		$imageHtml = htmlBase::newElement('image')
			->setSource('images/' . $image)
			->setWidth(sysConfig::get('SMALL_IMAGE_WIDTH'))
			->setHeight(sysConfig::get('SMALL_IMAGE_HEIGHT'))
			->thumbnailImage(true);

		$imageHref = htmlBase::newElement('a')
			->setHref(itw_app_link('products_id=' . $this->pInfo['id_string'], 'product', 'info'))
			->css(array(
			'font-weight' => 'bold'
		))
			->append($imageHtml);
		return $imageHref->draw();
	}

	public function getQueueQuantityHtml() {
		$ProductType = $this->getProductClass()->getProductTypeClass();
		if (method_exists($ProductType, 'getQueueQuantityHtml')){
			$qty = $ProductType->getQueueQuantityHtml($this);
		}
		else {
			$qty = htmlBase::newElement('input')
				->addClass('quantity')
				->setName('queue_quantity[' . $this->getId() . ']')
				->val($this->getQuantity())
				->attr('size', 4)
				->attr('data-id', $this->getId())
				->draw();
		}
		return $qty;
	}

	public function hasInfo($key) {
		return (isset($this->pInfo[$key]));
	}

	public function getInfo($key = null) {
		if (is_null($key)){
			return $this->pInfo;
		}
		else {
			if (isset($this->pInfo[$key])){
				return $this->pInfo[$key];
			}
			else {
				return false;
			}
		}
	}

	public function updateInfo($newInfo) {
		$newProductInfo = $this->pInfo;
		foreach($newInfo as $k => $v){
			$newProductInfo[$k] = $v;
		}
		$this->pInfo = $newProductInfo;
	}
}

?>