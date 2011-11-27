<?php
	$setName = $_POST['set_name'];
	$AdminFavorites = new AdminFavorites;
	$Admin = Doctrine_Core::getTable('Admin')->findOneByAdminId((int)Session::get('login_id'));
	$AdminFavorites->admin_favs_name = $setName;
	$AdminFavorites->favorites_links = $Admin->favorites_links;
	$AdminFavorites->favorites_names = $Admin->favorites_names;
	$AdminFavorites->save();
	$json = array(
		'success' => true
	);

	EventManager::attachActionResponse($json, 'json');
?>