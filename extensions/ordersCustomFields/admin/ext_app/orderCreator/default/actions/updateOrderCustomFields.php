<?php
$OrdersCustomFields = Doctrine_Core::getTable('OrdersCustomFields');

$CustomFieldsInfo = $Editor->InfoManager->getInfo('OrdersCustomFieldsValues');
if (!is_array($CustomFieldsInfo)){
	$CustomFieldsInfo = array();
}
foreach($_POST['orders_custom_field'] as $id => $val){
	$Field = $OrdersCustomFields->find($id);

	$CustomFieldsInfo[$Field->field_id] = array(
		'identifier' => $Field->field_identifier,
		'type'       => $Field->input_type,
		'value'      => $val,
		'display_order' => $Field->display_order
	);
}
$Editor->InfoManager->setInfo('OrdersCustomFieldsValues', $CustomFieldsInfo);

$Editor->getSaleModule()->saveProgress($Editor);

EventManager::attachActionResponse(array(
	'success' => true
), 'json');
