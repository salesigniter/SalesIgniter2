<?php
/*
	Rentals Products Extension Version 1

	I.T. Web Experts, Sales Igniter E-Commerce System v2
	http://www.itwebexperts.com

	Copyright (c) 2011 I.T. Web Experts

	This script and it's source is not redistributable
*/

class rentalProducts_admin_customers_default extends Extension_rentalProducts {

	public function __construct(){
		parent::__construct();
	}

	public function load(){
		if ($this->isEnabled() === false) return;

		EventManager::attachEvents(array(
			'AdminCustomersGridAddButtons',
			'AdminCustomerListingAddHeader',
			'AdminCustomerListingAddBodyRow'
		), null, $this);
	}

	public function AdminCustomerListingAddHeader(&$tableGridHeader){
		$tableGridHeader[] = array('text' => sysLanguage::get('TABLE_HEADING_MEMBER_OR_USER'));
		$tableGridHeader[] = array('text' => sysLanguage::get('TABLE_HEADING_MEMBERSHIP_STATUS'));
	}

	public function AdminCustomerListingAddBodyRow($customer, &$tableGridBodyRow){
		if (empty($customer['MembershipBillingReport'])){
			$addCls = '';
		}
		elseif ($customer['MembershipBillingReport']['status'] == 'A') {
			$addCls .= ' dataTableRowA';
		}
		elseif ($customer['MembershipBillingReport']['status'] == 'D') {
			$addCls .= ' dataTableRowD';
		}

		$hasQueue = 'false';
		if (!isset($customer['CustomersMembership']) || $customer['CustomersMembership']['ismember'] == 'U'){
			$member = 'User';
		}
		elseif ($customer['CustomersMembership']['ismember'] == 'M') {
			$member = 'Member';
			$hasQueue = 'true';
		}
		else {
			$member = 'Unknown';
		}

		if (!isset($customer['CustomersMembership'])){
			$activate = '';
		}
		elseif ($customer['CustomersMembership']['activate'] == 'Y') {
			$activate = 'Active';
		}
		elseif ($customer['CustomersMembership']['activate'] == 'N') {
			$activate = 'InActive';
		}

		$tableGridBodyRow['addCls'] = $addCls;
		$tableGridBodyRow['rowAttr']['data-has_queue'] = $hasQueue;
		$tableGridBodyRow['columns'][] = array('text' => $member, 'align' => 'center');
		$tableGridBodyRow['columns'][] = array('text' => $activate, 'align' => 'center');
	}

	public function AdminCustomersGridAddButtons(&$gridButtons){
		array_unshift(
			$gridButtons,
			htmlBase::newElement('button')->usePreset('continue')->setText('Auto Send Rentals')->setToolTip('Automatically Send All<br>Available Membership Rentals')->addClass('sendMemberRentalsButton')
		);

		$gridButtons[] = htmlBase::newElement('button')->setText('Rental Queue')->addClass('rentalQueueButton')->disable();
	}
}
?>