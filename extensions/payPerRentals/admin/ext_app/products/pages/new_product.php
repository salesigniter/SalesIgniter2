<?php
class PayPerRentalsProductClassImport extends MI_Importable
{

	private $_isPayPerRental = false;

	public function initPayPerRental() {
		$Qdata = Doctrine_Query::create()
			->from('ProductsPayPerRental')
			->where('products_id = ?', $this->getId())
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
		if ($Qdata && sizeof($Qdata) > 0){
			$Data = $Qdata[0];

			$this->productType->loadPurchaseType('reservation');
		}
	}
}

/*
	Pay Per Rentals Extension Version 1
	
	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class payPerRentals_admin_products_new_product extends Extension_payPerRentals
{

	public function __construct() {
		parent::__construct();
	}

	public function load() {
		if ($this->isEnabled() === false) {
			return;
		}

		EventManager::attachEvents(array(
				'ProductInfoClassConstruct'
			), null, $this);
	}

	public function ProductInfoClassConstruct(Product &$ProductClass, $Product) {
		$ProductClass->import(new PayPerRentalsProductClassImport);
		//$ProductClass->initPayPerRental();
	}
}

?>