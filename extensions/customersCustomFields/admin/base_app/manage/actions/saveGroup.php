<?php
$Groups = Doctrine_Core::getTable('CustomersCustomFieldsGroups');
if (isset($_GET['group_id'])){
	$Group = $Groups->find((int)$_GET['group_id']);
}
else {
	$Group = $Groups->create();
}

$Group->group_name = $_POST['group_name'];

if (isset($_POST['field'])){
	$Group->Fields->clear();

	foreach($_POST['field'] as $sortOrder => $fieldId){
		$newFieldToGroup = new CustomersCustomFieldsToGroups();
		$newFieldToGroup->field_id = $fieldId;
		$newFieldToGroup->sort_order = $sortOrder + 1;

		$Group->Fields->add($newFieldToGroup);
	}
}

//print_r($Group->toArray(true));itwExit();
$Group->save();

EventManager::attachActionResponse(array(
	'success' => true
), 'json');
