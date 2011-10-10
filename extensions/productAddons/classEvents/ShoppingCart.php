<?php
	class ShoppingCart_productAddons {
		
		public function __construct(){
		}
		
		public function init(){
			
			EventManager::attachEvents(array(
				'AddToCartAfterAction',
			), 'ShoppingCart', $this);
		}


		

		public function AddToCartAfterAction(ShoppingCartProduct &$cartProduct){
			global $messageStack, $ShoppingCart;
			foreach($_POST['addon_product'] as $addon => $val){
				$purchaseTypeCode = $_POST['addon_product_type'][$addon];
				unset($_POST['addon_product'][$addon]);
				$ShoppingCart->add($addon);

			}
		}
	}
?>