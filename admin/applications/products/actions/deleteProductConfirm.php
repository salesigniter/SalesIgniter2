<?php
	if (isset($_GET['products_id'])){
		$Products = Doctrine_Core::getTable('Products');
		foreach($_GET['products_id'] as $pId){
			$Product = $Products->find((int) $pId);
			if ($Product){
				$Product->delete();
			}
		}
	}

	EventManager::attachActionResponse(array(
		'success' => true
	), 'json');
?>