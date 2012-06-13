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
 * Total for the total manager class
 *
 * @package   Order
 * @author    Stephen Walker <stephen@itwebexperts.com>
 * @copyright Copyright (c) 2011, I.T. Web Experts
 */

class OrderTotal
{

	/**
	 * @var OrderTotalModuleBase
	 */
	protected $Module;

	/**
	 * @var array
	 */
	protected $data = array();

	/**
	 * @param            $ModuleCode
	 * @param array|null $mInfo
	 */
	public function __construct($ModuleCode, array $mInfo = null)
	{
		$this->Module = OrderTotalModules::getModule($ModuleCode);
		$this->Module->updateData($mInfo);
		$this->data['module_code'] = $ModuleCode;
	}

	/**
	 * @return ModuleBase|OrderTotalModuleBase
	 */
	public function &getModule()
	{
		return $this->Module;
	}

	/**
	 * @param array $options
	 */
	public function setData($options = array())
	{
		$options = array_merge(array(
			'sort_order'  => 0,
			'value'       => 0.0000
		), $options);

		$this->Module->setData($options);
	}

	/**
	 * @param      $k
	 * @param null $v
	 */
	public function updateData($k, $v = null)
	{
		if (is_array($k)){
			foreach($k as $key => $value){
				$this->Module->updateData($key, $value);
			}
		}
		else {
			$this->Module->updateData($k, $v);
		}
	}

	/**
	 * @param AccountsReceivableSalesTotals $Total
	 */
	public function onSaveProgress(AccountsReceivableSalesTotals &$Total){
		$Module = $this->getModule();

		$Total->module_code = $Module->getCode();
		$Total->total_value = $Module->getValue();
		$Total->display_order = $Module->getDisplayOrder();
		$Total->total_json = json_encode($this->prepareJsonSave());

		if (method_exists($Module, 'onSaveProgress')){
			$Module->onSaveProgress($Total);
		}
	}

	/**
	 * @param AccountsReceivableSalesTotals $Total
	 */
	public function onSaveSale(AccountsReceivableSalesTotals &$Total){
		$Module = $this->getModule();

		$Total->module_code = $Module->getCode();
		$Total->total_value = $Module->getValue();
		$Total->display_order = $Module->getDisplayOrder();
		$Total->total_json = json_encode($this->prepareJsonSave());

		if (method_exists($Module, 'onSaveSale')){
			$Module->onSaveSale($Total);
		}
	}

	/**
	 * @param OrderProductManager $ProductManager
	 */
	public function onProductAdded(OrderProductManager &$ProductManager){
		$Module = $this->getModule();
		if (method_exists($Module, 'onProductAdded')){
			$Module->onProductAdded($ProductManager);
		}
	}

	/**
	 * @param OrderProductManager $ProductManager
	 */
	public function onProductUpdated(OrderProductManager &$ProductManager){
		$Module = $this->getModule();
		if (method_exists($Module, 'onProductUpdated')){
			$Module->onProductUpdated($ProductManager);
		}
	}

	/**
	 * @return array
	 */
	public function prepareJsonSave()
	{
		$toEncode = array(
			'data' => $this->data
		);
		if (method_exists($this->Module, 'prepareJsonSave')){
			$toEncode['module_json'] = $this->Module->prepareJsonSave();
		}
		return $toEncode;
	}

	/**
	 * @param array $Decoded
	 */
	public function jsonDecode(array $Decoded)
	{
		if ($Decoded){
			$this->data = $Decoded['data'];

			$this->Module = OrderTotalModules::getModule($this->data['module_code']);
			if (isset($Decoded['module_json'])){
				$this->Module->jsonDecode($Decoded['module_json']);
			}
		}
	}
}

?>