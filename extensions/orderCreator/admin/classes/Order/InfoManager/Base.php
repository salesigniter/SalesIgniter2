<?php
/**
 * Info manager for the order class
 *
 * @package OrderCreator
 * @author Stephen Walker <stephen@itwebexperts.com>
 * @copyright Copyright (c) 2011, I.T. Web Experts
 */

require(dirname(__FILE__) . '/Info.php');

/**
 * @package OrderCreator
 */
class OrderCreatorInfoManager extends OrderInfoManager
{
	/**
	 * @param array|null $infoArray
	 */
	public function __construct(array $infoArray = null) {
		if (!empty($infoArray)){
			foreach($infoArray as $k => $v){
				$this->info[$k] = new OrderCreatorInfo($k, $v);
			}
		}
	}

	public function setInfo($k, $v){
		$this->info[$k] = new OrderCreatorInfo($k, $v);
	}

	public function jsonDecode($data){
		$infoArray = json_decode($data, true);
		foreach($infoArray as $k => $info){
			$this->info[$k] = new OrderCreatorInfo();
			$this->info[$k]->jsonDecode($info);
		}
	}
}
