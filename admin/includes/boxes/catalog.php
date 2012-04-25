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
	'text'     => sysLanguage::get('BOX_HEADING_CATALOG'),
	'link'     => false,
	'children' => array()
);

if (sysPermissions::adminAccessAllowed('categories', 'default') === true){
	$contents['children'][] = array(
		'link'     => false,
		'text'     => 'Categories',
		'children' => array(
			array(
				'link' => itw_app_link(null, 'categories', 'default', 'SSL'),
				'text' => 'View Categories'
			)
		)
	);
}

if (sysPermissions::adminAccessAllowed('products') === true){
	$productsMenu = array();
	if (sysPermissions::adminAccessAllowed('products', 'default') === true){
		$productsMenu[] = array(
			'link' => itw_app_link(null, 'products', 'default', 'SSL'),
			'text' => 'View Products'
		);
	}

	if (sysPermissions::adminAccessAllowed('products', 'expected') === true){
		$productsMenu[] = array(
			'link' => itw_app_link(null, 'products', 'expected', 'SSL'),
			'text' => sysLanguage::get('BOX_CATALOG_PRODUCTS_EXPECTED')
		);
	}
	$contents['children'][] = array(
		'link'     => false,
		'text'     => 'Products',
		'children' => $productsMenu
	);
}

EventManager::notify('BoxCatalogAddLink', &$contents);
if (count($contents['children']) == 0){
	$contents = array();
}
?>