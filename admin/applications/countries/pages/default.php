<?php
$Qcountries = Doctrine_Query::create()
	->select('countries_id, countries_name, countries_iso_code_2, countries_iso_code_3, address_format_id')
	->from('Countries')
	->orderBy('countries_name');

$tableGrid = htmlBase::newElement('newGrid')
	->usePagination(true)
	->useSearching(true)
	->setMainDataKey('country_id')
	->allowMultipleRowSelect(true)
	->setQuery($Qcountries);

$tableGrid->addButtons(array(
	htmlBase::newElement('button')->usePreset('new')->addClass('newButton'),
	htmlBase::newElement('button')->usePreset('edit')->addClass('editButton')->disable(),
	htmlBase::newElement('button')->usePreset('delete')->addClass('deleteButton')->disable()
));

$tableGrid->addHeaderRow(array(
	'columns' => array(
		array(
			'text'      => sysLanguage::get('TABLE_HEADING_COUNTRY_NAME'),
			'useSort'   => true,
			'sortKey'   => 'countries_name',
			'useSearch' => true,
			'searchObj' => GridSearchObj::Like()
				->useFieldObj(htmlBase::newElement('input')->setName('search_countries_name'))
				->setDatabaseColumn('countries_name')
		),
		array(
			'text'      => sysLanguage::get('TABLE_HEADING_COUNTRY_CODES_ISO2'),
			'useSort'   => true,
			'sortKey'   => 'countries_iso_code_2',
			'useSearch' => true,
			'searchObj' => GridSearchObj::Like()
				->useFieldObj(htmlBase::newElement('input')->setName('search_countries_iso_code_2'))
				->setDatabaseColumn('countries_iso_code_2')
		),
		array(
			'text'      => sysLanguage::get('TABLE_HEADING_COUNTRY_CODES_ISO3'),
			'useSort'   => true,
			'sortKey'   => 'countries_iso_code_3',
			'useSearch' => true,
			'searchObj' => GridSearchObj::Like()
				->useFieldObj(htmlBase::newElement('input')->setName('search_countries_iso_code_3'))
				->setDatabaseColumn('countries_iso_code_3')
		),
		array('text' => '&nbsp;')
	)
));

$Countries = &$tableGrid->getResults();
if ($Countries){
	foreach($Countries as $cInfo){
		$countryId = $cInfo['countries_id'];
		$countryName = $cInfo['countries_name'];
		$isoCode2 = $cInfo['countries_iso_code_2'];
		$isoCode3 = $cInfo['countries_iso_code_3'];
		$addressFormatId = $cInfo['address_format_id'];

		$tableGrid->addBodyRow(array(
			'rowAttr' => array(
				'data-country_id' => $countryId
			),
			'columns' => array(
				array('text' => $countryName),
				array('text' => $isoCode2),
				array('text' => $isoCode3),
				array('text' => '&nbsp;')
			)
		));
	}
}
?>
<div class="ui-widget ui-widget-content ui-corner-all" style="margin-right:5px;margin-left:5px;">
	<div style="margin:5px;"><?php echo $tableGrid->draw();?></div>
</div>