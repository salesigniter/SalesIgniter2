<?php
set_time_limit(0);
ProductTypeModules::loadModules();

require(sysConfig::getDirFsCatalog() . 'includes/classes/FileWriter/csv.php');
$ExportFile = new FileWriterCsv('temp');

$HeaderRow = $ExportFile->newHeaderRow();

$HeaderRow->addColumn('v_products_model');
$HeaderRow->addColumn('v_products_image');
$HeaderRow->addColumn('v_products_type');

foreach(ProductTypeModules::getModules() as $ProductTypeModule){
	if (method_exists($ProductTypeModule, 'addExportHeaderColumns')){
		$ProductTypeModule->addExportHeaderColumns(&$HeaderRow);
	}
}

$HeaderRow->addColumn('v_products_in_box');
$HeaderRow->addColumn('v_products_featured');
$HeaderRow->addColumn('v_products_weight');
$HeaderRow->addColumn('v_date_avail');
$HeaderRow->addColumn('v_memberships_not_enabled');
$HeaderRow->addColumn('v_products_categories');

foreach(sysLanguage::getLanguages() as $lInfo){
	$lID = $lInfo['id'];

	$HeaderRow->addColumn('v_products_name_' . $lID);
	$HeaderRow->addColumn('v_products_description_' . $lID);
	$HeaderRow->addColumn('v_products_url_' . $lID);
	$HeaderRow->addColumn('v_products_head_title_tag_' . $lID);
	$HeaderRow->addColumn('v_products_head_desc_tag_' . $lID);
	$HeaderRow->addColumn('v_products_head_keywords_tag_' . $lID);
}

$HeaderRow->addColumn('v_tax_class_title');
$HeaderRow->addColumn('v_status');

EventManager::notify('DataExportFullQueryFileLayoutHeader', &$HeaderRow);

foreach(PurchaseTypeModules::getModules() as $PurchaseTypeModule){
	$HeaderRow->addColumn('v_autogenerate_barcodes_' . $PurchaseTypeModule->getCode());
}

$QfileLayout = Doctrine_Query::create()
	->select(
	'p.products_id, ' .
		'p.products_model as v_products_model, ' .
		'p.products_image as v_products_image, ' .
		'p.products_weight as v_products_weight, ' .
		'p.products_date_available as v_date_avail, ' .
		'p.products_tax_class_id as v_tax_class_id, ' .
		'p.products_type as v_products_type, ' .
		'p.products_in_box as v_products_in_box, ' .
		'p.products_featured as v_products_featured, ' .
		'p.products_status as v_status, ' .
		'p.membership_enabled as v_memberships_not_enabled, ' .
		'(SELECT group_concat(p2c.categories_id) FROM ProductsToCategories p2c WHERE p2c.products_id = p.products_id) as v_products_categories'
)->from('Products p')
	->where('p.products_model is not null')
	->andWhere('p.products_model != ?', '');

foreach(ProductTypeModules::getModules() as $ProductTypeModule){
	if (method_exists($ProductTypeModule, 'addExportQueryConditions')){
		$ProductTypeModule->addExportQueryConditions($QfileLayout);
	}
}

EventManager::notify('DataExportFullQueryBeforeExecute', &$QfileLayout);

$Result = $QfileLayout->execute(array(), Doctrine_Core::HYDRATE_SCALAR);

