<?php
class AccountsReceivableModuleEstimateConvertShoppingCart
{

	public static function convert($id) {
		$Id = AccountsReceivable::convert(array(
			'id'     => $id,
			'model'  => 'AccountsReceivableShoppingCart',
			'idCol'  => 'shopping_cart_id',
			'module' => 'shoppingCart'
		), array(
			'model' => 'AccountsReceivableEstimate',
			'idCol' => 'estimate_id'
		));

		EventManager::attachActionResponse(itw_app_link('appExt=orderCreator&eID=' . $Id, 'default', 'new'), 'redirect');
	}
}