<?php
/**
 * Product manager class for the order creator
 *
 * @package    OrderCreator\ProductManager
 * @author     Stephen Walker <stephen@itwebexperts.com>
 * @since      2.0
 * @copyright  2012 I.T. Web Experts
 * @license    http://itwebexperts.com/license/ses-license.php
 */

class OrderCreatorProductManager extends OrderProductManager
{

	/**
	 * @var OrderCreatorProduct[]
	 */
	public $Contents = array();

	/**
	 * @return OrderCreatorProduct|OrderProduct
	 */
	public function getContentProductClass()
	{
		return new OrderCreatorProduct();
	}

	/**
	 * @param $id
	 * @return bool|OrderCreatorProduct|OrderProduct
	 */
	public function get($id)
	{
		$OrderedProduct = parent::get((int)$id);
		return $OrderedProduct;
	}

	/**
	 * @return array|OrderCreatorProduct[]|OrderProduct[]
	 */
	public function &getContents()
	{
		return $this->Contents;
	}

	/**
	 *
	 */
	public function init()
	{
		foreach($this->getContents() as $OrderedProduct){
			$OrderedProduct->init();
		}
	}

	/**
	 *
	 */
	public function updateFromPost()
	{
		foreach($_POST['product'] as $id => $pInfo){
			if (!isset($pInfo['qty'])){
				continue;
			}

			$Product = $this->get($id);
			if ($Product === false || is_null($Product)){
				echo 'Error: A Product Was Posted That Was Not In The Product Manager. ( ID:' . $id . ' )';
				echo '<pre>';
				print_r(array_keys($this->Contents));
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
	 * @param OrderCreatorProduct $OrderProduct
	 * @return bool
	 */
	public function add(OrderCreatorProduct &$OrderProduct)
	{
		$addAllowed = true;
		if (method_exists($OrderProduct, 'allowAddToContents')){
			$addAllowed = $OrderProduct->allowAddToContents();
		}

		$Success = false;
		if ($addAllowed === true){
			$OrderProduct->regenerateId();
			while(array_key_exists($OrderProduct->getId(), $this->Contents)){
				$OrderProduct->regenerateId();
			}

			$OrderProduct->onAddToContents();

			$this->Contents[$OrderProduct->getId()] = $OrderProduct;
			$this->cleanUp();
			$Success = true;
		}
		return $Success;
	}

	/**
	 *
	 */
	private function cleanUp()
	{
		foreach($this->getContents() as $cartProduct){
			if ($cartProduct->getQuantity() < 1){
				$this->removeFromContents($cartProduct->getId());
			}
		}
	}

	/**
	 * @param string $id
	 */
	public function remove($id)
	{
		$this->removeFromContents($id);
	}

	/**
	 * @param string $id
	 */
	private function removeFromContents($id)
	{
		if (array_key_exists($id, $this->Contents)){
			unset($this->Contents[$id]);
		}
	}

	/**
	 * @return array
	 */
	public function prepareJsonSave()
	{
		$ProductsJsonArray = array();
		foreach($this->getContents() as $Id => $OrderProduct){
			$ProductsJsonArray[$Id] = $OrderProduct->prepareJsonSave();
		}
		//echo __FILE__ . '::' . __LINE__ . '<pre>';print_r($ProductsJsonArray);
		return $ProductsJsonArray;
	}

	/**
	 * Used from init method in OrderCreator class
	 *
	 * @param string $data
	 */
	public function jsonDecode($data)
	{
		$Contents = json_decode($data, true);
		foreach($Contents as $Id => $pInfo){
			$OrderProduct = new OrderCreatorProduct();
			$OrderProduct->jsonDecode($pInfo);

			$this->Contents[$OrderProduct->getId()] = $OrderProduct;
		}
	}

	public function onSaveProgress(&$SaleProducts)
	{
		$SaleProducts->clear();
		foreach($this->getContents() as $OrderProduct){
			$SaleProduct = $SaleProducts
				->getTable()
				->getRecord();

			$OrderProduct->onSaveProgress($SaleProduct);

			$SaleProducts->add($SaleProduct);
		}
	}
}

require(__DIR__ . '/Product.php');
