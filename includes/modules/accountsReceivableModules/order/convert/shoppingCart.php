<?php
class AccountsReceivableModuleOrderConvertShoppingCart
{

	public static function convert($id) {
		$Id = AccountsReceivable::convert(array(
			'id'     => $id,
			'model'  => 'AccountsReceivableShoppingCart',
			'idCol'  => 'shoping_cart_id',
			'module' => 'shoppingCart'
		), array(
			'model' => 'AccountsReceivableOrder',
			'idCol' => 'order_id'
		));

		EventManager::attachActionResponse(itw_app_link('appExt=orderCreator&oID=' . $Id, 'default', 'new'), 'redirect');
	}
}