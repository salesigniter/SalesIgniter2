<?php
if (isset($_POST['invoice'])){
	foreach($_POST['invoice'] as $invoiceId){
		Doctrine_Query::create()
			->update('StoresFeesInvoices')
			->set('paid', '?', '1')
			->set('date_paid', '?', time())
			->where('id = ?', $invoiceId)
			->execute();
	}
}

EventManager::attachActionResponse(array(
		'success' => true
	), 'json');