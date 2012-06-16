<?php
class ProductTypeBase extends ModuleBase {

	public function CheckoutSaleOnAddToContents(CheckoutSaleProduct $CheckoutSaleProduct){
		//echo __FILE__ . '::' . __LINE__ . '<br>';
		//echo '<div style="margin-left:15px;">';
		//echo '</div>';
	}

	public function prepareJsonSave(OrderProduct &$OrderProduct){
	}

	public function jsonDecode(OrderProduct &$OrderProduct, $ProductTypeJson){
	}
}