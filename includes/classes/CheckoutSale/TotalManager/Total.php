<?php
/**
 * Order total class for the checkout sale order total manager
 *
 * @package CheckoutSale
 * @author Stephen Walker <stephen@itwebexperts.com>
 * @copyright Copyright (c) 2011, I.T. Web Experts
 */

class CheckoutSaleTotal extends OrderTotal
{

	/**
	 * @param string     $ModuleCode
	 * @param array|null $mInfo
	 */
	public function __construct($ModuleCode = '', array $mInfo = null)
	{
		if (!empty($ModuleCode)){
			$this->Module = $this->getOrderTotalModule($ModuleCode);
			$this->Module->setData($mInfo);
			$this->data['module_code'] = $ModuleCode;
		}
	}

	/**
	 * @param $ModuleCode
	 * @return ModuleBase|OrderTotalModuleBase
	 */
	private function getOrderTotalModule($ModuleCode){
		$Module = OrderTotalModules::getModule($ModuleCode);
		return $Module;
	}

	/**
	 * @param array $TotalInfo
	 */
	public function jsonDecode(array $TotalInfo)
	{
		$this->data = array_merge($this->data, $TotalInfo['data']);

		$this->Module = $this->getOrderTotalModule($this->data['module_code']);
		if (isset($TotalInfo['module_json'])){
			$this->Module->jsonDecode($TotalInfo['module_json']);
		}
	}
}

?>