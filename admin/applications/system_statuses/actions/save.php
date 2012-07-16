<?php
$SystemStatuses = Doctrine_Core::getTable('SystemStatuses');
if (isset($_GET['status_id'])){
	$Status = $SystemStatuses->find((int)$_GET['status_id']);
}
else {
	$Status = $SystemStatuses->getRecord();
}

$Status->status_types = implode(',', $_POST['status_types']);

$Description = $Status->Description;
foreach($_POST['status_name'] as $langId => $statusName){
	$Description[$langId]->language_id = $langId;
	$Description[$langId]->status_name = $statusName;
}

$Status->save();

EventManager::attachActionResponse(array(
	'success' => true
), 'json');
