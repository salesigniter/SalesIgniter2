<?php
$success = false;
$toDelete = explode(',', $_GET['format_id']);
$AddressFormats = Doctrine_Core::getTable('AddressFormat');
foreach($toDelete as $formatId){
	$AddressFormat = $AddressFormats->find($formatId);
	if ($AddressFormat){
		$AddressFormat->delete();
		$success = true;
	}
}

EventManager::attachActionResponse(array(
	'success' => $success
), 'json');
