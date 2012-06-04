<?php
class AccountsReceivableModuleInvoiceConvertShoppingCart
{

	public static function convert($id) {
		$Id = AccountsReceivable::convert(array(
			'id'     => $id,
			'model'  => 'AccountsReceivableShoppingCart',
			'idCol'  => 'shopping_cart_id',
			'module' => 'shoppingCart'
		), array(
			'model' => 'AccountsReceivableInvoice',
			'idCol' => 'invoice_id'
		));

		EventManager::attachActionResponse(itw_app_link('appExt=orderCreator&iID=' . $Id, 'default', 'new'), 'redirect');
	}
}