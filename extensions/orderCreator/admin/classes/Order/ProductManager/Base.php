<?php
/**
 * Product manager class for the order creator
 *
 * @package OrderCreator
 * @author Stephen Walker <stephen@itwebexperts.com>
 * @copyright Copyright (c) 2011, I.T. Web Experts
 */

require(dirname(__FILE__) . '/Product.php');

class OrderCreatorProductManager extends OrderProductManager implements Serializable
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
				while(array_key_exists($orderedProduct->getId(), $this->Contents)){
					$orderedProduct->regenerateId();
				}

				$this->Contents[$orderedProduct->getId()] = $orderedProduct;
			}
			$this->cleanUp();
		}
	}

	/**
	 * @return string
	 */
	public function serialize() {
		$data = array(
			'orderId'  => $this->orderId,
			'Contents' => $this->Contents
		);
		return serialize($data);
	}

	/**
	 * @param string $data
	 */
	public function unserialize($data) {
		$data = unserialize($data);
		foreach($data as $key => $dInfo){
			$this->$key = $dInfo;
		}
	}

	/**
	 * @param int $id
	 * @return OrderCreatorProduct|bool
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
		global $currencies, $Editor;
		foreach($_POST['product'] as $id => $pInfo){
			if (!isset($pInfo['qty'])){
				continue;
			}

			$Product = $this->get($id);
			if (is_null($Product)){
				die('Error: A Product Was Posted That Was Not In The Product Manager. ( ID:' . $id . ' )');
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
	 * @param OrderCreatorProduct $orderedProduct
	 */
	public function add(OrderCreatorProduct &$orderedProduct) {
		$addAllowed = true;
		if (method_exists($orderedProduct, 'OrderCreatorAllowAddToContents')){
			$addAllowed = $orderedProduct->OrderCreatorAllowAddToContents();
		}

		if ($addAllowed === true){
			$orderedProduct->regenerateId();
			while(array_key_exists($orderedProduct->getId(), $this->Contents)){
				$orderedProduct->regenerateId();
			}

			if ($orderedProduct->getPrice() <= 0){
				$orderedProduct->OrderCreatorOnAddToContents();
			}
			$this->Contents[$orderedProduct->getId()] = $orderedProduct;
			$this->cleanUp();
		}
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
}

?>