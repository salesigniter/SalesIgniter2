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
	 * @param AccountsReceivableSalesTotals $Total
	 */
	public function onSaveProgress(AccountsReceivableSalesTotals &$Total)
	{
		$Total->module_code = $this->getCode();
		$Total->total_value = $this->getValue();
		$Total->display_order = $this->getDisplayOrder();
		$Total->total_json = $this->prepareSave();

		$Module = $this->getModule();
		if (method_exists($Module, 'onSaveProgress')){
			$Module->onSaveProgress($Total);
		}
	}

	/**
	 * @param array $TotalInfo
	 */
	public function loadSessionData(array $TotalInfo)
	{
		$this->data = array_merge($this->data, $TotalInfo['data']);

		if (isset($TotalInfo['module_json'])){
			if (method_exists($this->Module, 'loadSessionData')){
				$this->Module->loadSessionData($TotalInfo['module_json']);
			}
		}
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
