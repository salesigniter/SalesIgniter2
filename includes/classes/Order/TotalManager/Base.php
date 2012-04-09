<?php
/**
 * Totals manager for the order class
 *
 * @package Order
 * @author Stephen Walker <stephen@itwebexperts.com>
 * @copyright Copyright (c) 2011, I.T. Web Experts
 */

require(dirname(__FILE__) . '/Total.php');

class OrderTotalManager extends SplObjectStorage
{

	/**
	 * @param array|null $orderTotals
	 */
	public function __construct(array $orderTotals = null) {
		if (is_null($orderTotals) === false){
			foreach($orderTotals as $i => $tInfo){
				$orderTotal = new OrderTotal($tInfo);
				$this->add($orderTotal);
			}
		}
	}

	/**
	 * @param int $val
	 */
	public function setOrderId($val) {
		$this->orderId = (int) $val;
	}

	/**
	 * @param OrderTotal $orderTotal
	 */
	public function add(OrderTotal $orderTotal) {
		$this->attach($orderTotal);
	}

	/**
	 * @param string $type
	 * @return float|null
	 */
	public function getTotalValue($type) {
		$OrderTotal = $this->get($type);
		if (is_null($OrderTotal) === false){
			return (float) $OrderTotal->getValue();
		}
		return null;
	}

	/**
	 * @param string $moduleType
	 * @return null|OrderTotal
	 */
	public function get($moduleType) {
		$orderTotal = null;
		$this->rewind();
		while($this->valid()){
			$orderTotal = $this->current();
			if ($orderTotal->getModuleType() == $moduleType){
				break;
			}
			$this->next();
			$orderTotal = null;
		}
		return $orderTotal;
	}

	/**
	 * @return htmlElement_table
	 */
	public function show() {
		$orderTotalTable = htmlBase::newElement('table')
			->setCellPadding(2)
			->setCellSpacing(0);

		$this->rewind();
		while($this->valid()){
			$orderTotal = $this->current();

			$orderTotalTable->addBodyRow(array(
				'columns' => array(
					array(
						'align' => 'right',
						'text'  => $orderTotal->getTitle()
					),
					array(
						'align' => 'right',
						'text'  => $orderTotal->getText()
					)
				)
			));
			$this->next();
		}

		return $orderTotalTable;
	}
}

?>