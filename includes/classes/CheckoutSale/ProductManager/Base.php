<?php
/**
 * Product manager class for the checkout sale class
 *
 * @package   CheckoutSale
 * @author    Stephen Walker <stephen@itwebexperts.com>
 * @copyright Copyright (c) 2011, I.T. Web Experts
 */

class CheckoutSaleProductManager extends OrderProductManager
{

	/**
	 * @var CheckoutSaleProduct[]
	 */
	public $Contents = array();

	/**
	 * @param array|null $orderedProducts
	 * @param int|null   $order
	 */
	public function __construct(array $orderedProducts = null, $order = nul)
	{
	}

	/**
	 * @return array|CheckoutSaleProduct[]|OrderProduct[]
	 */
	public function getContents()
	{
		return $this->Contents;
	}

	/**
	 * @param int $id
	 * @return bool|CheckoutSaleProduct|OrderProduct
	 */
	public function get($id)
	{
		$OrderedProduct = parent::get((int)$id);
		return $OrderedProduct;
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
	 * @param OrderProduct $OrderProduct
	 * @return bool
	 */
	public function add(OrderProduct &$OrderProduct)
	{
		$addAllowed = true;
		if (method_exists($OrderProduct, 'CheckoutSaleAllowAddToContents')){
			$addAllowed = $OrderProduct->CheckoutSaleAllowAddToContents();
		}

		$Success = false;
		if ($addAllowed === true){
			$OrderProduct->regenerateId();
			while(array_key_exists($OrderProduct->getId(), $this->Contents)){
				$OrderProduct->regenerateId();
			}

			//echo __FILE__ . '::' . __LINE__ . '<br>';
			$OrderProduct->CheckoutSaleOnAddToContents();
			$this->Contents[$OrderProduct->getId()] = $OrderProduct;
			$this->cleanUp();
			$Success = true;
		}
		return $Success;
	}

	/**
	 * @param ShoppingCartProduct $CartProduct
	 * @return bool
	 */
	public function addFromCart(ShoppingCartProduct &$CartProduct)
	{
		$SaleProduct = new CheckoutSaleProduct();
		$SaleProduct->setCartProductHashId($CartProduct->getId());
		$SaleProduct->setProductId($CartProduct->getIdString());
		$SaleProduct->setQuantity($CartProduct->getQuantity());
		$SaleProduct->setPrice($CartProduct->getPrice());

		//echo __FILE__ . '::' . __LINE__ . '<br>';
		$Success = $this->add($SaleProduct);
		return $Success;
	}

	/**
	 *
	 */
	public function cleanUp()
	{
		foreach($this->getContents() as $CartProduct){
			if ($CartProduct->getQuantity() < 1){
				//echo __FILE__ . '::' . __LINE__ . '<br>';
				$this->removeFromContents($CartProduct->getId());
			}elseif ($CartProduct->getCartProductHashId() == ''){
				//echo __FILE__ . '::' . __LINE__ . '<br>';
				$this->removeFromContents($CartProduct->getId());
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

	public function cartProductExists($hashId)
	{
		$exists = false;
		foreach($this->getContents() as $SaleProduct){
			if ($SaleProduct->getCartProductHashId() == $hashId){
				$exists = true;
			}
			//echo __FILE__ . '::' . __LINE__ . '::' . (int)$exists . '::' . $SaleProduct->getCartProductHashId() . '==' . $hashId . '<br>';
		}
		return $exists;
	}

	public function &getByCartProductHash($hashId)
	{
		$Product = false;
		foreach($this->getContents() as $SaleProduct){
			if ($SaleProduct->getCartProductHashId() == $hashId){
				$Product = $SaleProduct;
			}
		}
		return $Product;
	}

	/**
	 * Used when loading the sale from the database
	 *
	 * @param AccountsReceivableSalesProducts $Product
	 */
	public function jsonDecodeProduct(AccountsReceivableSalesProducts $Product)
	{
		$OrderProduct = new CheckoutSaleProduct();
		$OrderProduct->jsonDecodeProduct($Product);
		$this->Contents[$OrderProduct->getId()] = $OrderProduct;
	}

	/**
	 * Used from init method in CheckoutSale class
	 *
	 * @param string $data
	 */
	public function jsonDecode($data)
	{
		$Contents = json_decode($data, true);
		foreach($Contents as $Id => $pInfo){
			$OrderProduct = new CheckoutSaleProduct();
			$OrderProduct->jsonDecode($pInfo);

			$this->Contents[$OrderProduct->getId()] = $OrderProduct;
		}
	}
}

require(dirname(__FILE__) . '/Product.php');
