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
 *
 */
class ProductTypeBase extends ModuleBase
{

	/**
	 * @param OrderProduct                    $OrderProduct
	 * @param AccountsReceivableSalesProducts $SaleProduct
	 * @param bool                            $AssignInventory
	 */
	public function onSaveSale(OrderProduct $OrderProduct, AccountsReceivableSalesProducts &$SaleProduct, $AssignInventory = false)
	{
	}

	/**
	 * @param OrderProduct $OrderProduct
	 * @return array
	 */
	public function prepareJsonSave(OrderProduct &$OrderProduct)
	{
		return array();
	}

	/**
	 * @param OrderProduct $OrderProduct
	 * @param array        $ProductTypeJson
	 */
	public function jsonDecode(OrderProduct &$OrderProduct, array $ProductTypeJson)
	{
	}
}