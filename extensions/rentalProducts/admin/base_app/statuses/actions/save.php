<?php
$Ratio = $_POST['ratio'];
$Statuses = Doctrine_Core::getTable('RentalAvailability');

if (isset($_GET['sID'])){
	$Status = $Statuses->find((int)$_GET['sID']);
}else{
	$Status = $Statuses->create();
}

$Description = $Status->RentalAvailabilityDescription;
foreach($_POST['name'] as $langId => $Name){
	$Description[$langId]->language_id = $langId;
	$Description[$langId]->name = $Name;
}

$Status->ratio = $Ratio;
$Status->save();

EventManager::attachActionResponse(array(
	'success' => true,
	'sID' => $Status->rental_availability_id
), 'json');
