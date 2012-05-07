<?php
$success = false;
$toDelete = explode(',', $_GET['country_id']);
$Countries = Doctrine_Core::getTable('Countries');
foreach($toDelete as $countryId){
	$Country = $Countries->find((int)$countryId);
	if ($Country){
		$Country->delete();
		$success = true;
	}
}

EventManager::attachActionResponse(array(
	'success' => $success
), 'json');
