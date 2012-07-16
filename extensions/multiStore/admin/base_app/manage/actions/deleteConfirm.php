<?php
/*
	Multi Stores Extension Version 1
	
	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

$response = array(
	'success' => true
);

$Stores = Doctrine_Core::getTable('Stores');
$toDelete = explode(',', $_GET['store_id']);
foreach($toDelete as $storeId){
	$Store = $Stores->find($storeId);
	if ($Store){
		if ($Store->delete() === false){
			$response['success'] = false;
			$response['errorMessage'] = 'There was an error removing the store: ' . $Store->stores_name;
			break;
		}
	}else{
		$response['success'] = false;
		$response['errorMessage'] = 'Unable to remove store: Store Not Found!';
		break;
	}
}

EventManager::attachActionResponse($response, 'json');
