<?php
class AccountsReceivableModuleOrderConvertEstimate
{

	public static function convert($id) {
		$Id = AccountsReceivable::convert(array(
			'id'     => $id,
			'model'  => 'AccountsReceivableEstimate',
			'idCol'  => 'estimate_id',
			'module' => 'estimate'
		), array(
			'model' => 'AccountsReceivableOrder',
			'idCol' => 'order_id'
		));

		EventManager::attachActionResponse(itw_app_link('appExt=orderCreator&oID=' . $Id, 'default', 'new'), 'redirect');
	}
}