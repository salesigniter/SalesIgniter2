<?php
$Products = Doctrine_Query::create()
	->from('Products p')
	->leftJoin('p.ProductsDescription pd')
	->where('pd.language_id = ?', Session::get('languages_id'))
	->andWhere('p.products_type != ?', 'package')
	->orderBy('pd.products_name');
if (isset($_GET['pID'])){
	$Products->andWhere('p.products_id != ?', (int) $_GET['pID']);
}
$ResultSet = $Products->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

$ProductSelect = htmlBase::newElement('selectbox')
	->attr('multiple', 'true')
	->setSize(25)
	->css('width', '100%')
	->setId('packageProductSelect');

foreach($ResultSet as $pInfo){
	$ProductSelect->addOption($pInfo['products_id'], $pInfo['ProductsDescription'][0]['products_name']);
}

$PackageTable = htmlBase::newElement('table')
	->setCellPadding(3)
	->setCellSpacing(0)
	->css('width', '100%');

$PackageTable->addBodyRow(array(
	'columns' => array(
		array('text' => $ProductSelect->draw())
	)
));
$PackageTable->addBodyRow(array(
	'columns' => array(
		array('text' => htmlBase::newElement('button')
			->css('width', '100%')
			->addClass('buttonAddToPackage')
			->setText('Add Selected Product To Package')
			->draw())
	)
));

$ProductsGrid = htmlBase::newElement('newGrid')
	->addClass('PackagedProductsGrid');

$ProductsGrid->addButtons(array(
	htmlBase::newElement('button')->addClass('deleteButton')->usePreset('delete')->disable(),
	htmlBase::newElement('button')->addClass('editProductButton')->usePreset('edit')->setText('Edit Product')->disable()
));

$GridHeaders = array(
	array('id' => 'product_name', 'text' => 'Products Name'),
	array('id' => 'product_name', 'text' => 'Quantity In Package'),
	array('id' => 'product_type', 'text' => 'Products Type'),
	array('id' => 'product_price', 'text' => 'Products Price Each')
);

$ProductTypeModules = array();
$Dir = new DirectoryIterator(sysConfig::getDirFsCatalog() . 'extensions/packageProducts/productTypeModules/package/modules/productType/');
foreach($Dir as $File){
	if ($File->isDot() || $File->isDir()){
		continue;
	}
	require($File->getPathName());
	$className = 'PackageProductType' . ucfirst($File->getBasename('.php'));

	$ProductTypeModules[] = $className;

	foreach($className::getPackagedTableHeaders() as $hInfo){
		$GridHeaders[] = $hInfo;
	}
}

$ProductsGrid->addHeaderRow(array(
	'columns' => $GridHeaders
));

foreach($Product->getProductTypeClass()->getProducts() as $pInfo){
	$BodyCols = array(
		array('text' => $pInfo['name']),
		array('text' => $pInfo['quantity']),
		array('text' => $pInfo['type']),
		array('text' => $pInfo['price'])
	);

	foreach($ProductTypeModules as $ProductTypeClassName){
		foreach($ProductTypeClassName::getPackagedTableBody($pInfo) as $bInfo){
			$BodyCols[] = $bInfo;
		}
	}

	$ProductsGrid->addBodyRow(array(
		'rowAttr' => array(
			'data-product_id' => $pInfo['id']
		),
		'columns' => $BodyCols
	));
}

$PackageTable->addBodyRow(array(
	'columns' => array(
		array('text' => $ProductsGrid->draw())
	)
));

echo $PackageTable->draw();