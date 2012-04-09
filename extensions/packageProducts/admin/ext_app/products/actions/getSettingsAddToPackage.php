<?php
$PackageProduct = new Product($_GET['product_id']);

$Fields = array(
	array(
		'label' => 'Product Name',
		'field' => $PackageProduct->getName()
	),
	array(
		'label' => 'Quantity In Package',
		'field' => '<input type="text" size="6" name="settings[' . $PackageProduct->getId() . '][quantity]" value="1">'
	),
	array(
		'label' => 'Product Type',
		'field' => $PackageProduct->getProductType()
	)
);

$moduleFile = sysConfig::getDirFsCatalog() . 'extensions/packageProducts/productTypeModules/package/modules/productType/' . $PackageProduct->getProductType() . '.php';
if (file_exists($moduleFile)){
	require($moduleFile);
	$className = 'PackageProductType' . ucfirst($PackageProduct->getProductType());
	foreach($className::getSettingsAddToPackage($PackageProduct) as $fInfo){
		$Fields[] = array(
			'label' => $fInfo['label'],
			'field' => $fInfo['field']
		);
	}
}

EventManager::attachActionResponse(array(
	'success' => true,
	'fields' => $Fields
), 'json');