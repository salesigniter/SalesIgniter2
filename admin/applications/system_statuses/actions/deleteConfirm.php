<?php
$success = false;
$toDelete = explode(',', $_GET['status_id']);
$OrdersStatus = Doctrine_Core::getTable('SystemStatuses');
foreach($toDelete as $statusId){
	$Status = $OrdersStatus->find((int)$statusId);
	if ($Status){
		$Status->delete();
		$success = true;
	}
}

EventManager::attachActionResponse(array(
	'success' => $success,
), 'json');
