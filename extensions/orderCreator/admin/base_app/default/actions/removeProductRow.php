<?php
$Editor->ProductManager->remove((int)$_GET['id']);

$Editor->getSaleModule()->saveProgress($Editor);

EventManager::attachActionResponse(array(
	'success' => true
), 'json');
?>
