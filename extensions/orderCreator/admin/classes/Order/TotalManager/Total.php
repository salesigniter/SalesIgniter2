<?php
/**
 * Order total class for the order creator order total manager
 *
 * @package   OrderCreator\TotalManager
 * @author    Stephen Walker <stephen@itwebexperts.com>
 * @since     2.0
 * @copyright 2012 I.T. Web Experts
 * @license   http://itwebexperts.com/license/ses-license.php
 */

class OrderCreatorTotal extends OrderTotal
{

	/**
	 * @param array $TotalInfo
	 */
	public function loadSessionData(array $TotalInfo)
	{
		$this->data = array_merge($this->data, $TotalInfo['data']);

		$this->Module = $this->getTotalModule($this->data['module_code']);
		if (isset($TotalInfo['module_json'])){
			if (method_exists($this->Module, 'loadSessionData')){
				$this->Module->loadSessionData($TotalInfo['module_json']);
			}
		}
	}

	/**
	 * @param $ModuleCode
	 * @return OrderTotalModuleBase
	 */
	public function getTotalModule($ModuleCode)
	{
		if (file_exists(sysConfig::getDirFsCatalog() . 'clientData/extensions/orderCreator/admin/classes/Order/TotalManager/TotalModules/' . $ModuleCode . '/module.php')){
			$className = 'OrderCreatorTotal' . ucfirst($ModuleCode);
			if (!class_exists($className)){
				require(sysConfig::getDirFsCatalog() . 'clientData/extensions/orderCreator/admin/classes/Order/TotalManager/TotalModules/' . $ModuleCode . '/module.php');
			}
			$Module = new $className();
		}
		elseif (file_exists(__DIR__ . '/TotalModules/' . $ModuleCode . '/module.php')){
			$className = 'OrderCreatorTotal' . ucfirst($ModuleCode);
			if (!class_exists($className)){
				require(__DIR__ . '/TotalModules/' . $ModuleCode . '/module.php');
			}
			$Module = new $className();
		}
		else {
			$Module = parent::getTotalModule($ModuleCode);
		}
		return $Module;
	}

	/**
	 * @return bool
	 */
	public function isEditable()
	{
		if (method_exists($this->Module, 'isEditable')){
			return $this->Module->isEditable();
		}
		return true;
	}

	/**
	 * @return bool
	 */
	public function hasTotalId()
	{
		return (isset($this->data['total_id']));
	}

	/**
	 * @return int
	 */
	public function getTotalId()
	{
		return (int)$this->data['total_id'];
	}
}
