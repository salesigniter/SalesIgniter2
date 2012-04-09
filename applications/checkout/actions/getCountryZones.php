<?php
$country = $_GET['cID'];
$html = '';
$Qcheck = Doctrine_Query::create()
	->select('zone_id, zone_code, zone_name')
	->from('Zones')
	->where('zone_country_id = ?', (int)$country)
	->orderBy('zone_name')
	->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
if ($Qcheck){
	$htmlField = htmlBase::newElement('selectbox')
		->setName($_GET['state_type'])
		->attr('id', $_GET['state_type']);
	foreach($Qcheck as $zInfo){
		if (
			$zInfo['zone_id'] == $_GET['curVal'] ||
			$zInfo['zone_code'] == $_GET['curVal'] ||
			$zInfo['zone_name'] == $_GET['curVal']
		){
			$htmlField->selectOptionByValue($zInfo['zone_name']);
		}
		$htmlField->addOption($zInfo['zone_name'], $zInfo['zone_name']);
	}
}
else {
	$htmlField = htmlBase::newElement('input')
		->setName($_GET['state_type'])
		->attr('id', $_GET['state_type'])
		->setValue(isset($_GET['curVal']) ? $_GET['curVal'] : '');
}

EventManager::attachActionResponse($htmlField->draw(), 'html');
?>