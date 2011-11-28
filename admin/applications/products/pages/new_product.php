<?php
$Product = new Product(
	(isset($_GET['pID']) && empty($_POST) ? $_GET['pID'] : ''),
	true
);
if (!isset($_GET['pID']) && isset($_GET['productType'])){
	$Product->setProductType($_GET['productType']);
}

$manufacturers_array = array(array('id' => '', 'text' => sysLanguage::get('TEXT_NONE')));
$manufacturers_query = tep_db_query("select manufacturers_id, manufacturers_name from " . TABLE_MANUFACTURERS . " order by manufacturers_name");
while($manufacturers = tep_db_fetch_array($manufacturers_query)){
	$manufacturers_array[] = array('id' => $manufacturers['manufacturers_id'],
		'text' => $manufacturers['manufacturers_name']);
}

$tax_class_array = array(array('id' => '0', 'text' => sysLanguage::get('TEXT_NONE')));
$tax_class_query = tep_db_query("select tax_class_id, tax_class_title from " . TABLE_TAX_CLASS . " order by tax_class_title");
while($tax_class = tep_db_fetch_array($tax_class_query)){
	$tax_class_array[] = array('id' => $tax_class['tax_class_id'],
		'text' => $tax_class['tax_class_title']);
}

$languages = tep_get_languages();

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
	if ($Product->isInBox()){
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
	}
}

$is_box_array = array();
$is_box_array[] = array('id' => 0, 'text' => 'No');
$is_box_array[] = array('id' => 1, 'text' => 'Yes');
//------------------------- BOX set end block -----------------------------//

$ajaxSaveButton = htmlBase::newElement('button')->setType('submit')->usePreset('save')->addClass('ajaxSave')
	->setText(sysLanguage::get('TEXT_BUTTON_AJAX_SAVE'));
$saveButton = htmlBase::newElement('button')->setType('submit')->usePreset('save')
	->setText(sysLanguage::get('TEXT_BUTTON_SAVE'));
$cancelButton = htmlBase::newElement('button')->usePreset('cancel');

if (Session::exists('categories_cancel_link') === true){
	$cancelButton->setHref(Session::get('categories_cancel_link'));
}
else {
	$cancelButton->setHref(itw_app_link((isset($_GET['pID']) ? 'pID=' . $_GET['pID'] : ''), null, 'default'));
}
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
	'admin/applications/products/pages_tabs/tab_general.php' => sysLanguage::get('TAB_GENERAL'),
	'admin/applications/products/pages_tabs/tab_images.php' => sysLanguage::get('TAB_IMAGES'),
	'admin/applications/products/pages_tabs/tab_description.php' => sysLanguage::get('TAB_DESCRIPTION'),
	'admin/applications/products/pages_tabs/tab_categories.php' => sysLanguage::get('TAB_CATEGORIES')
);
EventManager::notify('NewProductAddDefaultTabs', $Product, $ProductType, &$adminTabs);

$Tabs = htmlBase::newElement('tabs')
	->setId('tab_container');

foreach($adminTabs as $k => $v){
	ob_start();
	if (file_exists($ProductType->getPath() . 'admin/applications/products/pages_tabs/' . basename($k))){
		require($ProductType->getPath() . 'admin/applications/products/pages_tabs/' . basename($k));
	}else{
		require(sysConfig::getDirFsCatalog() . $k);
	}
	$TabContent = ob_get_contents();
	ob_end_clean();

	$Tabs->addTabHeader(basename($k, '.php'), array('text' => $v))
		->addTabPage(basename($k, '.php'), array('text' => $TabContent));
}

if (file_exists(sysConfig::getDirFsCatalog() . 'includes/modules/productTypeModules/' . $ProductType->getCode() . '/admin/applications/products/pages/new_product.php')){
	require(sysConfig::getDirFsCatalog() . 'includes/modules/productTypeModules/' . $ProductType->getCode() . '/admin/applications/products/pages/new_product.php');

	$className = 'ProductType' . ucfirst($ProductType->getCode()) . '_admin_products_new_product';
	$PageTabs = new $className;
	$PageTabs->AddPageTabs($Product, $Tabs);
}

EventManager::notify('NewProductAddTabs', $Product, $ProductType, $Tabs);
?>
<input type="button" value="Turn On Upload Debugger" id="turnOnDebugger" /><br />
<form name="new_product" action="<?php echo itw_app_link(tep_get_all_get_params(array('action', 'pID')) . 'action=saveProduct' . ((int)$Product->getId() > 0
		? '&pID=' . $Product->getId() : ''));?>" method="post" enctype="multipart/form-data">
	<div style="position:relative;text-align:right;"><?php
	 echo $ajaxSaveButton->draw() . $saveButton->draw() . $cancelButton->draw();
		echo '<div class="pageHeading" style="position:absolute;left:0;top:.5em;">' . (isset($_GET['pID'])
			? 'Edit Product' : 'New Product') . '</div>';
		?></div>
	<br />
	<?php if (!isset($_GET['pID'])){ ?>
	<div class="ui-widget ui-widget-content ui-corner-all ui-state-warning newProductMessage" style="padding:.3em;font-weight:bold;">You are entering a new product. Some places are disabled, use the "Save Ajax" button to save this product and enable them</div>
	<br />
	<?php
}
	echo $Tabs->draw();
	?>
	<div style="position:relative;text-align:right;margin-top:.5em;margin-left:250px;"><?php
	if (Session::exists('categories_cancel_link') === true){
		echo tep_draw_hidden_field('categories_save_redirect', Session::get('categories_save_redirect'));
	}
		echo $ajaxSaveButton->draw() . $saveButton->draw() . $cancelButton->draw();
		?>
		<div class="smallText" style="text-align:left;width:315px;position:absolute;right:.5em;top:3em;">*Image upload fields do not work with ajax save<br>So you'll need to use the normal save button for uploads
		</div>
	</div>
</form>