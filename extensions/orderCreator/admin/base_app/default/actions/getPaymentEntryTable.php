<?php
if (isset($_POST['payment_method']) && !empty($_POST['payment_method'])){
	$Module = OrderPaymentModules::getModule($_POST['payment_method']);

	EventManager::attachActionResponse(array(
		'success' => true,
		'table' => $Module->getPaymentEntryTable(true)
	), 'json');
}else{
	EventManager::attachActionResponse(array(
		'success' => false
	), 'json');
}
