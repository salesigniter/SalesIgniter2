<?php
$mainServer = sysConfig::get('CURRENCY_SERVER_PRIMARY');
$backupServer = sysConfig::get('CURRENCY_SERVER_BACKUP');

$Qcurrencies = Doctrine_Query::create()
	->select('currencies_id, code, title')
	->from('CurrenciesTable')
	->execute();
foreach($Qcurrencies as $currency){
	$quote_function = 'quote_' . $mainServer . '_currency';
	$rate = $quote_function($currency->code, sysConfig::get('DEFAULT_CURRENCY'));
	$server_used = $mainServer;

	if (empty($rate) && !empty($backupServer)){
		$messageStack->addSession(
			'pageStack',
			sprintf(sysLanguage::get('WARNING_PRIMARY_SERVER_FAILED'), $mainServer, $currency->title, $currency->code),
			'warning'
		);

		$quote_function = 'quote_' . $backupServer . '_currency';
		$rate = $quote_function($currency->code, sysConfig::get('DEFAULT_CURRENCY'));

		$server_used = $backupServer;
	}

	if (!empty($rate)){
		$currency->value = $rate;
		$currency->last_updated = date('Y-m-d H:i:s');
		$currency->save();

		$messageStack->addSession(
			'pageStack',
			sprintf(sysLanguage::get('TEXT_INFO_CURRENCY_UPDATED'), $currency->title, $currency->code, $server_used),
			'success'
		);
	}
	else {
		$messageStack->addSession(
			'pageStack',
			sprintf(sysLanguage::get('ERROR_CURRENCY_INVALID'), $currency->title, $currency->code, $server_used),
			'error'
		);
	}
}

EventManager::attachActionResponse(itw_app_link(tep_get_all_get_params(array('action'))), 'redirect');
