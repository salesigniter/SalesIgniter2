<?php
/*
  Package Products Version 1

  I.T. Web Experts, Rental Store v2
  http://www.itwebexperts.com

  Copyright (c) 2011 I.T. Web Experts

  This script and it's source is not redistributable
 */
class Extension_packageProducts extends ExtensionBase {

	public function __construct(){
		parent::__construct('packageProducts');
	}

	public function init(){
		if ($this->isEnabled() === false)
			return;

		EventManager::attachEvents(array(
			'OrderQueryBeforeExecute',
			'GetReservationsOrdersListingPopulateArray',
			'PayPerRentalUtilitiesGetRentalPricingModify'
		), null, $this);

		$OrdersProducts = Doctrine_Core::getTable('OrdersProducts')
			->getRecordInstance();

		$OrdersProducts->hasMany('OrdersProducts as Packaged', array(
			'local' => 'orders_products_id',
			'foreign' => 'package_id',
			'cascade' => array('delete')
		));

		$OrdersProducts->hasOne('OrdersProducts as PackageProduct', array(
			'local' => 'package_id',
			'foreign' => 'orders_products_id'
		));
	}

	public function OrderQueryBeforeExecute(&$Qorder){
		global $appExtension;
		$Qorder->leftJoin('op.Packaged oppack')
			->leftJoin('oppack.Barcodes oppackbarcodes')
			->leftJoin('oppackbarcodes.ProductsInventoryBarcodes oppackbarcodesinfo');

		if ($appExtension->isInstalled('payPerRentals')){
			$Qorder->leftJoin('oppack.OrdersProductsReservation oppackres')
				->leftJoin('oppackres.ProductsInventoryBarcodes oppackresbarcodes');
		}
	}

	public function GetReservationsOrdersListingPopulateArray(&$Reservations){
		$Qreservations = Doctrine_Query::create()
			->from('Orders o')
			->leftJoin('o.OrdersAddresses oa')
			->leftJoin('o.OrdersProducts op')
			->leftJoin('op.Packaged opp')
			->leftJoin('opp.OrdersProductsReservation opr')
			->leftJoin('opr.ProductsInventoryBarcodes ib')
			->leftJoin('ib.ProductsInventory i')
			->leftJoin('opr.ProductsInventoryQuantity iq')
			->leftJoin('iq.ProductsInventory i2')
			->where('opr.start_date BETWEEN "' . $_GET['start_date'] . '" AND "' . $_GET['end_date'] . '"')
			->andWhere('opr.rental_state = ?', 'reserved')
			->andWhere('oa.address_type = ?', 'delivery')
			->andWhere('opr.parent_id is null');

		if(isset($_GET['eventSort'])){
			$Qreservations->orderBy('opr.event_name ' . $_GET['eventSort']);
		}
		if(isset($_GET['gateSort'])){
			$Qreservations->orderBy('opr.event_gate ' . $_GET['gateSort']);
		}

		if ($_GET['filter_pay'] == 'pay'){
			$Qreservations->andWhere('opr.amount_payed >= op.final_price');
		}else
			if ($_GET['filter_pay'] == 'notpay'){
				$Qreservations->andWhere('opr.amount_payed < op.final_price');
			}
		if ((int)$_GET['filter_status'] > 0){
			if($_GET['filter_status'] == 2){
				$Qreservations->andWhere('opr.rental_status_id = '. $_GET['filter_status'].' OR opr.rental_status_id is null');
			}else{
				$Qreservations->andWhere('opr.rental_status_id = ?', $_GET['filter_status']);
			}
		}


		EventManager::notify('OrdersListingBeforeExecute', &$Qreservations);

		$Qreservations = $Qreservations->execute();
		if ($Qreservations !== false){
			$Orders = $Qreservations->toArray(true);
			foreach($Orders as $oInfo){
				foreach($oInfo['OrdersProducts'] as $opInfo){
					$Reservation = array(
						'orders_id'       => $oInfo['orders_id'],
						'OrdersAddresses' => $oInfo['OrdersAddresses'],
						'OrdersProducts'  => $opInfo['Packaged']
					);

					$Reservations[] = $Reservation;
				}
			}
		}
	}

	public function PayPerRentalUtilitiesGetRentalPricingModify(&$PricingData){
		$PPR = Doctrine_Core::getTable('ProductsPayPerRental')
			->find($PricingData['pay_per_rental_id']);
		$Product = $PPR->Products;
	}
}
