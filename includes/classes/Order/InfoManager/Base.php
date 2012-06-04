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
 * Info manager for the order class
 *
 * @package   Order
 * @author    Stephen Walker <stephen@itwebexperts.com>
 * @copyright Copyright (c) 2011, I.T. Web Experts
 */

class OrderInfoManager
{

	/**
	 * @var array
	 */
	protected $info = array();

	/**
	 * @param array|null $infoArray
	 */
	public function __construct(array $infoArray = null)
	{
		if (!empty($infoArray)){
			foreach($infoArray as $k => $v){
				$this->info[$k] = new OrderInfo($k, $v);
			}
		}
	}

	/**
	 * @param string $k
	 * @return OrderInfo[]
	 */
	public function getInfo($k = '')
	{
		if ($k != ''){
			return (isset($this->info[$k]) ? $this->info[$k]->getValue() : '');
		}
		return $this->info;
	}

	/**
	 * @param $k
	 * @param $v
	 */
	public function setInfo($k, $v)
	{
		$this->info[$k] = new OrderInfo($k, $v);
	}

	/**
	 * @return string
	 */
	public function jsonEncode()
	{
		$jsonArray = array();
		foreach($this->getInfo() as $k => $Info){
			$jsonArray[$k] = $Info->jsonEncode();
		}
		return json_encode($jsonArray);
	}

	/**
	 * @param $data
	 */
	public function jsonDecode($data)
	{
		$infoArray = json_decode($data, true);
		foreach($infoArray as $k => $info){
			$this->info[$k] = new OrderInfo();
			$this->info[$k]->jsonDecode($info);
		}
	}
}

require(dirname(__FILE__) . '/Info.php');
