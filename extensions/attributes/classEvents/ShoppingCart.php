<?php
	class ShoppingCart_attributes {

		public function __construct($inputKey){
			$this->inputKey = $inputKey;
		}

		public function init(){
			EventManager::attachEvents(array(
				'AddToCartPrepare',
				'AddToCartAllow',
				'AddToCartBeforeAction',
				'UpdateProductPrepare'
			), 'ShoppingCart', $this);
		}
		
		public function AddToCartBeforeAction(&$cartProduct){
			if (isset($pID_info['attributes'])){
				$pInfo['attributes'] = $pID_info['attributes'];
			}
		}
		
		public function AddToCartPrepare(&$CartProductData){
			global $messageStack;
			$pID = $CartProductData['product_id'];
			$purchaseType = false;
			if (isset($CartProductData['purchaseType'])){
				$purchaseType = $CartProductData['purchaseType'];
			}

			if (isset($_POST[$this->inputKey])){
				if ($purchaseType !== false && isset($_POST[$this->inputKey][$purchaseType])){
					$Attributes = $_POST[$this->inputKey][$purchaseType];
				}else{
					$Attributes = $_POST[$this->inputKey];
				}
				$CartProductData['attributes'] = $Attributes;
			}else{
				$error = false;
				if (attributesUtil::productHasAttributes($pID, $purchaseType)){
					$error = true;
				}

				if ($error === true){
					$messageStack->addSession('pageStack', 'You must select the options in the box.', 'warning');
					tep_redirect(itw_app_link(tep_get_all_get_params(array('action')), 'product', 'info'));
				}
			}
		}

		public function UpdateProductPrepare(&$pID_string, &$pInfo){
			global $dontRun;
			if (isset($dontRun) && $dontRun === true){
				return;
			}

			$attributes = (isset($_POST[$this->inputKey][$pInfo['purchase_type']][$pID_string]) ? $_POST[$this->inputKey][$pID_string] : false);
			if ($attributes !== false){
				if (is_array($attributes)){
					reset($attributes);
					while(list($option, $value) = each($attributes)){
						$pInfo['attributes'][$option] = $value;
					}
				}
			}
		}

		public function AddToCartAllow($CartProductData, Product $Product){
			$attributes = (isset($CartProductData['attributes']) ? $CartProductData['attributes'] : false);
			if ($attributes !== false){
				$ProductType = $Product->getProductTypeClass();
				if (isset($CartProductData['purchase_type'])){
					$PurchaseType = $ProductType->getPurchaseType($CartProductData['purchase_type']);
					$inventoryCls = $PurchaseType->getInventoryClass();
					if ($inventoryCls->getControllerName() == 'attribute'){
						$invController = $inventoryCls->getController();
						$invController->setIdString(attributesUtil::getAttributeString($attributes));
						if ($invController->hasInventory() === false){
							if (sysConfig::get('EXTENSION_ATTRIBUTES_CART_CHECK') == 'False'){
								return false;
							}
						}
					}
				}
			}
			return true;
		}
	}
?>