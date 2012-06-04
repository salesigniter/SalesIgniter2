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
	 * @param array|null $orderTotals
	 */
	public function __construct(array $orderTotals = null)
	{
		if (is_null($orderTotals) === false){
			foreach($orderTotals as $i => $tInfo){
				$orderTotal = new OrderTotal($tInfo);
				$this->totals[$orderTotal->getModule()] = $orderTotal;
			}
		}
	}

	/**
	 * @param OrderTotal $orderTotal
	 */
	public function add(OrderTotal $orderTotal)
	{
		$this->totals[$orderTotal->getModule()] = $orderTotal;
	}

	/**
	 * @param string $type
	 * @return float|null
	 */
	public function getTotalValue($type)
	{
		$OrderTotal = $this->get($type);
		if (is_null($OrderTotal) === false){
			return (float)$OrderTotal->getValue();
		}
		return null;
	}

	/**
	 * @param string $moduleType
	 * @return OrderTotal|null
	 */
	public function get($moduleType)
	{
		$orderTotal = null;
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
			return ($a->getSortOrder() < $b->getSortOrder() ? -1 : 1);
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
						'text'  => $OrderTotal->getTitle()
					),
					array(
						'align' => 'right',
						'text'  => $OrderTotal->getText()
					)
				)
			));
		}

		return $orderTotalTable;
	}

	/**
	 * @return string
	 */
	public function jsonEncode()
	{
		$TotalJsonArray = array();
		foreach($this->totals as $OrderTotal){
			$TotalJsonArray[] = $OrderTotal->jsonEncode();
		}
		return json_encode($TotalJsonArray);
	}

	/**
	 * @param string $data
	 */
	public function jsonDecode($data)
	{
		$Totals = json_decode($data, true);
		foreach($Totals as $tInfo){
			$OrderTotal = new OrderTotal();
			$OrderTotal->jsonDencode($tInfo);
			$this->totals[$OrderTotal->getModule()] = $OrderTotal;
		}
	}
}

require(dirname(__FILE__) . '/Total.php');
