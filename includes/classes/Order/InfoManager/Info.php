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
		if (($k == 'date_added' || $k == 'last_modified') && is_array($v)){
			$Date = $this->val['date'];
			$TimeZone = $this->val['timezone'];
			$this->val = SesDateTime::createFromFormat(DATE_TIMESTAMP, $this->val['date']);
			$this->val->setTimezone(new DateTimeZone($TimeZone));
		}
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
	 * @return array
	 */
	public function prepareJsonSave()
	{
		return array(
			'key'   => $this->getKey(),
			'value' => $this->getValue()
		);
	}
}

?>