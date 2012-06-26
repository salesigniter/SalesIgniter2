<?php
class CheckoutSaleProductTypeStandard extends ProductTypeStandard
{

	/**
	 * @var PurchaseTypeBase
	 */
	protected $PurchaseTypeClass;

	/**
	 * @param bool $PurchaseType
	 * @param bool $ignoreStatus
	 * @return null
	 */
	public function loadPurchaseType($PurchaseType = false, $ignoreStatus = false)
	{
		$PurchaseType = $this->getPurchaseTypeCode($PurchaseType);
		if ($PurchaseType === false || $this->purchaseTypeEnabled($PurchaseType) === false){
			if ($ignoreStatus === false){
				return null;
			}
		}

		if (is_object($this->PurchaseTypeClass) === false){
			PurchaseTypeModules::$classPrefix = 'CheckoutSalePurchaseType';
			$isLoaded = PurchaseTypeModules::loadModule(
				$PurchaseType,
				sysConfig::getDirFsCatalog() . 'includes/classes/CheckoutSale/ProductManager/PurchaseTypeModules/' . $PurchaseType . '/'
			);

			if ($isLoaded === true){
				$this->PurchaseTypeClass = PurchaseTypeModules::getModule($PurchaseType);
				if ($this->PurchaseTypeClass === false){
					echo '<pre>';
					debug_print_backtrace();
					echo '</pre>';
					die('Error loading purchase type: ' . $PurchaseType);
				}
				$this->PurchaseTypeClass->loadData($this->getProductId());
				$this->PurchaseTypeClass->loadInventoryData($this->getProductId());
			}
		}
	}

	/**
	 * @return PurchaseTypeBase
	 */
	public function &getPurchaseTypeClass()
	{
		return $this->PurchaseTypeClass;
	}

	/**
	 * @param OrderProduct $OrderProduct
	 * @param null         $Qty
	 * @return bool
	 */
	public function hasEnoughInventory(OrderProduct $OrderProduct, $Qty = null)
	{
		$return = true;
		if (method_exists($this->PurchaseTypeClass, 'hasEnoughInventory')){
			$return = $this->PurchaseTypeClass->hasEnoughInventory($OrderProduct, $Qty);
		}
		return $return;
	}

	public function allowAddToContents()
	{
		return true;
	}

	public function onAddToContents(CheckoutSaleProduct $SaleProduct)
	{
	}

	public function onAddFromCart(CheckoutSaleProduct $SaleProduct, ShoppingCartProduct $CartProduct)
	{
		$this->loadPurchaseType($CartProduct->getInfo('purchase_type'));

		if (method_exists($this->PurchaseTypeClass, 'onAddFromCart')){
			$this->PurchaseTypeClass->onAddFromCart($SaleProduct, $CartProduct);
		}
	}

	public function onUpdateFromCart(CheckoutSaleProduct $SaleProduct, ShoppingCartProduct $CartProduct)
	{
		if (method_exists($this->PurchaseTypeClass, 'onUpdateFromCart')){
			$this->PurchaseTypeClass->onUpdateFromCart($SaleProduct, $CartProduct);
		}
	}

	public function onSetQuantity(OrderProduct &$OrderProduct)
	{
	}

	/**
	 * @param OrderProduct                    $OrderProduct
	 * @param AccountsReceivableSalesProducts $SaleProduct
	 * @param bool                            $AssignInventory
	 */
	public function onSaveSale(OrderProduct $OrderProduct, AccountsReceivableSalesProducts &$SaleProduct, $AssignInventory = false)
	{
		if (method_exists($this->PurchaseTypeClass, 'onSaveSale')){
			$this->PurchaseTypeClass->onSaveSale($OrderProduct, $SaleProduct, $AssignInventory);
		}
	}

	/*public function onSaveProgress(OrderProduct $OrderProduct, AccountsReceivableSalesProducts $SaleProduct){

	}

	public function hasEnoughInventory(OrderProduct $OrderProduct, $Qty){

	}*/

	/**
	 * @param OrderProduct $OrderProduct
	 * @return array|void
	 */
	public function prepareJsonSave(OrderProduct &$OrderProduct)
	{
		$toEncode = array(
			'purchase_type' => $this->PurchaseTypeClass->getCode()
		);
		if (method_exists($this->PurchaseTypeClass, 'prepareJsonSave')){
			$toEncode['PurchaseTypeJson'] = $this->PurchaseTypeClass->prepareJsonSave($this);
		}
		return $toEncode;
	}

	/*public function jsonDecodeProduct(OrderProduct $OrderProduct, array $ProductTypeJson){

	}*/

	/**
	 * @param OrderProduct $OrderProduct
	 * @param array        $ProductTypeJson
	 */
	public function jsonDecode(OrderProduct &$OrderProduct, array $ProductTypeJson)
	{
		$this->loadPurchaseType($ProductTypeJson['purchase_type']);

		if (method_exists($this->PurchaseTypeClass, 'jsonDecode')){
			$this->PurchaseTypeClass->jsonDecode($OrderProduct, $ProductTypeJson['PurchaseTypeJson']);
		}
	}
}