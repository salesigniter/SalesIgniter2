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
		'text' => 'Reports',
		'link' => false,
		'children' => array()
	);

	if (sysPermissions::adminAccessAllowed('membership', 'billing_report') === true){
		$contents['children'][] = array(
			'link' => false,
			'text' => 'Recurring Sales Reports',
			'children' => array(
				array(
					'link' => itw_app_link(null, 'membership', 'billing_report', 'SSL'),
					'text' => sysLanguage::get('BOX_CUSTOMERS_MEMBERSHIP_BILLING_REPORT')
				)
			)
		);
	}
	
	if (sysPermissions::adminAccessAllowed('statistics') === true){
		$salesReports = array();
		$miscReports = array();
		$productReports = array();
		if (sysPermissions::adminAccessAllowed('statistics', 'customers') === true){
			$miscReports[] = array(
				'link' => itw_app_link(null, 'statistics', 'customers', 'SSL'),
				'text' => 'Customers Orders'
			);
		}
		
		if (sysPermissions::adminAccessAllowed('statistics', 'keywords') === true){
			$miscReports[] = array(
				'link' => itw_app_link(null, 'statistics', 'keywords', 'SSL'),
				'text' => 'Search Keywords'
			);
		}
		
		if (sysPermissions::adminAccessAllowed('statistics', 'monthlySales') === true){
			$salesReports[] = array(
				'link' => itw_app_link(null, 'statistics', 'monthlySales', 'SSL'),
				'text' => 'Monthly Sales'
			);
		}
		
		if (sysPermissions::adminAccessAllowed('statistics', 'purchasedProducts') === true){
			$productReports[] = array(
				'link' => itw_app_link(null, 'statistics', 'purchasedProducts', 'SSL'),
				'text' => 'Purchased Products'
			);
		}
		
		if (sysPermissions::adminAccessAllowed('statistics', 'viewedProducts') === true){
			$productReports[] = array(
				'link' => itw_app_link(null, 'statistics', 'viewedProducts', 'SSL'),
				'text' => 'Products Views'
			);
		}
		if (sysPermissions::adminAccessAllowed('statistics', 'salesReport') === true){
			$salesReports[] = array(
				'link' => itw_app_link(null, 'statistics', 'salesReport', 'SSL'),
				'text' => 'Sales Report'
			);
		}
		$contents['children'][] = array(
			'link' => false,
			'text' => 'Sales Reports',
			'children' => $salesReports
		);
		$contents['children'][] = array(
			'link' => false,
			'text' => 'Product Reports',
			'children' => $productReports
		);
		$contents['children'][] = array(
			'link' => false,
			'text' => 'Misc Reports',
			'children' => $miscReports
		);
	}

	EventManager::notify('BoxMarketingAddLink', &$contents);
	if(count($contents['children']) == 0){
		$contents = array();
	}
?>