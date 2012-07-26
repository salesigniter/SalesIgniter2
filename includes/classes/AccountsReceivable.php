<?php
/**
 * Sales Igniter E-Commerce System
 * Version: {ses_version}
 *
 * I.T. Web Experts
 * http://www.itwebexperts.com
 *
 * Copyright (c) {ses_copyright} I.T. Web Experts
 *
 * This script and its source are not distributable without the written consent of I.T. Web Experts
 */

/**
 * Main accounts receivable class
 *
 * @package   AccountsReceivable
 * @author    Stephen Walker <stephen@itwebexperts.com>
 * @copyright Copyright (c) 2012, I.T. Web Experts
 */

class AccountsReceivable
{

	/**
	 * @static
	 * @param      $SaleType
	 * @param      $SaleId
	 * @param null $Revision
	 * @return mixed
	 */
	public static function getSalesQuery($SaleType, $SaleId, $Revision = null){
		$Module = AccountsReceivableModules::getModule($SaleType);
		return $Module->getSalesQuery($SaleId, $Revision);
	}

	/**
	 * @static
	 * @param      $SaleType
	 * @param      $SaleId
	 * @param null $Revision
	 * @return Order
	 */
	public static function getSale($SaleType, $SaleId, $Revision = null){
		if ($SaleType === null){
			$QSaleType = Doctrine_Query::create()
				->select('sale_module')
				->from('AccountsReceivableSales')
				->where('sale_id = ?', $SaleId)
				->orderBy('revision desc')
				->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
			$SaleType = $QSaleType[0]['sale_module'];
		}
		$Module = AccountsReceivableModules::getModule($SaleType);
		return $Module->getSale($SaleId, $Revision);
	}

	/**
	 * @static
	 * @param string $SaleType
	 * @return Order|Order[]
	 */
	public static function getSales($SaleType = ''){
		if ($SaleType != ''){
			$Module = AccountsReceivableModules::getModule($SaleType);
			$Sales = $Module->getSales();
		}else{
			AccountsReceivableModules::loadModules();
			$Sales = array();
			foreach(AccountsReceivableModules::getModules() as $Module){
				$QSales = $Module->getSalesQuery()
					->execute();
				foreach($QSales as $sInfo){
					$Sales[] = self::getSale($sInfo['sale_module'], $sInfo['sale_id'], $sInfo['sale_revision']);
				}
			}
		}
		return $Sales;
	}

	/**
	 * @static
	 * @param $SaleModule
	 * @return int
	 */
	public static function getNextId($SaleModule){
		$Max = Doctrine_Query::create()
			->select('max(sale_id) as sale_id')
			->from('AccountsReceivableSales')
			->where('sale_module = ?', $SaleModule)
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

		$nextId = ($Max[0]['sale_id'] == 0 || $Max[0]['sale_id'] == '' ? 1 : $Max[0]['sale_id'] + 1);
		return $nextId;
	}

	/**
	 * @static
	 * @param $From
	 * @param $To
	 * @return mixed
	 */
	public static function convert($From, $To){
		$FromInfo = Doctrine_Query::create()
			->from($From['model'])
			->where($From['idCol'] . ' = ?', $From['id'])
			->orderBy('revision desc')
			->fetchOne();

		$ToId = self::getNextId($To['idCol'], $To['model']);

		$Conversion = new $To['model']();
		$Conversion->{$To['idCol']} = $ToId;
		$Conversion->revision = 1;
		$Conversion->customers_id = $FromInfo->customers_id;
		$Conversion->customers_firstname = $FromInfo->customers_firstname;
		$Conversion->customers_lastname = $FromInfo->customers_lastname;
		$Conversion->customers_email_address = $FromInfo->customers_email_address;
		$Conversion->total = $FromInfo->total;
		$Conversion->date_added = date(DATE_TIMESTAMP);
		$Conversion->info_json = $FromInfo->info_json;
		$Conversion->address_json = $FromInfo->address_json;
		$Conversion->products_json = $FromInfo->products_json;
		$Conversion->totals_json = $FromInfo->totals_json;
		$Conversion->converted_from_module = $From['module'];
		$Conversion->converted_from_id = $From['id'];
		//echo '<pre>';print_r($Conversion->toArray());itwExit();
		$Conversion->save();

		return $Conversion->{$To['idCol']};
	}

	/**
	 * @static
	 * @param Order $Order
	 * @param       $convertTo
	 */
	public static function convertSale(Order $Order, $convertTo){
		$Module = $Order->getSaleModule();
		$Module->convertSale($convertTo);
	}

	/**
	 * @static
	 * @param Order $Order
	 */
	public static function duplicateSale(Order $Order){
		$Module = $Order->getSaleModule();
		$Module->duplicateSale($Order);
	}

	/**
	 * @static
	 * @param Order  $Order
	 * @param string $saleType
	 * @return int
	 */
	public static function saveSale(Order $Order, $saleType = ''){
		if (!empty($saleType)){
			$Module = AccountsReceivableModules::getModule($saleType);
		}else{
			$Module = $Order->getSaleModule();
		}
		return $Module->saveSale($Order);
	}
}
