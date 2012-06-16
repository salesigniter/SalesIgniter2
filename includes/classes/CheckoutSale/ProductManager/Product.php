<?php
/**
 * Product class for the checkout sale product manager class
 *
 * @package   CheckoutSale
 * @author    Stephen Walker <stephen@itwebexperts.com>
 * @copyright Copyright (c) 2011, I.T. Web Experts
 */

class CheckoutSaleProduct extends OrderProduct
{

	/**
	 * @param array|null $pInfo
	 */
	public function __construct(array $pInfo = null)
	{
		if (is_null($pInfo) === false){
			$this->setProductId($pInfo['products_id']);

			$ProductType =& $this->getProductTypeClass();
			if (method_exists($ProductType, 'processAddToCheckoutSale')){
				$ProductType->processAddToCheckoutSale(&$pInfo);
			}

			$this->updateInfo($pInfo);
		}
	}

	public function updateFromCart(ShoppingCartProduct $CartProduct)
	{
		$this->setQuantity($CartProduct->getQuantity());

		$ProductType = $this->getProductTypeClass();
		if (method_exists($ProductType, 'updateFromCart')){
			$ProductType->updateFromCart($CartProduct);
		}
	}

	public function setCartProductHashId($hashId){
		$this->setInfo('cart_product_hash', $hashId);
	}

	public function getCartProductHashId(){
		return $this->getInfo('cart_product_hash');
	}

	/**
	 * @return ProductTypeGiftVoucher|ProductTypeMembership|ProductTypePackage|ProductTypeStandard
	 */
	public function &getProductTypeClass()
	{
		return $this->ProductTypeClass;
	}

	/**
	 *
	 */
	public function init()
	{
		$this->setProductId((int)$this->pInfo['products_id']);

		$ProductType =& $this->getProductTypeClass();
		if (method_exists($ProductType, 'CheckoutSaleProductOnInit')){
			$ProductType->CheckoutSaleProductOnInit(&$this->pInfo);
		}
	}

	/**
	 * @param int $pID
	 */
	public function setProductId($pID)
	{
		global $CheckoutSale;
		$this->productClass = new Product($pID);
		$this->loadProductTypeClass($this->productClass->getProductType());

		$this->pInfo['products_id'] = $pID;
		$this->pInfo['products_name'] = $this->getProductClass()->getName();
		$this->pInfo['products_weight'] = $this->getProductClass()->getWeight();
		$this->pInfo['products_model'] = $this->getProductClass()->getModel();

		$taxAddress = null;
		if (is_object($CheckoutSale->AddressManager)){
			$taxAddress = $CheckoutSale->AddressManager->getAddress('billing');
		}
		$this->setTaxRate(tep_get_tax_rate(
			$this->getProductTypeClass()->getTaxClassId(),
			(is_object($taxAddress) ? $taxAddress->getCountryId() : -1),
			(is_object($taxAddress) ? $taxAddress->getZoneId() : -1)
		));

		$ProductType = $this->getProductTypeClass();
		if (method_exists($ProductType, 'setProductId')){
			$ProductType->setProductId($this->pInfo['products_id']);
		}
	}

	/**
	 *
	 */
	public function CheckoutSaleUpdateProductInfo()
	{
		$updateAllowed = true;
		$ProductType = $this->getProductTypeClass();
		if (method_exists($ProductType, 'CheckoutSaleAllowProductUpdate')){
			$updateAllowed = $ProductType->CheckoutSaleAllowProductUpdate($this);
		}

		if ($updateAllowed === true && method_exists($ProductType, 'CheckoutSaleUpdateProductInfo')){
			$pInfo = $this->pInfo;
			$ProductType->CheckoutSaleUpdateProductInfo(&$pInfo);
			$this->pInfo = $pInfo;
		}
	}

	/**
	 * @return bool
	 */
	public function CheckoutSaleAllowAddToContents()
	{
		$addAllowed = true;
		$ProductType = $this->getProductTypeClass();
		if (method_exists($ProductType, 'CheckoutSaleAllowAddToContents')){
			$addAllowed = $ProductType->CheckoutSaleAllowAddToContents($this);
		}
		return $addAllowed;
	}

	/**
	 *
	 */
	public function CheckoutSaleOnAddToContents()
	{
		//echo __FILE__ . '::' . __LINE__ . '<br>';
		$ProductType = $this->getProductTypeClass();
		if (method_exists($ProductType, 'CheckoutSaleOnAddToContents')){
			//echo __FILE__ . '::' . __LINE__ . '<br>';
			$ProductType->CheckoutSaleOnAddToContents($this);
		}
	}

	/**
	 * @param float $val
	 */
	public function setTaxRate($val)
	{
		$this->pInfo['products_tax'] = (float)$val;
	}

	/**
	 * @param int $val
	 */
	public function setQuantity($val)
	{
		$this->pInfo['products_quantity'] = (int)$val;

		$ProductType = $this->getProductTypeClass();
		if (method_exists($ProductType, 'onSetQuantity')){
			$ProductType->onSetQuantity($this);
		}
	}

	/**
	 * @param float $val
	 */
	public function setPrice($val)
	{
		$this->pInfo['products_price'] = (float)$val;
		$this->pInfo['final_price'] = (float)$val;
	}

	/**
	 * @param array $val
	 */
	public function setBarcodes(array $val)
	{
		$this->pInfo['Barcodes'] = $val;
	}

	/**
	 * @return array
	 */
	public function getBarcodes()
	{
		return $this->pInfo['Barcodes'];
	}

	/**
	 * @return bool
	 */
	public function hasBarcodes()
	{
		return (isset($this->pInfo['Barcodes']));
	}

	/**
	 * @param string $k
	 * @param mixed  $v
	 */
	public function setInfo($k, $v = '')
	{
		if (is_array($k)){
			$this->pInfo = $k;
		}
		else {
			$this->pInfo[$k] = $v;
		}
	}

	/**
	 * @param array $newInfo
	 */
	public function updateInfo(array $newInfo)
	{
		$newProductInfo = $this->pInfo;
		foreach($newInfo as $k => $v){
			$newProductInfo[$k] = $v;
		}
		$this->pInfo = $newProductInfo;
		//$this->purchaseTypeClass->processUpdateCart(&$this->pInfo);
	}

	public function onUpdateCheckoutSaleProduct()
	{
		$this->updateInfo(array(
			'purchase_type' => $_GET['purchase_type']
		));

		$ProductType = $this->getProductTypeClass();
		if (method_exists($ProductType, 'onUpdateCheckoutSaleProduct')){
			$ProductType->onUpdateCheckoutSaleProduct($this);
		}
	}
}

?>