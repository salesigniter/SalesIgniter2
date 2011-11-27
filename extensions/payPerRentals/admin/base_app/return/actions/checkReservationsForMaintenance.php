<?php

	$QMaintenancePeriod = Doctrine_Query::create()
	->from('PayPerRentalMaintenancePeriods ppmp')
	->where('after_return = ?', '1')
	->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

	$afterhire = 0; //only one prehire check supported for now
	$lastBarcode = $_GET['iBarcode'];
	foreach($QMaintenancePeriod as $mPeriod){
		$afterhire = $mPeriod['maintenance_period_id'];
	}


	$popupContent = '';
	ob_start();
	$_GET['type'] = $afterhire;
	$_GET['dialog'] = true;
	$_GET['lastBarcode'] = $lastBarcode;
	if (file_exists(sysConfig::getDirFsCatalog() . 'extensions/payPerRentals/admin/base_app/maintenance/language_defines/global.xml')){
		sysLanguage::loadDefinitions(sysConfig::getDirFsCatalog() . 'extensions/payPerRentals/admin/base_app/maintenance/language_defines/global.xml');
	}
	require(sysConfig::getDirFsCatalog(). 'extensions/payPerRentals/admin/base_app/maintenance/pages/default.php');

	$popupContent = ob_get_contents();
	ob_end_clean();


	EventManager::attachActionResponse(array(
			'success' => true,
			'popupContent' => $popupContent
		), 'json');

?>