<?php
$OrdersStatus = Doctrine_Core::getTable('OrdersStatus');
if (isset($_GET['status_id'])){
	$Status = $OrdersStatus->find((int)$_GET['status_id']);
}
else {
	$Status = $OrdersStatus->getRecord();
}

$Description = $Status->OrdersStatusDescription;
foreach($_POST['orders_status_name'] as $langId => $statusName){
	$Description[$langId]->language_id = $langId;
	$Description[$langId]->orders_status_name = $statusName;
}

$Status->save();

if (isset($_POST['default']) && ($_POST['default'] == 'on')){
	Doctrine_Query::create()
		->update('Configuration')
		->set('configuration_value', '?', $Status->orders_status_id)
		->where('configuration_key = ?', 'DEFAULT_ORDERS_STATUS_ID')
		->execute();
}

EventManager::attachActionResponse(array(
	'success' => true
), 'json');
