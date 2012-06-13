<?php
$jsonData = array();

$SearchTerm = $_GET['term'];
$QSearch = Doctrine_Query::create()
	->from('Products p')
	->leftJoin('p.ProductsDescription pd')
	->leftJoin('p.ProductsInventory pi')
	->leftJoin('pi.ProductsInventoryBarcodes pib')
	->where('pd.language_id = ?', Session::get('languages_id'))
	->andWhere('(pib.barcode LIKE ? OR pd.products_name LIKE ?)', array(
		$SearchTerm . '%',
		$SearchTerm . '%'
	));

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