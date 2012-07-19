<?php
$jsonData = array();

$SearchTerm = $_GET['term'];
$QSearch = Doctrine_Query::create()
	->from('Products p')
	->leftJoin('p.ProductsDescription pd')
	->where('pd.language_id = ?', Session::get('languages_id'))
	->andWhere('pd.products_name LIKE ?', $SearchTerm . '%');

$Results = $QSearch->execute();

if ($Results){
	foreach($Results as $Result){
		$jsonData[] = array(
			'value' => $Result->products_id,
			'label' => $Result->ProductsDescription[Session::get('languages_id')]->products_name
		);
	}
}

EventManager::attachActionResponse($jsonData, 'json');
?>