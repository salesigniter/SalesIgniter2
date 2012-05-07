<?php
$success = false;
$toDelete = explode(',', $_GET['product_id']);
$Products = Doctrine_Core::getTable('Products');
foreach($toDelete as $pId){
	$Product = $Products->find((int)$pId);
	if ($Product){
		$Product->delete();
		$success = true;
	}
}

EventManager::attachActionResponse(array(
	'success' => $success
), 'json');
