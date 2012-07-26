<?php
$Qproducts = Doctrine_Query::create()
	->from('Products p')
	->leftJoin('p.ProductsDescription pd')
	->leftJoin('p.ProductsToCategories p2c')
	->leftJoin('p2c.Categories c')
	->leftJoin('c.CategoriesDescription cd')
	->where('pd.language_id = ?', (int)Session::get('languages_id'))
	->andWhere('p.products_in_box = ?', '0');

if (isset($_GET['categorySelect']) && $_GET['categorySelect'] != -1){
	$Qproducts->andWhere('p2c.categories_id = ?', $_GET['categorySelect']);
}

EventManager::notify('AdminProductListingQueryBeforeExecute', $Qproducts);

$tableGrid = htmlBase::newElement('newGrid')
	->useSearching(true)
	->useSorting(true)
	->usePagination(true)
	->allowMultipleRowSelect(true)
	->setMainDataKey('product_id')
	->setQuery($Qproducts);

$gridButtons = array(
	htmlBase::newElement('button')->usePreset('new')->addClass('newButton'),
	htmlBase::newElement('button')->usePreset('edit')->addClass('editButton')->disable(),
	htmlBase::newElement('button')->usePreset('copy')->addClass('copyButton')->disable(),
	htmlBase::newElement('button')->usePreset('delete')->addClass('deleteButton')->disable(),
	htmlBase::newElement('button')->usePreset('edit')->setText('Manage Inventory')->setTooltip('Manage Inventory')
		->addClass('invButton')->disable()
);

$tableGrid->addButtons($gridButtons);

$searchForm = htmlBase::newElement('form')
	->attr('name', 'search')
	->attr('id', 'search')
	->attr('action', itw_app_link(null, null, null, 'SSL'))
	->attr('method', 'get');

$categorySelect = htmlBase::newElement('selectbox')
	->setName('categorySelect')
	->setLabel(sysLanguage::get('TEXT_SELECT_CATEGORY'))
	->setLabelPosition('before');

function addCategoryTreeToGrid($parentId, &$categorySelect, $namePrefix = '') {
	$Qcategories = Doctrine_Query::create()
		->select('c.*, cd.categories_name')
		->from('Categories c')
		->leftJoin('c.CategoriesDescription cd')
		->where('cd.language_id = ?', Session::get('languages_id'))
		->andWhere('c.parent_id = ?', $parentId)
		->orderBy('c.sort_order, cd.categories_name');

	EventManager::notify('CategoryListingQueryBeforeExecute', $Qcategories);

	$ResultC = $Qcategories->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
	if (count($ResultC) > 0){
		foreach($ResultC as $Category){
			$categorySelect->addOption($Category['categories_id'], $namePrefix . $Category['CategoriesDescription'][0]['categories_name']);
			addCategoryTreeToGrid($Category['categories_id'], &$categorySelect, '&nbsp;&nbsp;&nbsp;' . $namePrefix);
		}
	}
}

$categorySelect->addOption('-1', sysLanguage::get('TEXT_PLEASE_SELECT'));
addCategoryTreeToGrid(0, $categorySelect, '');

$header2 = array(
	array('text' => 'Set'),
	array(
		'text'      => sysLanguage::get('TABLE_HEADING_ID'),
		'useSort'   => true,
		'sortKey'   => 'p.products_id',
		'useSearch' => true,
		'searchObj' => GridSearchObj::Equal()
			->useFieldObj(htmlBase::newElement('input')->attr('size', 4)->setName('search_product_id'))
			->setDatabaseColumn('p.products_id')
	),
	array(
		'text'      => 'Categories',
		'useSort'   => true,
		'sortKey'   => 'p2c.categories_id',
		'useSearch' => true,
		'searchObj' => GridSearchObj::Like()
			->useFieldObj(htmlBase::newElement('input')->setName('search_category_name'))
			->setDatabaseColumn('cd.categories_name')
	),
	array('text' => 'Type'),
	array(
		'text'      => 'Name',
		'useSort'   => true,
		'sortKey'   => 'pd.products_name',
		'useSearch' => true,
		'searchObj' => GridSearchObj::Like()
			->useFieldObj(htmlBase::newElement('input')->setName('search_products_name'))
			->setDatabaseColumn('pd.products_name')
	),
	array(
		'text'      => 'Model',
		'useSort'   => true,
		'sortKey'   => 'p.products_model',
		'useSearch' => true,
		'searchObj' => GridSearchObj::Like()
			->useFieldObj(htmlBase::newElement('input')->setName('search_products_model'))
			->setDatabaseColumn('p.products_model')
	)
);
$header2[] = array('text' => sysLanguage::get('TABLE_HEADING_STATUS'));
$header2[] = array('text' => sysLanguage::get('TABLE_HEADING_FEATURED'));
$header2[] = array('text' => sysLanguage::get('TABLE_HEADING_INFO'));

