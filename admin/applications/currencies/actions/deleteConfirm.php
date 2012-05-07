<?php
$success = false;
$toDelete = explode(',', $_GET['currency_id']);
$CurrenciesTable = Doctrine_Core::getTable('CurrenciesTable');
foreach($toDelete as $currencyId){
	$Currency = $CurrenciesTable->find((int)$currencyId);
	if ($Currency){
		if ($Currency->code == sysConfig::get('DEFAULT_CURRENCY')){
			Doctrine_Query::create()
				->update('Configuration')
				->set('configuration_value', '?', '')
				->where('configuration_key = ?', 'DEFAULT_CURRENCY')
				->execute();
		}
		$Currency->delete();
		$success = true;
	}
}

EventManager::attachActionResponse(array(
	'success' => $success
), 'json');
