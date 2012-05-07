<?php
$success = false;
$toDelete = explode(',', $_GET['admin_id']);
$AdminTable = Doctrine_Core::getTable('Admin');
foreach($toDelete as $adminId){
	$Admin = $AdminTable->find($adminId);
	if ($Admin){
		$Admin->delete();
		$success = true;
	}
}

EventManager::attachActionResponse(array(
	'success' => $success
), 'json');
?>