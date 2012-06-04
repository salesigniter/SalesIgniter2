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
 * Info class for the order class
 *
 * @package   Order
 * @author    Stephen Walker <stephen@itwebexperts.com>
 * @copyright Copyright (c) 2011, I.T. Web Experts
 */

class OrderInfo
{

	/**
	 * @var string
	 */
	protected $key = '';

	/**
	 * @var string
	 */
	protected $val = '';

	/**
	 * @param string $k
	 * @param string $v
	 */
	public function __construct($k = '', $v = '')
	{
		$this->key = $k;
		$this->val = $v;
	}

	/**
	 * @return array|null
	 */
	public function getKey()
	{
		return $this->key;
	}

	/**
	 * @return string
	 */
	public function getValue()
	{
		return $this->val;
	}

	/**
	 * @return string
	 */
	public function jsonEncode()
	{
		return json_encode(array(
			'key'   => $this->getKey(),
			'value' => $this->getValue()
		));
	}

	/**
	 * @param string $data
	 */
	public function jsonDecode($data)
	{
		$data = json_decode($data, true);
		$this->key = $data['key'];
		$this->val = $data['value'];
	}
}

?>