$p = -1;
foreach($Result as $pInfo){
	foreach($pInfo as $k => $v){
		$pInfo[substr($k, strpos($k, '_')+1)] = $v;
		unset($pInfo[$k]);
	}

	$p++;
	if (isset($_POST['start_num']) && (!empty($_POST['start_num']) || $_POST['start_num'] == 0)){
		if ($p < $_POST['start_num']) continue;
	}
	if (isset($_POST['num_items']) && !empty($_POST['num_items'])){
		if ($p >= ((int)$_POST['start_num'] + $_POST['num_items'])) break;
	}

	$CurrentRow = $ExportFile->newRow();
	$CurrentRow->addColumn($pInfo['v_products_model'], 'v_products_model');
	$CurrentRow->addColumn($pInfo['v_products_image'], 'v_products_image');
	$CurrentRow->addColumn($pInfo['v_products_weight'], 'v_products_weight');
	$CurrentRow->addColumn($pInfo['v_products_date_available'], 'v_date_avail');
	$CurrentRow->addColumn($pInfo['v_products_tax_class_id'], 'v_tax_class_id');
	$CurrentRow->addColumn($pInfo['v_products_type'], 'v_products_type');
	$CurrentRow->addColumn($pInfo['v_products_in_box'], 'v_products_in_box');
	$CurrentRow->addColumn($pInfo['v_products_featured'], 'v_products_featured');
	$CurrentRow->addColumn($pInfo['v_products_status'], 'v_status');

	foreach(sysLanguage::getLanguages() as $lInfo){
		$lID = $lInfo['id'];

		$Qdescription = Doctrine_Query::create()
			->from('ProductsDescription pd')
			->where('products_id = ?', $pInfo['products_id'])
			->andWhere('language_id = ?', $lID)
			->execute()->toArray();
		if (isset($Qdescription[$lID])){
			$CurrentRow->addColumn($Qdescription[$lID]['products_name'], 'v_products_name_' . $lID);
			$CurrentRow->addColumn($Qdescription[$lID]['products_description'], 'v_products_description_' . $lID);
			$CurrentRow->addColumn($Qdescription[$lID]['products_url'], 'v_products_url_' . $lID);
			$CurrentRow->addColumn($Qdescription[$lID]['products_head_title_tag'], 'v_products_head_title_tag_' . $lID);
			$CurrentRow->addColumn($Qdescription[$lID]['products_head_desc_tag'], 'v_products_head_desc_tag_' . $lID);
			$CurrentRow->addColumn($Qdescription[$lID]['products_head_keywords_tag'], 'v_products_head_keywords_tag_' . $lID);
		}
	}

	$categories = explode(',', $pInfo['v_products_categories']);
	$catPaths = array();
	foreach($categories as $categoryId){
		$currentParent = $categoryId;
		$catPath = array();
		while($currentParent > 0){
			$Qcategory = Doctrine_Query::create()
				->select('c.categories_id, c.parent_id, cd.categories_name')
				->from('Categories c')
				->leftJoin('c.CategoriesDescription cd')
				->where('c.categories_id = ?', $currentParent)
				->andWhere('cd.language_id = ?', Session::get('languages_id'))
				->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

			$catPath[] = trim($Qcategory[0]['CategoriesDescription'][0]['categories_name']);
			$currentParent = $Qcategory[0]['parent_id'];
		}
		$catPaths[] = implode('>', array_reverse($catPath));
	}
	$CurrentRow->addColumn(implode(';', $catPaths), 'v_products_categories');

	$nmembershipsString = '';
	if ($pInfo['v_memberships_not_enabled'] != ''){
		$notEnabledMemberships = explode(';',$pInfo['v_memberships_not_enabled']);
		$Qmembership = Doctrine_Query::create()
			->from('Membership m')
			->leftJoin('m.MembershipPlanDescription md')
			->where('md.language_id = ?', Session::get('languages_id'))
			->orderBy('sort_order')
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
		foreach($Qmembership as $mInfo){
			if(in_array($mInfo['plan_id'], $notEnabledMemberships)){
				$nmembershipsString.= $mInfo['MembershipPlanDescription'][0]['name'].';';
			}
		}
		$nmembershipsString = substr($nmembershipsString,0,strlen($nmembershipsString)-1);
	}

	foreach(ProductTypeModules::getModules() as $ProductTypeModule){
		if (method_exists($ProductTypeModule, 'addExportRowColumns')){
			$ProductTypeModule->addExportRowColumns($CurrentRow, $pInfo);
		}
	}

	$CurrentRow->addColumn($nmembershipsString, 'v_memberships_not_enabled');
	$CurrentRow->addColumn(tep_get_tax_class_title($pInfo['v_tax_class_id']), 'v_tax_class_title');
	$CurrentRow->addColumn(($pInfo['v_status'] == '1' ? $active : $inactive), 'v_status');

	EventManager::notify('DataExportBeforeFileLineCommit', $CurrentRow, $pInfo);

	foreach(PurchaseTypeModules::getModules() as $PurchaseTypeModule){
		$CurrentRow->addColumn(0, 'v_autogenerate_barcodes_' . $PurchaseTypeModule->getCode());
	}
}
//print_r($ExportFile);
$ExportFile->output();
?>