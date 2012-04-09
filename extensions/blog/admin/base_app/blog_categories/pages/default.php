<?php
function addCategoryTreeToGrid($parentId, &$tableGrid, $namePrefix = '') {
	global $lID, $allGetParams, $cInfo;
	$Qcategories = Doctrine_Query::create()
		->from('BlogCategories c')
		->leftJoin('c.BlogCategoriesDescription cd')
		->where('cd.language_id = ?', $lID)
		->andWhere('c.parent_id = ?', $parentId)
		->orderBy('c.sort_order, cd.blog_categories_title');

	if (isset($_GET['key'])){
		$Qcategories->andWhere('c.extra_key = ?', $_GET['key']);
	}

	EventManager::notify('BlogCategoryListingQueryBeforeExecute', &$Qcategories);

	$Result = $Qcategories->execute();
	if ($Result->count() > 0){
		foreach($Result->toArray(true) as $Category){
			if ($Category['parent_id'] > 0){
				//$namePrefix .= '&nbsp;';
			}

			$infoBoxSettings = array(
				'categoryId'		   => $Category['blog_categories_id'],
				'categoryImage'		=> $Category['categories_image'],
				'categoryName'		 => $Category['BlogCategoriesDescription'][Session::get('languages_id')]['blog_categories_title'],
				'categoryChildren'	 => tep_childs_in_blog_category_count($Category['blog_categories_id'])
			);

			if ((isset($_GET['cID']) && $_GET['cID'] == $Category['blog_categories_id'])){
				$cInfo = $infoBoxSettings;
			}

			// Get parent_id for subcategories if search
			if (isset($_GET['search'])) {
				$cPath = $Category['parent_id'];
			}

			$category_childs = array('childs_count' => $infoBoxSettings['categoryChildren']);

			$folderIcon = htmlBase::newElement('icon')->setType('folderClosed');

			$tableGrid->addBodyRow(array(
				'addCls'  => ($parentId > 0 ? 'child-of-node-' . $parentId : ''),
				'rowAttr' => array(
					'id'               => 'node-' . $infoBoxSettings['categoryId'],
					'data-category_id' => $Category['blog_categories_id']
				),
				'columns' => array(
					array('text' => $namePrefix . $folderIcon->draw() . '<span class="categoryListing-name">' . $infoBoxSettings['categoryName'] . '</span>')
				)
			));

			addCategoryTreeToGrid($Category['blog_categories_id'], &$tableGrid, '&nbsp;&nbsp;&nbsp;' . $namePrefix);
		}
	}
}

$categories_count = 0;
$rows = 0;
$lID = (int)Session::get('languages_id');

$tableGrid = htmlBase::newElement('newGrid');

$tableGrid->addButtons(array(
	htmlBase::newElement('button')->addClass('newButton')->usePreset('install')->setText('New Category'),
	htmlBase::newElement('button')->addClass('editButton')->usePreset('edit')->disable(),
	htmlBase::newElement('button')->addClass('deleteButton')->usePreset('delete')->disable()
));

$tableGrid->addHeaderRow(array(
	'columns' => array(
		array('text' => sysLanguage::get('TABLE_HEADING_CATEGORIES'))
	)
));

$allGetParams = tep_get_all_get_params(array('cID', 'action'));

$infoBoxes = array();
addCategoryTreeToGrid(0, $tableGrid);
?>
<div class="pageHeading"><?php echo sysLanguage::get('HEADING_TITLE');?></div>
<br />
<div class="ui-widget ui-widget-content ui-corner-all" style=";margin-right:5px;margin-left:5px;">
	<div style="margin:5px;">
		<?php echo $tableGrid->draw();?>
	</div>
</div>
