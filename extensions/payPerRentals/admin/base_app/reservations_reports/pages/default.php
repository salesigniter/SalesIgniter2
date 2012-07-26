<?php
$Products = Doctrine_Query::create()
	->from('Products p')
	->leftJoin('p.ProductsDescription pd')
	->where('pd.language_id = ?', sysLanguage::getId());

$ProductsGrid = htmlBase::newGrid()
	->setMainDataKey('product_id')
	->allowMultipleRowSelect(true)
	->useSearching(true)
	->useSorting(true)
	->putFilterButtonsInButtonBar(true)
	->setQuery($Products);

$ProductsGrid->addHeaderRow(array(
	'columns' => array(
		array(
			'text' => 'Products',
			'useSort'   => true,
			'sortKey'   => 'pd.products_name',
			'useSearch' => true,
			'searchObj' => GridSearchObj::Equal()
				->useFieldObj(htmlBase::newElement('input')->setName('search_customer_id'))
				->setDatabaseColumn('c.customers_id')
		)
	)
));

$Products = $ProductsGrid->getResults(false);
foreach($Products as $Product){
	$ProductsGrid->addBodyRow(array(
		'rowAttr' => array(
			'data-product_id' => $Product->products_id
		),
		'columns' => array(
			array('text' => $Product->ProductsDescription[sysLanguage::getId()]->products_name)
		)
	));
}

$ListingColHeader = htmlBase::newElement('div')
	->css('font-size', '1.5em')
	->css('margin', '1em')
	->html('Select Product(s) to view the reservations for them, the row color when selected shows what color to look for on the calendar');
$ListingCol = htmlBase::newElement('div')
	->addClass('column listingColumn')
	->append($ProductsGrid);

$CalendarCol = htmlBase::newElement('div')
	->addClass('column calendarColumn');

$PageLayout = htmlBase::newElement('div')
	->append($ListingColHeader)
	->append($ListingCol)
	->append($CalendarCol);

echo $PageLayout->draw();