<?php
/**
 * Order total manager class for the order creator
 *
 * @package   Order\OrderCreator\TotalManager
 * @author    Stephen Walker <stephen@itwebexperts.com>
 * @since     2.0
 * @copyright 2012 I.T. Web Experts
 * @license   http://itwebexperts.com/license/ses-license.php
 */

class OrderCreatorTotalManager extends OrderTotalManager
{

	/**
	 * @param string $ModuleCode
	 */
	public function remove($ModuleCode)
	{
		if (isset($this->totals[$ModuleCode]) === true){
			unset($this->totals[$ModuleCode]);
		}
	}

	/**
	 *
	 */
	public function updateFromPost()
	{
		global $currencies, $Editor;
		foreach($_POST['order_total'] as $id => $tInfo){
			$OrderTotal = $this->get($tInfo['type']);

			$addTotal = false;
			if (is_null($OrderTotal) === true){
				$OrderTotal = new OrderCreatorTotal();
				$OrderTotal->setModule($tInfo['type']);
				$addTotal = true;
			}

			$value = $tInfo['value'];
			if (substr($value, -3, 1) == ',' || substr($value, -5, 1) == ','){
				$value = str_replace(',', '.', $value);
				$value[strpos($value, '.')] = '';
			}
			else {
				$value = str_replace(',', '', $value);
			}

			$OrderTotal->setSortOrder($tInfo['sort_order']);
			$OrderTotal->setTitle($tInfo['title']);
			$OrderTotal->setValue($value);
			$OrderTotal->setText($currencies->format($value, true, $Editor->getCurrency(), $Editor->getCurrencyValue()));
			$OrderTotal->setModule($tInfo['type']);
			$OrderTotal->setMethod(null);

			if ($addTotal === true){
				$this->totals[$OrderTotal->getModule()] = $OrderTotal;
			}

			if ($tInfo['type'] == 'shipping'){
				$shipModule = explode('_', $tInfo['title']);
				$OrderTotal->setModule($shipModule[0]);
				$OrderTotal->setMethod($shipModule[1]);

				$Module = OrderShippingModules::getModule($shipModule[0]);
				$Quote = $Module->quote($shipModule[1]);
				$OrderTotal->setTitle($Quote['module'] . ' ( ' . $Quote['methods'][0]['title'] . ' ) ');
				$Editor->setShippingModule($tInfo['title']);
			}
		}
	}

	/**
	 * @param Doctrine_Collection $CollectionObj
	 */
	public function addAllToCollection(Doctrine_Collection &$CollectionObj)
	{
		$CollectionObj->clear();
		$this->rewind();
		while($this->valid()){
			$orderTotal = $this->current();

			$OrdersTotal = new OrdersTotal();
			$OrdersTotal->title = $orderTotal->getTitle();
			$OrdersTotal->text = $orderTotal->getText();
			$OrdersTotal->value = $orderTotal->getValue();
			$OrdersTotal->module_type = $orderTotal->getModuleType();
			$OrdersTotal->module = $orderTotal->getModule();
			$OrdersTotal->method = $orderTotal->getMethod();
			$OrdersTotal->sort_order = $orderTotal->getSortOrder();

			$CollectionObj->add($OrdersTotal);
			$this->next();
		}
	}

	/**
	 * @param string $key
	 * @param float  $amount
	 */
	public function addToTotal($key, $amount)
	{
		foreach($this->totals as $OrderTotal){
			if ($OrderTotal->getModule() == $key){
				$OrderTotal->setValue($OrderTotal->getValue() + $amount);
			}
		}
	}

	/**
	 * Used when loading the sale from the database
	 *
	 * @param AccountsReceivableSalesTotals $Total
	 */
	public function jsonDecodeTotal(AccountsReceivableSalesTotals $Total)
	{
		$TotalDecoded = json_decode($Total->total_json, true);
		$OrderTotal = new OrderCreatorTotal($TotalDecoded['data']['module_code']);
		$OrderTotal->jsonDecode($TotalDecoded);

		$this->add($OrderTotal);
	}

	/**
	 * Used from init method in OrderCreator class
	 *
	 * @param string $data
	 */
	public function jsonDecode($data)
	{
		$this->totals = array();
		$Totals = json_decode($data, true);
		foreach($Totals as $tInfo){
			$OrderTotal = new OrderCreatorTotal();
			$OrderTotal->jsonDecode($tInfo);

			$this->add($OrderTotal);
		}
	}
}

require(dirname(__FILE__) . '/Total.php');
