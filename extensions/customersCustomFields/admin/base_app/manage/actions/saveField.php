<?php
$CustomersCustomFields = Doctrine_Core::getTable('CustomersCustomFields');
if (isset($_GET['field_id'])){
	$Field = $CustomersCustomFields->find((int)$_GET['field_id']);
}
else {
	$Field = $CustomersCustomFields->create();
}

$Field->input_type = $_POST['input_type'];
$Field->field_key = $_POST['field_key'];
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

if ($_POST['input_type'] == 'select'){
	$lID = Session::get('languages_id');

	$i = 0;
	foreach($_POST['option_name'] as $index => $val){
		if (!empty($val)){
			$NewOption = new CustomersCustomFieldsOptionsToFields();
			$NewOption->Option->sort_order = $_POST['option_sort'][$index];
			$NewOption->Option->Description[$lID]->option_name = $val;
			$NewOption->Option->Description[$lID]->language_id = $lID;

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