$tableGrid->addHeaderRow(array('columns' => $header2));

$Products = &$tableGrid->getResults();
if ($Products){
	$allGetParams = tep_get_all_get_params(array('action', 'product_id', 'flag', 'fflag'));
	foreach($Products as $pInfo){
		$ProductClass = new Product((int)$pInfo['products_id']);

		$productId = $ProductClass->getID();
		$productModel = $ProductClass->getModel();
		$productName = $ProductClass->getName();

		$statusIcon = htmlBase::newElement('icon');
		if ($ProductClass->isActive() === true){
			$statusIcon->setType('circleCheck')->setTooltip('Click to disable')
				->setHref(itw_app_link($allGetParams . 'action=setflag&flag=0&product_id=' . $productId));
		}
		else {
			$statusIcon->setType('circleClose')->setTooltip('Click to enable')
				->setHref(itw_app_link($allGetParams . 'action=setflag&flag=1&product_id=' . $productId));
		}

		$featuredIcon = htmlBase::newElement('icon');
		if ($ProductClass->isFeatured() === true){
			$featuredIcon->setType('circleCheck')->setTooltip('Click to disable')
				->setHref(itw_app_link($allGetParams . 'action=setfflag&fflag=0&product_id=' . $productId));
		}
		else {
			$featuredIcon->setType('circleClose')->setTooltip('Click to enable')
				->setHref(itw_app_link($allGetParams . 'action=setfflag&fflag=1&product_id=' . $productId));
		}

		$nameAlignCenter = false;
		if (empty($productName)){
			$nameAlignCenter = true;
			$productName = htmlBase::newElement('icon')->setType('alert')->setTooltip('This product needs a name')
				->draw();
			$nameSpacing = '';
		}

		$modelAlignCenter = false;
		if (empty($productModel)){
			$modelAlignCenter = true;
			$productModel = htmlBase::newElement('icon')->setType('alert')
				->setTooltip('This product needs a model to work with data export/import')->draw();
		}

		$rowAttr = array(
			'data-product_id' => $productId
		);
		$nameSpacing = '';
		if ($ProductClass->isInBox() === true){
			$rowAttr['data-box_id'] = $ProductClass->getBoxID();
			$rowAttr['style'] = 'display:none';
			$nameSpacing = '&nbsp;|-&nbsp;';
		}

		$Categories = array();
		foreach($pInfo['ProductsToCategories'] as $cInfo){
			$Categories[] = $cInfo['Categories']['CategoriesDescription'][Session::get('languages_id')]['categories_name'];
		}

		$tableGridBody = array();
		$tableGridBody[] = array(
			'text'  => ($ProductClass->isBox() === true ? htmlBase::newElement('icon')
				->addClass('setExpander')->setType('triangleEast')->draw() : '&nbsp;'),
			'align' => 'center'
		);
		$tableGridBody[] = array(
			'text'   => $productId,
			'format' => 'int'
		);
		$tableGridBody[] = array(
			'text'   => implode(', ', $Categories),
			'format' => 'string'
		);
		$tableGridBody[] = array(
			'text'   => $ProductClass->getProductTypeClass()->getTitle(),
			'format' => 'string'
		);
		$tableGridBody[] = array(
			'text'  => $nameSpacing . $productName,
			'align' => ($nameAlignCenter === true ? 'center' : 'left')
		);
		$tableGridBody[] = array(
			'text'  => $productModel,
			'align' => ($modelAlignCenter === true ? 'center' : 'left')
		);

		$added = 0;
//		echo '<pre>';print_r(PurchaseTypeModules::getModules());
		foreach(PurchaseTypeModules::getModules() as $PurchaseType){
			if ($PurchaseType->getConfigData('SHOW_ON_ADMIN_PRODUCT_LIST') == 'True'){
				$PurchaseType->loadProduct($ProductClass->getId());
				$tableGridBody[] = array(
					'text'   => (int)$PurchaseType->getCurrentStock(),
					'align'  => 'center',
					'format' => 'int'
				);
				$added++;
			}
		}

		$tableGridBody[] = array(
			'text'  => $statusIcon->draw(),
			'align' => 'center'
		);
		$tableGridBody[] = array(
			'text'  => $featuredIcon->draw(),
			'align' => 'center'
		);
		$tableGridBody[] = array(
			'text'  => htmlBase::newElement('icon')->setType('info')->draw(),
			'align' => 'right'
		);

		$tableGrid->addBodyRow(array(
			'rowAttr' => $rowAttr,
			'columns' => $tableGridBody
		));

		$productImage = $ProductClass->getImage();
		if (!empty($productImage) && file_exists(sysConfig::getDirFsCatalog() . 'images/' . $productImage)){
			$imageHtml = htmlBase::newElement('image')
				->setSource('images/' . $productImage)
				->setWidth(sysConfig::get('SMALL_IMAGE_WIDTH'))
				->setHeight(sysConfig::get('SMALL_IMAGE_HEIGHT'))
				->thumbnailImage(true);
		}
		else {
			$imageHtml = htmlBase::newElement('span')
				->addClass('main')
				->html('Image Does Not Exist');
		}

		$tableGrid->addBodyRow(array(
			'addCls'  => 'gridInfoRow',
			'columns' => array(
				array(
					'colspan' => sizeof($tableGridBody),
					'text'    => '<table cellpadding="1" cellspacing="0" border="0" width="75%">' .
						'<tr>' .
						'<td valign="top" width="' . ((int)sysConfig::get('SMALL_IMAGE_WIDTH') + 10) . '">' . $imageHtml->draw() . '<br />' . $productImage . '</td>' .
						'<td valign="top"><table cellpadding="2" cellspacing="0" border="0">' .
						'<tr>' .
						'<td><b>' . sysLanguage::get('TEXT_DATE_ADDED') . '</b></td>' .
						'<td> ' . $ProductClass->getDateAdded()->format(sysLanguage::getDateFormat('long')) . '</td>' .
						'</tr>' .
						'<tr>' .
						'<td><b>' . sysLanguage::get('TEXT_LAST_MODIFIED') . '</b></td>' .
						'<td>' . $ProductClass->getLastModified()
						->format(sysLanguage::getDateFormat('long')) . '</td>' .
						'</tr>' .
						(time() < $ProductClass->getDateAvailable()->getTimestamp() ?
							'<tr>' .
								'<td><b>' . sysLanguage::get('TEXT_DATE_AVAILABLE') . '</b></td>' .
								'<td>' . $ProductClass->getDateAvailable()
								->format(sysLanguage::getDateFormat('long')) . '</td>' .
								'</tr>' : '') .
						'</table></td>' .
						'</tr>' .
						'</table>'
				)
			)
		));
	}
}

