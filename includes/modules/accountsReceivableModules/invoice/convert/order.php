<?php
class AccountsReceivableModuleInvoiceConvertOrder
{

	public static function convert($id) {
		$Id = AccountsReceivable::convert(array(
			'id'     => $id,
			'model'  => 'AccountsReceivableOrder',
			'idCol'  => 'order_id',
			'module' => 'order'
		), array(
			'model' => 'AccountsReceivableInvoice',
			'idCol' => 'invoice_id'
		));

		EventManager::attachActionResponse(itw_app_link('appExt=orderCreator&iID=' . $Id, 'default', 'new'), 'redirect');
	}
}