<?php
require(sysConfig::getDirFsCatalog() . 'extensions/orderCreator/admin/classes/product/Base.php');

class OrderCreatorProduct extends OrderProduct implements Serializable {

	public function __construct($pInfo = null){
		parent::__construct($pInfo);
		if (is_null($pInfo) === false){
			$ProductType =& $this->getProductTypeClass();
			if (method_exists($ProductType, 'processAddToOrder')){
				$ProductType->processAddToOrder($pInfo);
			}
			/*$this->pInfo = $pInfo;
			$this->productClass = new OrderCreatorProductProduct((int) $this->pInfo['products_id']);
			$this->pInfo['products_weight'] = $this->productClass->getWeight();
			$this->purchaseTypeClass = $this->productClass->getPurchaseType($this->pInfo['purchase_type']);
			$this->purchaseTypeClass->processAddToOrder($this->pInfo);

			EventManager::notify('OrderEditorProductAddToCart', $this->pInfo, $this->productClass, $this->purchaseTypeClass);*/
		}
	}

	public function init(){
		/*$this->productClass = new OrderCreatorProductProduct((int) $this->pInfo['products_id']);
		$this->purchaseTypeClass = $this->productClass->getPurchaseType($this->pInfo['purchase_type']);*/
		$this->productClass = new Product((int)$this->pInfo['products_id']);

		$ProductType =& $this->getProductTypeClass();
		$ProductType->setProductId($this->pInfo['products_id']);
		if (method_exists($ProductType, 'OrderProductOnInit')){
			$ProductType->OrderProductOnInit($this->pInfo);
		}
		$this->setWeight($this->getProductClass()->getWeight());
	}

	public function serialize(){
		$data = array(
			'id' => $this->id,
			'pInfo' => $this->pInfo
		);
		return serialize($data);
	}

	public function unserialize($data){
		$data = unserialize($data);
		foreach($data as $key => $dInfo){
			$this->$key = $dInfo;
		}
	}
	
	public function setProductsId($pID){
		global $Editor;
		$this->productClass = new Product($pID);

		$this->pInfo['products_id'] = $pID;
		$this->pInfo['products_name'] = $this->getProductClass()->getName();
		$this->pInfo['products_weight'] = $this->getProductClass()->getWeight();
		$this->pInfo['products_model'] = $this->getProductClass()->getModel();
		
		$taxAddress = $Editor->AddressManager->getAddress('billing');
		$this->setTaxRate(tep_get_tax_rate(
				$this->getProductClass()->getProductTypeClass()->getTaxClassId(),
			(is_object($taxAddress) ? $taxAddress->getCountryId() : -1),
				(is_object($taxAddress) ? $taxAddress->getZoneId() : -1)
		));
	}


	public function updateProductInfo(){
		$updateAllowed = true;
		$ProductType = $this->getProductTypeClass();
		if (method_exists($ProductType, 'OrderCreatorAllowProductUpdate')){
			$updateAllowed = $ProductType->OrderCreatorAllowProductUpdate($this);
		}

		if ($updateAllowed === true && method_exists($ProductType, 'updateOrderCreatorProductInfo')){
			$pInfo = $this->pInfo;
			$ProductType->updateOrderCreatorProductInfo(&$pInfo);
			$this->pInfo = $pInfo;
		}
	}

	public function OrderCreatorAllowAddToContents(){
		$addAllowed = true;
		$ProductType = $this->getProductTypeClass();
		if (method_exists($ProductType, 'OrderCreatorAllowAddToContents')){
			$addAllowed = $ProductType->OrderCreatorAllowAddToContents($this);
		}
		return $addAllowed;
	}
	
	public function setTaxRate($val){
		$this->pInfo['products_tax'] = $val;
	}

	public function setQuantity($val){
		$this->pInfo['products_quantity'] = $val;
	}
	
	public function setPrice($val){
		$this->pInfo['products_price'] = $val;
		$this->pInfo['final_price'] = $val;
	}

	public function setBarcodeId($val){
		$this->pInfo['barcode_id'] = $val;
	}

	public function getBarcodeId(){
		return $this->pInfo['barcode_id'];
	}

	public function hasBarcodeId(){
		return (isset($this->pInfo['barcode_id']));
	}

	public function getTaxRateEdit(){
		return '<input type="text" size="5" class="ui-widget-content taxRate" name="product[' . $this->id . '][tax_rate]" value="' . $this->getTaxRate() . '">%';
	}

	public function getPriceEdit($incQty = false, $incTax = false){
		global $Editor, $currencies;
		$html = '';
		if ($incQty === false && $incTax === false){
			$html = '<input type="text" size="5" class="ui-widget-content priceEx" name="product[' . $this->id . '][price]" value="' . $this->getFinalPrice($incQty, $incTax) . '">';
		}elseif ($incQty === true && $incTax === false){
			$html = '<b class="priceExTotal">' . $currencies->format($this->getFinalPrice($incQty, $incTax), true, $Editor->getCurrency(), $Editor->getCurrencyValue()) . '</b>';
		}elseif ($incQty === false && $incTax === true){
			$html = '<b class="priceIn">' . $currencies->format($this->getFinalPrice($incQty, $incTax), true, $Editor->getCurrency(), $Editor->getCurrencyValue()) . '</b>';
		}elseif ($incQty === true && $incTax === true){
			$html = '<b class="priceInTotal">' . $currencies->format($this->getFinalPrice($incQty, $incTax), true, $Editor->getCurrency(), $Editor->getCurrencyValue()) . '</b>';
		}
		return $html;
	}

	public function getQuantityEdit(){
		return '<input type="text" size="3" class="ui-widget-content productQty" name="product[' . $this->id . '][qty]" value="' . $this->getQuantity() . '">&nbsp;x';
	}

	public function getNameEdit($excludedPurchaseTypes = array()){
		$ProductType = $this->getProductTypeClass();
		$productsName = $this->getName();
		if (method_exists($ProductType, 'OrderCreatorAfterProductName')){
			$productsName .= $ProductType->OrderCreatorAfterProductName($this);
		}

		$contents = EventManager::notifyWithReturn('OrderProductAfterProductNameEdit', $this);
		foreach($contents as $content){
			$productsName .= $content;
		}
		return $productsName;
	}

	public function getBarcodeEdit(){
		$barcodeDrop = '';
		$ProductType = $this->getProductTypeClass();
		if (method_exists($ProductType, 'OrderCreatorBarcodeEdit')){
			$barcodeDrop = $ProductType->OrderCreatorBarcodeEdit($this);
		}
		return $barcodeDrop;
	}

	public function updateInfo($newInfo){
		$newProductInfo = $this->pInfo;
		foreach($newInfo as $k => $v){
			$newProductInfo[$k] = $v;
		}
		$this->pInfo = $newProductInfo;
		$this->purchaseTypeClass->processUpdateCart(&$this->pInfo);
	}

	public function onAddToCollection(&$OrderedProduct){
		$ProductType =& $this->productClass->getProductTypeClass();
		if (method_exists($ProductType, 'addToOrdersProductCollection')){
			$ProductType->addToOrdersProductCollection($this, $OrderedProduct);
		}
		//print_r($this);
		EventManager::notify('OrderCreatorProductAddToCollection', $this, &$OrderedProduct);
	}
}
?>