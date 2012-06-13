<?php
/**
 * Product manager class for the order creator
 *
 * @package OrderCreator
 * @author Stephen Walker <stephen@itwebexperts.com>
 * @copyright Copyright (c) 2011, I.T. Web Experts
 */

class OrderCreatorProductManager extends OrderProductManager
{

	/**
	 * @var OrderCreatorProduct[]
	 */
	public $Contents = array();

	/**
	 * @param array|null $orderedProducts
	 */
	public function __construct(array $orderedProducts = null) {
		if (is_null($orderedProducts) === false){
			foreach($orderedProducts as $i => $pInfo){
				$orderedProduct = new OrderCreatorProduct($pInfo);
				$this->Contents[$orderedProduct->getId()] = $orderedProduct;
			}
			$this->cleanUp();
		}
	}

	/**
	 * @param int $id
	 * @return bool|OrderProduct
	 */
	public function get($id) {
		$OrderedProduct = parent::get((int) $id);
		return $OrderedProduct;
	}

	/**
	 *
	 */
	public function init(){
		foreach($this->getContents() as $OrderedProduct){
			$OrderedProduct->init();
		}
	}

	/**
	 *
	 */
	public function updateFromPost() {
		foreach($_POST['product'] as $id => $pInfo){
			if (!isset($pInfo['qty'])){
				continue;
			}

			$Product = $this->get($id);
			if ($Product === false || is_null($Product)){
				echo 'Error: A Product Was Posted That Was Not In The Product Manager. ( ID:' . $id . ' )';
				echo '<pre>';print_r(array_keys($this->Contents));
				itwExit();
			}

			$Product->setQuantity($pInfo['qty']);
			$Product->setPrice($pInfo['price']);
			$Product->setTaxRate($pInfo['tax_rate']);

			if (isset($pInfo['barcode_id'])){
				$barcodes = array();
				foreach($pInfo['barcode_id'] as $bID){
					$barcodes[] = array(
						'barcode_id' => $bID
					);
				}
				$Product->setBarcodes($barcodes);
			}

			if (isset($pInfo['attributes'])){
				$Product->updateInfo(array(
					'attributes' => $pInfo['attributes']
				));
			}

			$ProductType = $Product->getProductTypeClass();
			if (method_exists($ProductType, 'OrderCreatorProductManagerUpdateFromPost')){
				$ProductType->OrderCreatorProductManagerUpdateFromPost($Product);
			}
			/*
   product[85544][qty]:1
   product[85544][purchase_type]:new
   product[85544][attributes][1][value]:2
   product[85544][attributes][1][prefix]:
   product[85544][attributes][1][price]:0
   product[85544][tax_rate]:0
   product[85544][price]:17.99
   */
		}
	}

	/**
	 * @param Doctrine_Collection $CollectionObj
	 */
	public function addAllToCollection(Doctrine_Collection $CollectionObj) {
		$CollectionObj->clear();
		foreach($this->Contents as $id => $Product){
			$OrderedProduct = new OrdersProducts();

			$OrderedProduct->products_id = $Product->getProductsId();
			$OrderedProduct->products_quantity = $Product->getQuantity();
			$OrderedProduct->products_name = $Product->getName();
			$OrderedProduct->products_model = $Product->getModel();
			$OrderedProduct->products_price = $Product->getFinalPrice(false, false);
			$OrderedProduct->final_price = $Product->getFinalPrice(false, false);
			$OrderedProduct->products_tax = $Product->getTaxRate();

			$Product->onAddToCollection($OrderedProduct);

			$CollectionObj->add($OrderedProduct);
		}
	}

	/**
	 * @param OrderProduct $OrderProduct
	 * @return bool|void
	 */
	public function add(OrderProduct &$OrderProduct) {
		$addAllowed = true;
		if (method_exists($OrderProduct, 'OrderCreatorAllowAddToContents')){
			$addAllowed = $OrderProduct->OrderCreatorAllowAddToContents();
		}

		if ($addAllowed === true){
			$OrderProduct->regenerateId();
			while(array_key_exists($OrderProduct->getId(), $this->Contents)){
				$OrderProduct->regenerateId();
			}

			$OrderProduct->OrderCreatorOnAddToContents();
			$this->Contents[$OrderProduct->getId()] = $OrderProduct;
			$this->cleanUp();
		}
		return $addAllowed;
	}

	/**
	 *
	 */
	private function cleanUp() {
		foreach($this->getContents() as $cartProduct){
			if ($cartProduct->getQuantity() < 1){
				$this->removeFromContents($cartProduct->getId());
			}
		}
	}

	/**
	 * @param string $id
	 */
	public function remove($id) {
		$this->removeFromContents($id);
	}

	/**
	 * @param string $id
	 */
	private function removeFromContents($id) {
		if (array_key_exists($id, $this->Contents)){
			unset($this->Contents[$id]);
		}
	}

	/**
	 * Used when loading the sale from the database
	 *
	 * @param AccountsReceivableSalesProducts $Product
	 */
	public function jsonDecodeProduct(AccountsReceivableSalesProducts $Product){
		$OrderProduct = new OrderCreatorProduct();
		$OrderProduct->jsonDecodeProduct($Product);
		$this->Contents[$OrderProduct->getId()] = $OrderProduct;
	}

	/**
	 * Used from init method in OrderCreator class
	 *
	 * @param string $data
	 */
	public function jsonDecode($data){
		$Contents = json_decode($data, true);
		foreach($Contents as $Id => $pInfo){
			$OrderProduct = new OrderCreatorProduct();
			$OrderProduct->jsonDecode($pInfo);

			$this->Contents[$OrderProduct->getId()] = $OrderProduct;
		}
	}
}

require(dirname(__FILE__) . '/Product.php');
