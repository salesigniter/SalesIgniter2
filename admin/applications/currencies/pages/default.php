<?php
$Qcurrencies = Doctrine_Query::create()
	->from('CurrenciesTable')
	->orderBy('title');

EventManager::notify('CurrencyListingQueryBeforeExecute', &$Qcurrencies);

$tableGrid = htmlBase::newElement('newGrid')
	->usePagination(true)
	->setMainDataKey('currency_id')
	->allowMultipleRowSelect(true)
	->setQuery($Qcurrencies);

$tableGrid->addButtons(array(
	htmlBase::newElement('button')->setIcon('transferthick-e-w')->setText('Update Exchange Rate')
		->setHref(itw_app_link('action=updateExchange', 'currencies', 'default')),
	htmlBase::newElement('button')->addClass('newButton')->usePreset('new'),
	htmlBase::newElement('button')->addClass('editButton')->usePreset('edit')->disable(),
	htmlBase::newElement('button')->addClass('deleteButton')->usePreset('delete')->disable()
));

$tableGrid->addHeaderRow(array(
	'columns' => array(
		array('text' => sysLanguage::get('TABLE_HEADING_CURRENCY_NAME')),
		array('text' => sysLanguage::get('TABLE_HEADING_CURRENCY_CODES')),
		array('text' => sysLanguage::get('TABLE_HEADING_CURRENCY_VALUE'))
	)
));

$Result = &$tableGrid->getResults();
if ($Result){
	$allGetParams = tep_get_all_get_params(array('action', 'cID'));
	foreach($Result as $currency){
		$currencyId = $currency['currencies_id'];
		$currencyTitle = $currency['title'];
		$currencyCode = $currency['code'];
		$currencyValue = $currency['value'];

		if (sysConfig::get('DEFAULT_CURRENCY') == $currencyCode){
			$currencyTitle = '<b>' . $currencyTitle . ' (' . sysLanguage::get('TEXT_DEFAULT') . ')</b>';
		}

		$tableGrid->addBodyRow(array(
			'rowAttr' => array(
				'data-currency_id' => $currencyId
			),
			'columns' => array(
				array('text' => $currencyTitle),
				array('text' => $currencyCode),
				array(
					'text'  => number_format($currencyValue, 8),
					'align' => 'right'
				)
			)
		));
	}
}
?>
<div class="ui-widget ui-widget-content ui-corner-all" style="margin-right:5px;margin-left:5px;">
	<div style="margin:5px;">
		<?php echo $tableGrid->draw();?>
	</div>
</div>
