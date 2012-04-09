<?php
$Status = Doctrine_Core::getTable('RentalAvailability')->find((int) $_GET['sID']);
$success = false;
if ($Status){
	$Status->delete();
	$success = true;
}

EventManager::attachActionResponse(array(
	'success' => $success
), 'json');
