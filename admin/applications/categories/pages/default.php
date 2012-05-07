<?php
$Qcategories = Doctrine_Query::create()
	->from('Categories c')
	->leftJoin('c.CategoriesDescription cd')
	->where('cd.language_id = ?', Session::get('languages_id'))
	->andWhere('c.parent_id = ?', $current_category_id)
	->orderBy('c.sort_order, cd.categories_name');

EventManager::notify('CategoryListingQueryBeforeExecute', $Qcategories);

$CategoriesGrid = htmlBase::newElement('newGrid')
	->useSearching(true)
	->useSorting(true)
	->usePagination(true)
	->setMainDataKey('category_id')
	->allowMultipleRowSelect(true)
	->setQuery($Qcategories);

$CategoriesGrid->addButtons(array(
	htmlBase::newElement('button')->usePreset('new')->addClass('newButton'),
	htmlBase::newElement('button')->usePreset('new')->addClass('newChildButton')
		->setText(sysLanguage::get('TEXT_BUTTON_NEW_CHILD_CATEGORY'))->disable(),
	htmlBase::newElement('button')->usePreset('edit')->addClass('editButton')->disable(),
	htmlBase::newElement('button')->usePreset('delete')->addClass('deleteButton')->disable()
));

$CategoriesGrid->addHeaderRow(array(
	'columns' => array(
		array(
			'text'      => sysLanguage::get('TABLE_HEADING_CATEGORIES'),
			'useSort'   => true,
			'sortKey'   => 'cd.categories_name',
			'useSearch' => true,
			'searchObj' => GridSearchObj::Like()
				->useFieldObj(htmlBase::newElement('input')->setName('search_categories_name'))
				->setDatabaseColumn('cd.categories_name')
		),
		array('text'    => sysLanguage::get('TABLE_HEADING_CATEGORIES_MENU'))
	)
));

$Categories = &$CategoriesGrid->getResults();
if ($Categories){
	$folderIcon = htmlBase::newElement('icon')->setType('folderClosed');
	foreach($Categories as $Category){
		$CategoriesGrid->addBodyRow(array(
			'rowAttr' => array(
				'data-category_id' => $Category['categories_id']
			),
			'columns' => array(
				array('text' => $folderIcon->draw() . '<span class="categoryListing-name">' . $Category['CategoriesDescription'][Session::get('languages_id')]['categories_name'] . '</span>'),
				array('text' => ucfirst($Category['categories_menu']))
			)
		));
	}
}
?>
<div class="ui-widget ui-widget-content ui-corner-all" style="margin-right:5px;margin-left:5px;">
	<div style="margin:5px;">
		<?php echo $CategoriesGrid->draw();?>
	</div>
</div>
