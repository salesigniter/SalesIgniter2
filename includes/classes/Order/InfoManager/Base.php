<?php
/**
 * Info manager for the order class
 *
 * @package    Order\InfoManager
 * @author     Stephen Walker <stephen@itwebexperts.com>
 * @since      2.0
 * @copyright  2012 I.T. Web Experts
 * @license    http://itwebexperts.com/license/ses-license.php
 */

class OrderInfoManager
{

	/**
	 * @var array
	 */
	protected $info = array();

	/**
	 *
	 */
	public function __construct()
	{
	}

	/**
	 * @return OrderInfo
	 */
	public function getInfoObjectClass()
	{
		return new OrderInfo();
	}

	/**
	 * @param $k
	 * @return bool
	 */
	public function hasInfo($k)
	{
		return isset($this->info[$k]);
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
		$InfoClass = $this->getInfoObjectClass();
		$InfoClass->setKey($k);
		$InfoClass->setValue($v);

		$this->info[$k] = $InfoClass;
	}

	/**
	 * @return array
	 */
	public function prepareSave()
	{
		$toEncode = array();
		foreach($this->getInfo() as $k => $Info){
			$toEncode[$k] = $Info->prepareSave();
		}
		return $toEncode;
	}

	/**
	 * @param $data
	 */
	public function loadDatabaseData($data)
	{
		foreach($data as $k => $info){
			$InfoClass = $this->getInfoObjectClass();
			$InfoClass->setKey($info['key']);
			$InfoClass->setValue($info['value']);

			$this->info[$k] = $InfoClass;
		}
	}
}

require(dirname(__FILE__) . '/Info.php');
