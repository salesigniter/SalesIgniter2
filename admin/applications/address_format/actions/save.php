<?php
$formatId = (isset($_GET['format_id']) ? (int)$_GET['format_id'] : false);
$address_format = $_POST['address_format'];
$address_summary = $_POST['address_summary'];

$addressFormat = Doctrine_Core::getTable('AddressFormat');
if ($formatId !== false){
	$addressFormat = $addressFormat->find($formatId);
}
else {
	$addressFormat = new AddressFormat();
}

$addressFormat->address_format = $address_format;
$addressFormat->address_summary = $address_summary;
$addressFormat->save();

EventManager::attachActionResponse(array(
	'success' => true
), 'json');
