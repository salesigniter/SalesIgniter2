<?php
$ExtMultiStore = $appExtension->getExtension('multiStore');
$postcodes = array();
foreach($ExtMultiStore->getStoresArray() as $sInfo){
	$postcodes[] = array(
		'id' => $sInfo['stores_id'],
		'address' => $sInfo['stores_street_address']
	);
}

EventManager::attachActionResponse(array(
		'success' => true,
		'postcodes' => $postcodes
	), 'json');