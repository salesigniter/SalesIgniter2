<?php
$CustomerCustomFields = Doctrine_Core::getTable('CustomersCustomFields');

$CustomFieldsInfo = $Editor->InfoManager->getInfo('CustomersCustomFieldsValues');
if (!is_array($CustomFieldsInfo)){
	$CustomFieldsInfo = array();
}
foreach($_POST['customers_custom_field'] as $id => $val){
	$Field = $CustomerCustomFields->find($id);

	$CustomFieldsInfo[$Field->field_id] = array(
		'key'   => $Field->field_key,
		'type'  => $Field->input_type,
		'value' => $val
	);
}
$Editor->InfoManager->setInfo('CustomersCustomFieldsValues', $CustomFieldsInfo);

$Editor->getSaleModule()->saveProgress($Editor);

EventManager::attachActionResponse(array(
	'success' => true
), 'json');
