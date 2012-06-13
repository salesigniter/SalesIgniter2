<?php
/**
 * Order total class for the order creator order total manager
 *
 * @package OrderCreator
 * @author Stephen Walker <stephen@itwebexperts.com>
 * @copyright Copyright (c) 2011, I.T. Web Experts
 */

class OrderCreatorTotal extends OrderTotal
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
		if (file_exists(sysConfig::getDirFsCatalog() . 'extensions/orderCreator/admin/classes/OrderTotalModules/' . $ModuleCode . '/module.php')){
			$className = 'OrderCreatorOrderTotal' . ucfirst($ModuleCode);
			if (!class_exists($className)){
				require(sysConfig::getDirFsCatalog() . 'extensions/orderCreator/admin/classes/OrderTotalModules/' . $ModuleCode . '/module.php');
			}
			$Module = new $className();
		}else{
			$Module = OrderTotalModules::getModule($ModuleCode);
		}
		return $Module;
	}

	/**
	 * @return bool
	 */
	public function isEditable() {
		if (method_exists($this->Module, 'isEditable')){
			return $this->Module->isEditable();
		}
		return true;
	}

	/**
	 * @return bool
	 */
	public function hasTotalId(){
		return (isset($this->data['total_id']));
	}

	/**
	 * @return int
	 */
	public function getTotalId(){
		return (int)$this->data['total_id'];
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