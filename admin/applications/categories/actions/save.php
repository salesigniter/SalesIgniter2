<?php
$Categories = Doctrine_Core::getTable('Categories');
if (isset($_GET['category_id'])){
	$Category = $Categories->findOneByCategoriesId((int)$_GET['category_id']);
	$categoryId = $_GET['category_id'];
}
else {
	$Category = $Categories->create();
	if (isset($_GET['parent_id'])){
		$Category->parent_id = $_GET['parent_id'];
		$categoryId = $_GET['parent_id'];
	}
}

if (isset($_POST['parent_id']) && $_POST['parent_id'] > -1){
	$Category->parent_id = $_POST['parent_id'];
	$categoryId = $_POST['parent_id'];
}

$Category->sort_order = (int)$_POST['sort_order'];
$Category->categories_menu = $_POST['categories_menu'];
$Category->categories_image = $_POST['categories_image'];

$CategoriesDescription =& $Category->CategoriesDescription;
foreach(sysLanguage::getLanguages() as $lInfo){
	$lID = $lInfo['id'];

	$CategoriesDescription[$lID]->language_id = $lID;
	$CategoriesDescription[$lID]->categories_name = $_POST['categories_name'][$lID];
	$CategoriesDescription[$lID]->categories_description = $_POST['categories_description'][$lID];

	$CategoriesDescription[$lID]->categories_seo_url = $_POST['categories_seo_url'][$lID];
}

EventManager::notify('CategoriesDescriptionsBeforeSave', $CategoriesDescription);

$Category->save();

EventManager::attachActionResponse(itw_app_link(tep_get_all_get_params(array('action', 'category_id')) . 'category_id=' . $Category->categories_id, null, 'default'), 'redirect');
?>
