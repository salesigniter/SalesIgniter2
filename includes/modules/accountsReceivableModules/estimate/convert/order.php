<?php
class AccountsReceivableModuleEstimateConvertOrder
{

	public static function convert($id) {
		$Id = AccountsReceivable::convert(array(
			'id'     => $id,
			'model'  => 'AccountsReceivableOrder',
			'idCol'  => 'order_id',
			'module' => 'order'
		), array(
			'model' => 'AccountsReceivableEstimate',
			'idCol' => 'estimate_id'
		));

		EventManager::attachActionResponse(itw_app_link('appExt=orderCreator&eID=' . $Id, 'default', 'new'), 'redirect');
	}
}