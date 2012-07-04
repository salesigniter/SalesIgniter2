<?php
/**
 * Product manager class for the checkout sale class
 *
 * @package    CheckoutSale\ProductManager
 * @author     Stephen Walker <stephen@itwebexperts.com>
 * @since      2.0
 * @copyright  2012 I.T. Web Experts
 * @license    http://itwebexperts.com/license/ses-license.php
 */

class CheckoutSaleProductManager extends OrderProductManager
{

	/**
	 * @var CheckoutSaleProduct[]
	 */
	public $Contents = array();

	/**
	 * @return CheckoutSaleProduct|OrderProduct
	 */
	public function getContentProductClass()
	{
		return new CheckoutSaleProduct();
	}

	public function addErrorMessage()
	{
	}

	/**
	 * @return array|CheckoutSaleProduct[]|OrderProduct[]
	 */
	public function &getContents()
	{
		return $this->Contents;
	}

	/**
	 * @param $id
	 * @return bool|CheckoutSaleProduct|OrderProduct
	 */
	public function &get($id)
	{
		$id = (int)$id;
		if (array_key_exists($id, $this->Contents)){
			return $this->Contents[$id];
		}
		return false;
	}

	/**
	 * @param OrderProduct $CheckoutSaleProduct
	 * @return bool
	 */
	public function add(OrderProduct &$CheckoutSaleProduct)
	{
		$addAllowed = true;
		if (method_exists($CheckoutSaleProduct, 'allowAddToContents')){
			$addAllowed = $CheckoutSaleProduct->allowAddToContents();
		}

		$Success = false;
		if ($addAllowed === true){
			$CheckoutSaleProduct->regenerateId();
			while(array_key_exists($CheckoutSaleProduct->getId(), $this->Contents)){
				$CheckoutSaleProduct->regenerateId();
			}

			$CheckoutSaleProduct->onAddToContents();

			$this->Contents[$CheckoutSaleProduct->getId()] = $CheckoutSaleProduct;
			$this->cleanUp();
			$Success = true;
		}
		return $Success;
	}

	public function importShoppingCartProduct(ShoppingCartProduct $CartProduct, CheckoutSale &$CheckoutSale)
	{
		if ($this->cartProductExists($CartProduct->getId())){
			$OrderProduct = $this->getByCartProductHash($CartProduct->getId());
			//echo __FILE__ . '::' . __LINE__ . '<br>';
			$Success = $OrderProduct->updateFromCart($CartProduct);
			if ($Success === false){
				$this->addErrorMessage('There was an error updating a cart product!');
			}
			else {
				$CheckoutSale->TotalManager->onProductUpdated($this);
			}
		}
		else {
			//echo __FILE__ . '::' . __LINE__ . '<br>';
			//echo '<div style="margin-left:15px;">';
			$SaleProduct = new CheckoutSaleProduct();

			$SaleProduct->onAddFromCart($CartProduct);

			//echo __FILE__ . '::' . __LINE__ . '<br>';
			$Success = $this->add($SaleProduct);
			if ($Success === false){
				$this->addErrorMessage('There was an error adding a cart product to the sale!');
			}
			else {
				//echo '</div>';
				//echo __FILE__ . '::' . __LINE__ . '<br>';
				//echo '<div style="margin-left:15px;">';
				$CheckoutSale->TotalManager->onProductAdded($this);
				//echo '</div>';
			}
		}
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
			}
			elseif ($CartProduct->getCartProductHashId() == '') {
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
