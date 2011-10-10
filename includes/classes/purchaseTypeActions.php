<?php
class PurchaseTypeActions {

	static public function AddToOrder(&$PurchaseType){
		global $ShoppingCart;
		$qty = 1;
		if ($PurchaseType->getConfigData('ALLOW_MULTIPLE_IN_CART') == 'True' && isset($_POST['quantity'])){
			if (is_numeric($_POST['quantity'])){
				$qty = $_POST['quantity'];
			}elseif (is_array($_POST['quantity']) && isset($_POST['quantity'][$PurchaseType->getCode()])){
				$qty = $_POST['quantity'][$PurchaseType->getCode()];
			}
		}
		
		$ShoppingCart->addProduct($PurchaseType->getData('products_id'), $PurchaseType->getCode(), $qty);
		$PurchaseType->onAddToOrder();
	}

	static public function RemoveFromOrder(&$PurchaseType){
		global $ShoppingCart;
		$ShoppingCart->removeProduct($PurchaseType->getData('products_id'));
		$PurchaseType->onRemoveFromOrder();
	}

	static public function Return(&$PurchaseType){
		$PurchaseType->onReturn();
	}

	static public function Send(&$PurchaseType){
		$PurchaseType->onSend();
	}
}
