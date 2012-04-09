<?php
class OrderTotalModuleBase extends ModuleBase
{

	public function init($code, $forceEnable = false, $moduleDir = false) {
		$this->import(new Installable);
		$this->import(new SortedDisplay);

		$this->setModuleType('orderTotal');
		parent::init($code, $forceEnable, $moduleDir);

	}

	public function getStatus() {
		return $this->isEnabled();
	}

	public function process(array &$outputData) {
		die('Process function not overwritten.');
	}

	public function pre_confirmation_check($orderTotal) {
	}

	public function selection_test() {
	}

	public function formatAmount($amount) {
		global $order, $currencies;
		return $currencies->format($amount, true, $order->info['currency'], $order->info['currency_value']);
	}

	public function onInstall(&$module, &$moduleConfig) {
	}
}

?>