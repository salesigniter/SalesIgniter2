<?php
	$success = false;
	$Categories = Doctrine_Core::getTable('BlogCategories')->findOneByBlogCategoriesId((int)$_GET['cID']);
	if ($Categories){
		$Categories->delete();
		$success = true;
	}

	EventManager::attachActionResponse(array(
		'success' => $success
	), 'json');
?>