<?php
/**
 * Total for the total manager class
 *
 * @package Order
 * @author Stephen Walker <stephen@itwebexperts.com>
 * @copyright Copyright (c) 2011, I.T. Web Experts
 */

class OrderTotal
{

	/**
	 * @var array|null
	 */
	protected $totalInfo = array();

	/**
	 * @param array|null $tInfo
	 */
	public function __construct(array $tInfo = null) {
		if (is_null($tInfo) === false){
			$this->totalInfo = $tInfo;
		}
	}

	/**
	 * @return bool
	 */
	public function hasOrderTotalId() {
		return array_key_exists('orders_total_id', $this->totalInfo);
	}

	/**
	 * @return int
	 */
	public function getOrderTotalId() {
		return (int) $this->totalInfo['orders_total_id'];
	}

	/**
	 * @return string
	 */
	public function getModuleType() {
		return (string) $this->totalInfo['module_type'];
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return (string) $this->totalInfo['title'];
	}

	/**
	 * @return string
	 */
	public function getText() {
		return (string) $this->totalInfo['text'];
	}

	/**
	 * @return float
	 */
	public function getValue() {
		return (float) $this->totalInfo['value'];
	}

	/**
	 * @return int
	 */
	public function getSortOrder() {
		return (int) $this->totalInfo['sort_order'];
	}

	/**
	 * @return string
	 */
	public function getModule() {
		return (string) $this->totalInfo['module'];
	}

	/**
	 * @return string
	 */
	public function getMethod() {
		return (string) $this->totalInfo['method'];
	}
}

?>