function addGridRow($productClass, &$tableGrid, &$infoBoxes) {
	global $allGetParams, $editButton, $copyButton, $deleteButton, $currencies;
	$infoBox->setForm(array(
		'name'   => 'generate',
		'action' => itw_app_link('action=generateProducts')
	));

	if ($productClass->isInBox() === false){
		$infoBox->addContentRow('<table cellpadding="3" cellspacing="0">' .
			'<tr>' .
			'<td class="smallText" colspan="2">' . sysLanguage::get('TEXT_BOX_SET_TITLE') . '</td>' .
			'</tr>' .
			'<tr>' .
			'<td class="smallText">' . sysLanguage::get('TEXT_BOX_SET_BODY') . '</td>' .
			'<td class="smallText">' . tep_draw_input_field('discs', '', 'size=5') . '</td>' .
			'</tr>' .
			'<tr>' .
			'<td class="smallText" colspan="2">' . htmlBase::newElement('button')->setText('Generate Box Set')
			->setType('submit')->draw() . '</td>' .
			'</tr>' .
			'<tr>' .
			'<td class="smallText" colspan="2"><br>' . sysLanguage::get('TEXT_BOX_SET_FOTTER') . tep_draw_hidden_field('products_id', $productId) . '</td>' .
			'</tr>' .
			'</table>');
	}

	if ($productClass->isBox() === true){
		$discs = $productClass->getDiscs(false, true);
		foreach($discs as $setProductId){
			$setProductClass = new product($setProductId);
			addGridRow($setProductClass, &$tableGrid, &$infoBoxes);
		}
	}
}

echo $tableGrid->draw();
