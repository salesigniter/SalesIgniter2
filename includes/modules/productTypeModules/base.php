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
	 * @param AccountsReceivableSalesProducts $SaleProduct
	 * @param bool                            $AssignInventory
	 */
	public function onSaveSale(&$SaleProduct, $AssignInventory = false)
	{
	}

	/**
	 * @return array
	 */
	public function prepareJsonSave()
	{
		return array();
	}

	/**
	 * Cannot typehint due to the possibility of packages extension being installed
	 * and its' products are from another table with the same columns
	 *
	 * @param AccountsReceivableSalesProducts|AccountsReceivableSalesProductsPackaged $Product
	 * @param array                                                                   $ProductJson
	 */
	public function jsonDecodeProduct($Product, array $ProductJson)
	{
	}
}