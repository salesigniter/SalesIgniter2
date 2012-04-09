<?php
/**
 * Order total class for the order creator order total manager
 *
 * @package OrderCreator
 * @author Stephen Walker <stephen@itwebexperts.com>
 * @copyright Copyright (c) 2011, I.T. Web Experts
 */

class OrderCreatorTotal extends OrderTotal implements Serializable
{

	/**
	 * @return string
	 */
	public function serialize() {
		$data = array(
			'totalInfo' => $this->totalInfo
		);
		return serialize($data);
	}

	/**
	 * @param string $data
	 */
	public function unserialize($data) {
		$data = unserialize($data);
		foreach($data as $key => $dInfo){
			$this->$key = $dInfo;
		}
	}

	/**
	 * @return bool
	 */
	public function isEditable() {
		return (isset($this->totalInfo['editable']) && $this->totalInfo['editable'] === false ? false : true);
	}

	/**
	 * @param string $val
	 */
	public function setModuleType($val) {
		$this->totalInfo['module_type'] = (string) $val;
	}

	/**
	 * @param string $val
	 */
	public function setTitle($val) {
		$this->totalInfo['title'] = (string) $val;
	}

	/**
	 * @param string $val
	 */
	public function setText($val) {
		$this->totalInfo['text'] = (string) $val;
	}

	/**
	 * @param float $val
	 */
	public function setValue($val) {
		$this->totalInfo['value'] = (float) $val;
	}

	/**
	 * @param int $val
	 */
	public function setSortOrder($val) {
		$this->totalInfo['sort_order'] = (int) $val;
	}

	/**
	 * @param string $val
	 */
	public function setModule($val) {
		$this->totalInfo['module'] = (string) $val;
	}

	/**
	 * @param string $val
	 */
	public function setMethod($val) {
		$this->totalInfo['method'] = (string) $val;
	}
}

?>