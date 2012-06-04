<?php
class AccountsReceivableModuleInvoiceConvertEstimate
{

	public static function convert($id) {
		$Id = AccountsReceivable::convert(array(
			'id'     => $id,
			'model'  => 'AccountsReceivableEstimate',
			'idCol'  => 'estimate_id',
			'module' => 'estimate'
		), array(
			'model' => 'AccountsReceivableInvoice',
			'idCol' => 'invoice_id'
		));

		EventManager::attachActionResponse(itw_app_link('appExt=orderCreator&iID=' . $Id, 'default', 'new'), 'redirect');
	}
}