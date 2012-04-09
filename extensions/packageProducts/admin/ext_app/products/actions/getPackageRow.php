<?php
$NewRows = array();

foreach($_POST['settings'] as $ProductId => $sInfo){
	$PackageProduct = new Product($ProductId);

	$NewRow = array(
		'columns' => array(
			array('text' => '<input type="hidden" name="package_product[]" value="' . $ProductId . '">' . $PackageProduct->getName()),
			array('text' => '<input type="hidden" name="package_product_settings[' . $ProductId . '][quantity]" value="' . $sInfo['quantity'] . '">' . $sInfo['quantity']),
			array('text' => $PackageProduct->getProductType())
		)
	);

	$moduleFile = sysConfig::getDirFsCatalog() . 'extensions/packageProducts/productTypeModules/package/modules/productType/' . $PackageProduct->getProductType() . '.php';
	if (file_exists($moduleFile)){
		$className = 'PackageProductType' . ucfirst($PackageProduct->getProductType());
		if (!class_exists($className)){
			require($moduleFile);
		}

		$className::addPackageRowData($PackageProduct, $sInfo, &$NewRow);
	}

	$NewRows[] = $NewRow;
}

EventManager::attachActionResponse(array(
	'success' => true,
	'newRows' => $NewRows
), 'json');