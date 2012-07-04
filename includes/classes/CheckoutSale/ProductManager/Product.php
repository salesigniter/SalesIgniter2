<?php
/**
 * Product class for the checkout sale product manager class
 *
 * @package   CheckoutSale\ProductManager
 * @author    Stephen Walker <stephen@itwebexperts.com>
 * @since     2.0
 * @copyright 2012 I.T. Web Experts
 * @license   http://itwebexperts.com/license/ses-license.php
 */

class CheckoutSaleProduct extends OrderProduct
{

	/**
	 * @var CheckoutSaleProductTypeStandard|CheckoutSaleProductTypePackage
	 */
	protected $ProductTypeClass;

	/**
	 * @return CheckoutSaleProductTypeStandard|CheckoutSaleProductTypePackage|ProductTypeBase
	 */
	public function &getProductTypeClass()
	{
		return $this->ProductTypeClass;
	}

	/**
	 * @param string $hashId
	 */
	public function setCartProductHashId($hashId)
	{
		$this->setInfo('cart_product_hash', $hashId);
	}

	/**
	 * @return string
	 */
	public function getCartProductHashId()
	{
		return $this->getInfo('cart_product_hash');
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

		if (method_exists($this->ProductTypeClass, 'onSetQuantity')){
			$this->ProductTypeClass->onSetQuantity($this);
		}
	}

	/**
	 * @param float $val
	 */
	public function setPrice($val)
	{
		$this->pInfo['products_price'] = (float)$val;
		$this->pInfo['final_price'] = (float)$val;

		if (method_exists($this->ProductTypeClass, 'onSetPrice')){
			$this->ProductTypeClass->onSetPrice($this);
		}
	}

	/**
	 * @param array $val
	 */
	public function setBarcodes(array $val)
	{
		$this->pInfo['Barcodes'] = $val;

		if (method_exists($this->ProductTypeClass, 'onSetBarcodes')){
			$this->ProductTypeClass->onSetBarcodes($this);
		}
	}

	/**
	 * @param int $productId
	 */
	public function loadProduct($productId)
	{
		$this->ProductClass = new Product((int)$productId);

		$ProductType = $this->ProductClass->getProductType();
		ProductTypeModules::$classPrefix = 'CheckoutSaleProductType';
		$isLoaded = ProductTypeModules::loadModule(
			$ProductType,
			sysConfig::getDirFsCatalog() . 'includes/classes/CheckoutSale/ProductManager/ProductTypeModules/' . $ProductType . '/'
		);
		if ($isLoaded === true){
			$this->ProductTypeClass = ProductTypeModules::getModule($ProductType);
			if ($this->ProductTypeClass === false){
				echo '<pre>';
				debug_print_backtrace();
				echo '</pre>';
				die('Error loading product type: ' . $ProductType);
			}
			$this->ProductTypeClass->setProductId($this->ProductClass->getId());
		}
	}

	/**
	 * @param int $pID
	 */
	public function setProductId($pID)
	{
		global $CheckoutSale;
		$this->loadProduct($pID);

		$this->pInfo['products_id'] = $pID;
		$this->pInfo['products_name'] = $this
			->getProductClass()
			->getName();
		$this->pInfo['products_weight'] = $this
			->getProductClass()
			->getWeight();
		$this->pInfo['products_model'] = $this
			->getProductClass()
			->getModel();

		$taxAddress = null;
		if (is_object($CheckoutSale->AddressManager)){
			$taxAddress = $CheckoutSale->AddressManager->getAddress('billing');
		}
		$this->setTaxRate(tep_get_tax_rate(
			$this->ProductTypeClass->getTaxClassId(),
			(is_object($taxAddress) ? $taxAddress->getCountryId() : -1),
			(is_object($taxAddress) ? $taxAddress->getZoneId() : -1)
		));
	}

	/**
	 * @return bool
	 */
	public function CheckoutSaleAllowAddToContents()
	{
		$addAllowed = true;
		if (method_exists($this->ProductTypeClass, 'AllowAddToContents')){
			$addAllowed = $this->ProductTypeClass->allowAddToContents($this);
		}
		return $addAllowed;
	}

	/**
	 *
	 */
	public function onAddToContents()
	{
		if (method_exists($this->ProductTypeClass, 'OnAddToContents')){
			$this->ProductTypeClass->onAddToContents($this);
		}
	}

	/**
	 * @param ShoppingCartProduct $CartProduct
	 */
	public function onAddFromCart(ShoppingCartProduct $CartProduct)
	{
		global $messageStack;
		$this->setCartProductHashId($CartProduct->getId());
		$this->setProductId($CartProduct->getIdString());
		$this->setQuantity($CartProduct->getQuantity());
		$this->setPrice($CartProduct->getPrice());

		if (method_exists($this->ProductTypeClass, 'OnAddFromCart')){
			$this->ProductTypeClass->onAddFromCart($this, $CartProduct);
		}
		if ($this->hasEnoughInventory($CartProduct->getQuantity()) === false){
			$Success = false;
			$messageStack->addSession('pageStack', 'One or more of your products are not available anymore.<br> - ' . $CartProduct->getName(), 'error');
			tep_redirect(itw_app_link(null, 'shoppingCart', 'default'));
		}
	}

	/**
	 * @param ShoppingCartProduct $CartProduct
	 * @return bool
	 */
	public function updateFromCart(ShoppingCartProduct $CartProduct)
	{
		global $messageStack;
		$Success = true;
		if ($this->hasEnoughInventory($CartProduct->getQuantity()) === false){
			$Success = false;
			$messageStack->addSession('pageStack', 'One or more of your products are not available anymore.<br> - ' . $CartProduct->getName(), 'error');
			tep_redirect(itw_app_link(null, 'shoppingCart', 'default'));
		}
		else {
			$this->setQuantity($CartProduct->getQuantity());
			$this->setPrice($CartProduct->getFinalPrice());

			if (method_exists($this->ProductTypeClass, 'OnUpdateFromCart')){
				$this->ProductTypeClass->onUpdateFromCart($this, $CartProduct);
			}
		}
		return $Success;
	}
}
