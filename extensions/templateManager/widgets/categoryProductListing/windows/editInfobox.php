<?php
$CategoryId = (isset($WidgetSettings->category_id) ? $WidgetSettings->category_id : '');
$MaxProductsNum = (isset($WidgetSettings->max_products) ? $WidgetSettings->max_products : '10');
$WhenMaxVal = (isset($WidgetSettings->when_max_products) ? $WidgetSettings->when_max_products : 'show_sub');

$CategorySelect = htmlBase::newElement('selectbox')
	->setName('category_id')
	->selectOptionByValue($CategoryId);

$Qcategories = Doctrine_Query::create()
	->from('Categories c')
	->leftJoin('c.CategoriesDescription cd')
	->where('cd.language_id = ?', Session::get('languages_id'))
	->andWhere('c.parent_id = ?', 0)
	->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
$CategorySelect->addOption('', sysLanguage::get('TEXT_PLEASE_SELECT'));
foreach($Qcategories as $cInfo){
	$CategorySelect->addOption(
		$cInfo['categories_id'],
		$cInfo['CategoriesDescription'][0]['categories_name']
	);
}

$MaxProducts = htmlBase::newElement('input')
	->setName('max_products')
	->val($MaxProductsNum);

$WhenMaxProducts = htmlBase::newElement('radio')
	->addGroup(array(
		'name' => 'when_max_products',
		'checked' => $WhenMaxVal,
		'labelPosition' => 'after',
		'separator' => '<br>',
		'data' => array(
			array('value' => 'show_sub', 'label' => 'Show Sub Categories'),
			array('value' => 'limit_products', 'label' => 'Show Products Up To Max')
		)
	));

$WidgetSettingsTable->addBodyRow(array(
	'columns' => array(
		array('text' => sysLanguage::get('TEXT_CATEGORYPRODUCTLISTING_SELECT_CATEGORY')),
		array('text' => $CategorySelect->draw() . sysLanguage::get('TEXT_CATEGORYPRODUCTLISTING_INFO'))
	)
));

$WidgetSettingsTable->addBodyRow(array(
	'columns' => array(
		array('text' => sysLanguage::get('TEXT_CATEGORYPRODUCTLISTING_MAX_PRODUCTS')),
		array('text' => $MaxProducts->draw() . sysLanguage::get('TEXT_CATEGORYPRODUCTLISTING_MAX_PRODUCTS_INFO'))
	)
));

$WidgetSettingsTable->addBodyRow(array(
	'columns' => array(
		array('text' => sysLanguage::get('TEXT_CATEGORYPRODUCTLISTING_WHEN_MAX')),
		array('text' => sysLanguage::get('TEXT_CATEGORYPRODUCTLISTING_WHEN_MAX_INFO') . '<br>' . $WhenMaxProducts->draw())
	)
));
?>