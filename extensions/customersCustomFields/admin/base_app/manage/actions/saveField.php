<?php
$CustomersCustomFields = Doctrine_Core::getTable('CustomersCustomFields');
if (isset($_GET['field_id'])){
	$Field = $CustomersCustomFields->find((int)$_GET['field_id']);
}
else {
	$Field = $CustomersCustomFields->create();
}

$Field->field_key = $_POST['field_key'];
$Field->field_data = array(
	'type' => $_POST['input_type'],
	'required' => (isset($_POST['input_required']) ? 1 : 0),
	'multiple' => (isset($_POST['is_multiple']) ? 1 : 0),
	'default_value' => $_POST['field_default_value'],
	'label_max_chars' => $_POST['label_max_chars'],
	'include_in_search' => (isset($_POST['include_in_search']) ? 1 : 0),
	'show_on' => array(
		'customer_listing' => (isset($_POST['show_on']) && in_array('customer_listing', $_POST['show_on']) ? 1 : 0),
		'order_creator' => (isset($_POST['show_on']) && in_array('order_creator', $_POST['show_on']) ? 1 : 0),
		'customer_account' => (isset($_POST['show_on']) && in_array('customer_account', $_POST['show_on']) ? 1 : 0),
		'address_labels' => (isset($_POST['show_on']) && in_array('address_labels', $_POST['show_on']) ? 1 : 0)
	)
);

EventManager::notify('CustomersCustomFieldsSaveOptions', $Field);

foreach($_POST['field_name'] as $lId => $fieldName){
	$Field->Description[$lId]->field_name = $fieldName;
	$Field->Description[$lId]->language_id = $lId;
}

if ($Field->Options && $Field->Options->count() > 0){
	$Field->Options->clear();
}

if ($_POST['input_type'] == 'select' || $_POST['input_type'] == 'radio' || $_POST['input_type'] == 'checkbox'){
	$lID = Session::get('languages_id');

	$i = 0;
	foreach($_POST['option_name'] as $index => $val){
		if (!empty($val)){
			$NewOption = $Field->Options->getTable()->getRecord();
			$NewOption->display_order = $_POST['option_sort'][$index];
			$NewOption->Description[$lID]->language_id = $lID;
			$NewOption->Description[$lID]->option_name = $val;

			$Field->Options->add($NewOption);
			$i++;
		}
	}
}

//echo '<pre>';print_r($Field->toArray(true));
$Field->save();

EventManager::attachActionResponse(array(
	'success' => true,
	'fInfo' => array(
		'input_type' => $Field->field_data->input_type,
		'field_name' => $Field->Description[Session::get('languages_id')]->field_name,
		'show_on_site' => $Field->field_data->show_on->customers_account
	)
), 'json');
