<?php
	if (isset($_GET['products_id'])){
		$Products = Doctrine_Core::getTable('Products');
		$Product = $Products->find((int) $_GET['products_id']);
		if ($Product){
			$Product->delete();
		}
		
		$messageStack->addSession('pageStack', 'Product has been removed', 'success');
	}

	EventManager::attachActionResponse(array(
		'success' => true
	), 'json');
?>