<?php
/*
	Related Products Version 1

	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class Extension_productAddons extends ExtensionBase {

	public function __construct(){
		parent::__construct('productAddons');
	}

	public function init(){
		if ($this->enabled === false) return;

		EventManager::attachEvents(array(
		'PurchaseTypeHiddenFields'
		), null, $this);

		require(dirname(__FILE__) . '/classEvents/ShoppingCart.php');
		$eventClass = new ShoppingCart_productAddons();
		$eventClass->init();
	}

	public function PurchaseTypeHiddenFields(&$hiddenFields){
		foreach($_POST['addon_product'] as $addon => $val){
			$purchaseTypeCode = $_POST['addon_product_type'][$addon];
			$hiddenFields[] = tep_draw_hidden_field('addon_product['.$addon.']', $val);
			$hiddenFields[] = tep_draw_hidden_field('addon_product_type['.$addon.']', $purchaseTypeCode);
		}
	}

}
?>