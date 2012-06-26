<?php
$CustomersCustomFields = Doctrine_Core::getTable('CustomersCustomFields');
if (isset($_GET['field_id'])){
	$Field = $CustomersCustomFields->find((int)$_GET['field_id']);
}
else {
	$Field = $CustomersCustomFields->create();
}

$Field->input_type = $_POST['input_type'];
$Field->input_required = (isset($_POST['input_required']) ? 1 : 0);
$Field->is_multiple = (isset($_POST['is_multiple']) ? 1 : 0);
$Field->field_key = $_POST['field_key'];
$Field->field_default_value = $_POST['field_default_value'];
if (isset($_POST['show_on'])){
	$Field->show_on_site = 0;
	$Field->show_on_listing = 0;
	foreach($_POST['show_on'] as $ShowOn){
		switch($ShowOn){
			case 'site':
				$Field->show_on_site = 1;
				break;
			case 'listing':
				$Field->show_on_listing = 1;
				break;
		}
	}
}

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
		'input_type' => $Field->input_type,
		'field_name' => $Field->Description[Session::get('languages_id')]->field_name,
		'show_on_site' => $Field->show_on_site
	)
), 'json');
