<?php
class OrderProductTypeStandard extends ProductTypeStandard
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
			PurchaseTypeModules::$classPrefix = 'OrderPurchaseType';
			$isLoaded = PurchaseTypeModules::loadModule(
				$PurchaseType,
				sysConfig::getDirFsCatalog() . 'includes/classes/Order/ProductManager/PurchaseTypeModules/' . $PurchaseType . '/'
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

	public function onGetEmailList(&$orderedProductsString){
		$orderedProductsString .= ' - Purchase Type: ' . $this->PurchaseTypeClass->getTitle() . "\n";

		if (method_exists($this->PurchaseTypeClass, 'onGetEmailList')){
			$this->PurchaseTypeClass->onGetEmailList(&$orderedProductsString);
		}
	}
}