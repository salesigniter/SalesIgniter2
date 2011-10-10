<?php
/*
	Multi Stores Extension Version 1

	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class multiStore_admin_rentalProducts_rental_report_default extends Extension_multiStore {

	public function __construct(){
		parent::__construct('multiStore');
	}

	public function load(){
		global $appExtension;
		if ($this->enabled === false) return;

		EventManager::attachEvents(array(
				'RentalReportOrdersQueryBeforeExecute',
				'RentalReportsGridAddHeaderColFront',
				'RentalReportsGridFilterAddHeaderColFront',
				'RentalReportsGridAddBodyColFront'
			), null, $this);

	}

	public function RentalReportOrdersQueryBeforeExecute(&$Qorders){
		$Qorders
			->leftJoin('o.OrdersToStores o2s')
			->leftJoin('o2s.Stores s')
			->whereIn('o2s.stores_id', Session::get('admin_showing_stores'));
	}

	public function RentalReportsGridAddHeaderColFront(&$gridHeaderRow){
		$gridHeaderRow[] = array('text' => sysLanguage::get('TABLE_HEADING_STORES_NAME'));
	}

	public function RentalReportsGridFilterAddHeaderColFront(&$gridHeaderFilterRow){
		$gridHeaderFilterRow[] = array('text' => '');
	}

	public function RentalReportsGridAddBodyColFront(&$gridBodyRow, $oInfo, $opInfo){
		$gridBodyRow[] = array('text' => $oInfo['OrdersToStores']['Stores']['stores_name']);
	}
}
