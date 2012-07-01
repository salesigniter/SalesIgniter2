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
 * Product manager for the order class
 *
 * @package   Order
 * @author    Stephen Walker <stephen@itwebexperts.com>
 * @copyright Copyright (c) 2011, I.T. Web Experts
 */

class OrderProductManager
{

	/**
	 * @var OrderProduct[]
	 */
	protected $Contents = array();

	/**
	 *
	 */
	public function __construct()
	{
	}

	/**
	 * @return OrderProduct[]
	 */
	public function getContents()
	{
		return $this->Contents;
	}

	/**
	 * @param OrderProduct $orderProduct
	 * @return bool
	 */
	public function add(OrderProduct &$orderProduct)
	{
		$this->Contents[$orderProduct->getId()] = $orderProduct;
		return true;
	}

	/**
	 * @param int $id
	 * @return OrderProduct|bool
	 */
	public function &get($id)
	{
		$id = (int)$id;
		if (array_key_exists($id, $this->Contents)){
			return $this->Contents[$id];
		}
		return false;
	}

	/**
	 * @return float|int
	 */
	public function getTotalWeight()
	{
		$total_weight = 0;
		foreach($this->Contents as $Product){
			$total_weight += $Product->getWeight();
		}
		return $total_weight;
	}

	/**
	 * @return string
	 */
	public function getEmailList()
	{
		global $currencies, $typeNames;

		$orderedProductsString = '';
		foreach($this->getContents() as $orderedProduct){
			$orderedProductsString .= sprintf("%s x %s (%s) = %s\n",
				$orderedProduct->getQuantity(),
				$orderedProduct->getName(),
				$orderedProduct->getModel(),
				$currencies->display_price(
					$orderedProduct->getPrice(),
					$orderedProduct->getTaxRate(),
					$orderedProduct->getQuantity()
				)
			);

			$orderedProduct->onGetEmailList(&$orderedProductsString);
		}

		return $orderedProductsString;
	}

	/**
	 * @return array
	 */
	public function prepareJsonSave()
	{
		$ProductsJsonArray = array();
		foreach($this->getContents() as $Id => $OrderProduct){
			$ProductsJsonArray[$Id] = $OrderProduct->prepareJsonSave();
		}
		//echo __FILE__ . '::' . __LINE__ . '<pre>';print_r($ProductsJsonArray);
		return $ProductsJsonArray;
	}

	/**
	 * Used when loading the sale from the database
	 *
	 * @param AccountsReceivableSalesProducts $Product
	 */
	public function jsonDecodeProduct(AccountsReceivableSalesProducts $Product)
	{
		$OrderProduct = new OrderProduct();
		$OrderProduct->jsonDecodeProduct($Product);
		$this->Contents[$OrderProduct->getId()] = $OrderProduct;
	}
}

require(dirname(__FILE__) . '/Product.php');
require(dirname(__FILE__) . '/RentalMembershipProduct.php');
require(dirname(__FILE__) . '/OrderGiftCertificateProduct.php');
