<?php
if ($_GET['baseModel'] != $_GET['model']){
	$BaseModel = Doctrine_Core::getTable($_GET['baseModel']);
	$Model = $BaseModel->getRelation($_GET['model'])->getTable();
}else{
	$Model = Doctrine_Core::getTable($_GET['model']);
}
$Relations = $Model->getRelations();
sort($Relations);

$rels = array();
foreach($Relations as $rInfo){
	$rels[] = $rInfo->getAlias();
}

$cols = array();
$Columns = $Model->getColumns();
foreach($Columns as $cName => $cInfo){
	$cols[] = $cName;
}

EventManager::attachActionResponse(array(
	'success' => true,
	'relations' => $rels,
	'columns' => $cols
), 'json');
