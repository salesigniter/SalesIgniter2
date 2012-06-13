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
 * Totals manager for the order class
 *
 * @package   Order
 * @author    Stephen Walker <stephen@itwebexperts.com>
 * @copyright Copyright (c) 2011, I.T. Web Experts
 */

class OrderTotalManager
{

	/**
	 * @var OrderTotal[]
	 */
	protected $totals = array();

	/**
	 *
	 */
	public function __construct()
	{

	}

	/**
	 * @param $ModuleCode
	 * @return bool|OrderTotal
	 */
	public function &getTotal($ModuleCode)
	{
		$return = false;
		if (isset($this->totals[$ModuleCode])){
			$return =& $this->totals[$ModuleCode];
		}
		return $return;
	}

	/**
	 * @param OrderTotal $orderTotal
	 */
	public function add(OrderTotal $orderTotal)
	{
		$this->totals[$orderTotal->getModule()->getCode()] = $orderTotal;
	}

	/**
	 * @param string $type
	 * @return float|null
	 */
	public function getTotalValue($type)
	{
		$OrderTotal = $this->get($type);
		if ($OrderTotal !== false){
			return (float)$OrderTotal->getModule()->getValue();
		}
		return null;
	}

	/**
	 * @param string $moduleType
	 * @return bool|OrderTotal
	 */
	public function get($moduleType)
	{
		$orderTotal = false;
		if (isset($this->totals[$moduleType])){
			$orderTotal = $this->totals[$moduleType];
		}
		return $orderTotal;
	}

	/**
	 * @return array|OrderTotal[]
	 */
	public function getAll()
	{
		$TotalsSorted = $this->totals;
		usort($TotalsSorted, function ($a, $b)
		{
			return ($a->getModule()->getDisplayOrder() < $b->getModule()->getDisplayOrder() ? -1 : 1);
		});
		return $TotalsSorted;
	}

	/**
	 * @return htmlElement_table
	 */
	public function show()
	{
		$orderTotalTable = htmlBase::newElement('table')
			->setCellPadding(2)
			->setCellSpacing(0);

		foreach($this->totals as $OrderTotal){
			$orderTotalTable->addBodyRow(array(
				'columns' => array(
					array(
						'align' => 'right',
						'text'  => $OrderTotal->getModule()->getTitle()
					),
					array(
						'align' => 'right',
						'text'  => $OrderTotal->getModule()->getText()
					)
				)
			));
		}

		return $orderTotalTable;
	}

	/**
	 * @return array
	 */
	public function prepareJsonSave()
	{
		$TotalJsonArray = array();
		foreach($this->totals as $OrderTotal){
			$TotalJsonArray[] = $OrderTotal->prepareJsonSave();
		}
		return $TotalJsonArray;
	}

	/**
	 * Used when loading the sale from the database
	 *
	 * @param AccountsReceivableSalesTotals $Total
	 */
	public function jsonDecodeTotal(AccountsReceivableSalesTotals $Total){
		$TotalDecoded = json_decode($Total->total_json, true);
		$OrderTotal = new OrderTotal($TotalDecoded['data']['module_code']);
		$OrderTotal->jsonDecode($TotalDecoded);

		$this->add($OrderTotal);
	}

	/**
	 * @param OrderProductManager $ProductManager
	 */
	public function onProductAdded(OrderProductManager $ProductManager){
		foreach($this->getAll() as $Module){
			$Module->onProductAdded($ProductManager);
		}
	}

	/**
	 * @param OrderProductManager $ProductManager
	 */
	public function onProductUpdated(OrderProductManager $ProductManager){
		foreach($this->getAll() as $Module){
			$Module->onProductUpdated($ProductManager);
		}
	}
}

require(dirname(__FILE__) . '/Total.php');
