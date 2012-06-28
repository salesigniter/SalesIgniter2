<?php
$Product = new Product(
	(isset($_GET['product_id']) && empty($_POST) ? $_GET['product_id'] : ''),
	true
);
if (!isset($_GET['product_id']) && isset($_GET['productType'])){
	$Product->setProductType($_GET['productType']);
}

$tax_class_array = array(
	array(
		'id'   => '0',
		'text' => sysLanguage::get('TEXT_NONE')
	)
);
$QtaxClass = Doctrine_Manager::getInstance()
->getCurrentConnection()
->fetchAssoc("select tax_class_id, tax_class_title from tax_class order by tax_class_title");
foreach($QtaxClass as $tax_class){
	$tax_class_array[] = array(
		'id'   => $tax_class['tax_class_id'],
		'text' => $tax_class['tax_class_title']
	);
}

if ($Product->isActive() === true){
	$in_status = true;
	$out_status = false;
}
else {
	$in_status = false;
	$out_status = true;
}

if ($Product->isFeatured() === true){
	$non_featured = false;
	$featured = true;
}
else {
	$featured = false;
	$non_featured = true;
}

//------------------------- BOX set begin block -----------------------------//
$box_id = false;
$disc_label = 1;
if ($Product->getId() > 0){
	/*if ($Product->isInBox()){
		$box_query = tep_db_query("select box_id, disc from " . TABLE_PRODUCTS_TO_BOX . " where products_id=" . $Product->getId());
		$box = tep_db_fetch_array($box_query);
		$box_id = $box['box_id'];
		$disc_label = $box['disc'];
	}

	$boxes_query = tep_db_query("select p.products_id, pd.products_name from " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON p.products_id = pd.products_id AND pd.language_id='" . (int)Session::get('languages_id') . "' AND p.products_in_box=0 AND p.products_status=1 AND p.products_id<>" . $Product->getId());
	while($boxes = tep_db_fetch_array($boxes_query))
	{
		$boxes_array[] = array('id' => $boxes['products_id'],
			'text' => $boxes['products_name']);
	}*/
}

$is_box_array = array();
$is_box_array[] = array(
	'id'   => 0,
	'text' => 'No'
);
$is_box_array[] = array(
	'id'   => 1,
	'text' => 'Yes'
);
//------------------------- BOX set end block -----------------------------//

?>
<script language="javascript">
	var tax_rates = new Array();
	<?php
	for($i = 0, $n = sizeof($tax_class_array); $i < $n; $i++){
		if ($tax_class_array[$i]['id'] > 0){
			echo 'tax_rates["' . $tax_class_array[$i]['id'] . '"] = ' . tep_get_tax_rate_value($tax_class_array[$i]['id']) . ';' . "\n";
		}
	}
	?>
</script>
<?php
$ProductType = $Product->getProductTypeClass();
$adminTabs = array(
	'admin/applications/products/pages_tabs/tab_general.php'     => sysLanguage::get('TAB_GENERAL'),
	'admin/applications/products/pages_tabs/tab_images.php'      => sysLanguage::get('TAB_IMAGES'),
	'admin/applications/products/pages_tabs/tab_description.php' => sysLanguage::get('TAB_DESCRIPTION'),
	'admin/applications/products/pages_tabs/tab_categories.php'  => sysLanguage::get('TAB_CATEGORIES')
);
EventManager::notify('NewProductAddDefaultTabs', $Product, $ProductType, $adminTabs);

$Tabs = htmlBase::newElement('tabs')
->setId('tab_container');

/*
 * This handles the replacement of the original default tabs with tabs stored with the product type module
 */
foreach($adminTabs as $k => $v){
	ob_start();
	if (file_exists($ProductType->getPath() . $k)){
		require($ProductType->getPath() . $k);
	}
	else {
		require(sysConfig::getDirFsCatalog() . $k);
	}
	$TabContent = ob_get_contents();
	ob_end_clean();

	$Tabs
	->addTabHeader(basename($k, '.php'), array('text' => $v))
	->addTabPage(basename($k, '.php'), array('text' => $TabContent));
}

/*
 * This handles adding tabs from the product type module
 */
if (file_exists($ProductType->getPath() . 'admin/applications/products/pages/new_product.php')){
	require($ProductType->getPath() . 'admin/applications/products/pages/new_product.php');

	$className = 'ProductType' . ucfirst($ProductType->getCode()) . '_admin_products_new_product';
	$PageTabs = new $className;
	$PageTabs->AddPageTabs($Product, $Tabs);
}

EventManager::notify('NewProductAddTabs', $Product, $ProductType, $Tabs);

echo $Tabs->draw();

if (Session::exists('categories_cancel_link') === true){
	echo tep_draw_hidden_field('categories_save_redirect', Session::get('categories_save_redirect'));
}
?>
<input type="hidden" name="products_type" value="<?php echo $ProductType->getCode();?>">
