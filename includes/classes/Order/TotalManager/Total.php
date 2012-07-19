<?php
/**
 * Total for the total manager class
 *
 * @package   Order\TotalManager
 * @author    Stephen Walker <stephen@itwebexperts.com>
 * @since     1.0
 * @copyright 2012 I.T. Web Experts
 * @license   http://itwebexperts.com/license/ses-license.php
 */

class OrderTotal
{

	/**
	 * @var OrderTotalModuleBase
	 */
	public $Module;

	/**
	 * @var array
	 */
	public $data = array(
		'module_code' => ''
	);

	/**
	 *
	 */
	public function __construct()
	{
	}

	/**
	 * @param array $data
	 */
	public function loadDatabaseData(array $data)
	{
		$this->data = $data['data'];
		$this->setModule(
			$this->data['module_code'],
			(isset($data['module_json']) ? $data['module_json'] : null)
		);
	}

	/**
	 * @return array
	 */
	public function prepareSave()
	{
		$toEncode = array(
			'data' => $this->data
		);
		if (method_exists($this->Module, 'prepareSave')){
			$toEncode['module_json'] = $this->Module->prepareSave();
		}
		return $toEncode;
	}

	/**
	 * @param AccountsReceivableSalesTotals $Total
	 */
	public function onSaveProgress(AccountsReceivableSalesTotals &$Total)
	{
		$Module = $this->getModule();
		//echo __FILE__ . '::' . __LINE__ . '<pre>';print_r($Module);

		$Total->module_code = $Module->getCode();
		$Total->total_value = $Module->getValue();
		$Total->display_order = $Module->getDisplayOrder();
		$Total->total_json = $this->prepareSave();

		if (method_exists($Module, 'onSaveProgress')){
			$Module->onSaveProgress($Total);
		}
	}

	/**
	 * @param AccountsReceivableSalesTotals $Total
	 */
	public function onSaveSale(AccountsReceivableSalesTotals &$Total)
	{
		$Module = $this->getModule();

		$Total->module_code = $Module->getCode();
		$Total->total_value = $Module->getValue();
		$Total->display_order = $Module->getDisplayOrder();
		$Total->total_json = $this->prepareSave();

		if (method_exists($Module, 'onSaveSale')){
			$Module->onSaveSale($Total);
		}
	}

	/**
	 * @param $ModuleCode
	 * @return OrderTotalModuleBase
	 */
	public function getTotalModule($ModuleCode)
	{
		$Module = OrderTotalModules::getModule($ModuleCode);
		return $Module;
	}

	/**
	 * @param string     $ModuleCode
	 * @param array|null $mInfo
	 */
	public function setModule($ModuleCode, array $mInfo = null)
	{
		$this->data['module_code'] = $ModuleCode;
		$this->Module = $this->getTotalModule($ModuleCode);
		$this->Module->setData($mInfo);
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
			'display_order'  => 0,
			'value'          => 0.0000
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
	 * @param OrderProductManager $ProductManager
	 */
	public function onProductAdded(OrderProductManager &$ProductManager)
	{
		//echo __FILE__ . '::' . __LINE__  . '<br>';
		//echo '<div style="margin-left:15px">';
		$Module = $this->getModule();
		if (method_exists($Module, 'onProductAdded')){
			$Module->onProductAdded($ProductManager);
		}
		//echo '</div>';
	}

	/**
	 * @param OrderProductManager $ProductManager
	 */
	public function onProductUpdated(OrderProductManager &$ProductManager)
	{
		$Module = $this->getModule();
		//echo __FILE__ . '::' . __LINE__ . '::' . $Module->getTitle() . '<br>';
		//echo '<div style="margin-left:15px">';
		if (method_exists($Module, 'onProductUpdated')){
			$Module->onProductUpdated($ProductManager);
		}
		//echo '</div>';
	}

	public function onExport($addColumns, &$CurrentRow, &$HeaderRow)
	{
		$CurrentRow->addColumn($this->Module->getValue(), 'v_total_' . $this
			->getModule()
			->getCode());
	}
}
