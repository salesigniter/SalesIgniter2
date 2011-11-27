<?php
	$jsonData = array();

	$QproductName = Doctrine_Query::create()
	->from('ProductsDescription')
	->where('products_name LIKE ?', $_GET['term'] . '%')
	->andWhere('language_id = ?', Session::get('languages_id'))
	->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
	if ($QproductName){
		foreach($QproductName as $pInfo){  //here should be checked for inventory by purchaseType...if it has it should show the product
			$jsonData[] = array(
				'value' => $pInfo['products_id'],
				'label' => $pInfo['products_name']
			);
		}
	}
	
	EventManager::attachActionResponse($jsonData, 'json');
?>