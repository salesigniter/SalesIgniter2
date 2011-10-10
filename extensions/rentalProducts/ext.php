<?php
/*
	Rental Products Extension Version 1

	I.T. Web Experts, SalesIgniter v1
	http://www.itwebexperts.com

	Copyright (c) 2011 I.T. Web Experts

	This script and it's source is not redistributable
*/

class Extension_rentalProducts extends ExtensionBase
{

	public function __construct() {
		parent::__construct('rentalProducts');
	}

	public function init() {
		global $appExtension;
		if ($this->enabled === false) {
			return;
		}

		EventManager::attachEvents(array(
				'ProductQueryBeforeExecute',
				'ProductInfoClassBindMethods',
				'OrderQueryBeforeExecute'
			), null, $this);
	}

	public function ProductInfoClassBindMethods(&$class) {
		$class->bindMethod('getRentalSettings', function (&$ProductInfoClass) {
				$QrentalSettings = Doctrine_Query::create()
					->from('ProductsRentalSettings')
					->where('products_id = ?', $ProductInfoClass->getId())
					->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
				return ($QrentalSettings && sizeof($QrentalSettings) > 0 ? $QrentalSettings[0] : false);
			});
	}

	public function ProductQueryBeforeExecute(&$productQuery) {
		$productQuery->addSelect('prs.*')
			->leftJoin('p.ProductsRentalSettings prs');
	}

	public function OrderQueryBeforeExecute(&$Qorder){
		$Qorder->leftJoin('op.OrdersProductsRentals rented')
			->leftJoin('rented.ProductsInventoryBarcodes rentedBarcodes');
	}
}

?>