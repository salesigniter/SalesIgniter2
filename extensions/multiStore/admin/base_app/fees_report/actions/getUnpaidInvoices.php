<?php
$QBilled = Doctrine_Query::create()
	->select(
		'id, ' .
		'date_added, ' .
		'(fee_royalty + fee_management + fee_marketing + fee_labor + fee_parts) as InvoiceTotal'
	)
	->from('StoresFeesInvoices')
	->where('stores_id = ?', $_GET['sID'])
	->andWhere('paid = ?', '0')
	->orderBy('date_added desc')
	->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
$Invoices = array();
if ($QBilled){
	foreach($QBilled as $Invoice){
		$Invoices[] = array(
			'id' => $Invoice['id'],
			'date_added' => date(sysLanguage::getDateFormat(), $Invoice['date_added']),
			'total' => $currencies->format($Invoice['InvoiceTotal'])
		);
	}
}

EventManager::attachActionResponse(array(
		'success' => true,
		'invoices' => $Invoices
	), 'json');