<?php
/*
	Sales Igniter E-Commerce System
	Version: 1.0
	
	I.T. Web Experts
	http://www.itwebexperts.com
	
	Copyright (c) 2010 I.T. Web Experts
	
	This script and its source are not distributable without the written conscent of I.T. Web Experts
*/

$contents = array(
	'text'     => sysLanguage::get('BOX_HEADING_TOOLS'),
	'link'     => false,
	'children' => array()
);

$emailTools = array();
$cartTools = array();
$databaseTools = array();

if (sysPermissions::adminAccessAllowed('mail', 'default') === true){
	$emailTools[] = array(
		'link' => itw_app_link(null, 'mail', 'default', 'SSL'),
		'text' => sysLanguage::get('BOX_TOOLS_MAIL')
	);
}

if (sysPermissions::adminAccessAllowed('newsletters', 'default') === true){
	$emailTools[] = array(
		'link' => itw_app_link(null, 'newsletters', 'default', 'SSL'),
		'text' => sysLanguage::get('BOX_TOOLS_NEWSLETTER_MANAGER')
	);
}

if (sysPermissions::adminAccessAllowed('server_info', 'default') === true){
	$cartTools[] = array(
		'link' => itw_app_link(null, 'server_info', 'default', 'SSL'),
		'text' => sysLanguage::get('BOX_TOOLS_SERVER_INFO')
	);
}

if (sysPermissions::adminAccessAllowed('whos_online', 'default') === true){
	$cartTools[] = array(
		'link' => itw_app_link(null, 'whos_online', 'default', 'SSL'),
		'text' => sysLanguage::get('BOX_TOOLS_WHOS_ONLINE')
	);
}

if (sysPermissions::adminAccessAllowed('zones', 'default') === true){
	$cartTools[] = array(
		'link' => itw_app_link(null, 'zones', 'default', 'SSL'),
		'text' => 'Google Zones'
	);
}

if (sysPermissions::adminAccessAllowed('ses_update', 'default') === true){
	$cartTools[] = array(
		'link' => itw_app_link(null, 'ses_update', 'default', 'SSL'),
		'text' => sysLanguage::get('TEXT_ADMIN_MENU_SES_UPDATES')
	);
}
if (sysPermissions::adminAccessAllowed('database_manager', 'default') === true){
	$databaseTools[] = array(
		'link' => itw_app_link(null, 'database_manager', 'default'),
		'text' => 'Database Management'
	);
}

if (sysPermissions::adminAccessAllowed('index', 'manageFavorites') === true){
	$cartTools[] = array(
		'link' => itw_app_link(null, 'index', 'manageFavorites'),
		'text' => 'Manage Favorites'
	);
}

if (sysPermissions::adminAccessAllowed('cleardb', 'default') === true){
	$databaseTools[] = array(
		'link' => itw_app_link(null, 'cleardb', 'default'),
		'text' => 'Clear Database'
	);
}
$contents['children'][] = array(
	'link'     => false,
	'text'     => 'Email Tools',
	'children' => $emailTools
);
$contents['children'][] = array(
	'link'     => false,
	'text'     => 'Cart Tools',
	'children' => $cartTools
);
$contents['children'][] = array(
	'link'     => false,
	'text'     => 'Database Tools',
	'children' => $databaseTools
);

EventManager::notify('BoxToolsAddLink', &$contents);
if (count($contents['children']) == 0){
	$contents = array();
}
?>