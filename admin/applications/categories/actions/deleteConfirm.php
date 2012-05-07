<?php
$success = false;
$toDelete = explode(',', $_GET['category_id']);
$Categories = Doctrine_Core::getTable('Categories');
foreach($toDelete as $cId){
	$Category = $Categories->find($cId);
	if ($Category){
		$Category->delete();
		$success = true;
	}
}

EventManager::attachActionResponse(array(
	'success' => $success
), 'json');
