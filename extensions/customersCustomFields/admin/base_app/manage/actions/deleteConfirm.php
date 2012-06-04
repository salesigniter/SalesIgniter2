<?php
if (isset($_GET['field_id'])){
	$Record = Doctrine_Core::getTable('CustomersCustomFields')
		->find((int) $_GET['field_id']);
}
elseif (isset($_GET['group_id'])){
	$Record = Doctrine_Core::getTable('CustomersCustomFieldsGroups')
		->find((int) $_GET['group_id']);
}

if (isset($Record) && $Record){
	$Record->delete();
	EventManager::attachActionResponse(array(
		'success'  => true
	), 'json');
}else{
	EventManager::attachActionResponse(array(
		'success' => false
	), 'json');
}
