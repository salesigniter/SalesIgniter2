<?php
/**
 * Order total manager class for the order creator
 *
 * @package OrderCreator
 * @author Stephen Walker <stephen@itwebexperts.com>
 * @copyright Copyright (c) 2011, I.T. Web Experts
 */

require(dirname(__FILE__) . '/Total.php');

class OrderCreatorTotalManager extends OrderTotalManager
{

	/**
	 * @param array|null $orderTotals
	 */
	public function __construct(array $orderTotals = null) {
		if (is_null($orderTotals) === false){
			foreach($orderTotals as $i => $tInfo){
				$orderTotal = new OrderCreatorTotal($tInfo);
				$this->add($orderTotal);
			}
		}
	}

	/**
	 * @param string $moduleType
	 */
	public function remove($moduleType) {
		$orderTotal = $this->getTotal($moduleType);
		if (is_null($orderTotal) === false){
			$this->detach($orderTotal);
		}
	}

	/**
	 *
	 */
	public function updateFromPost() {
		global $currencies, $Editor;
		foreach($_POST['order_total'] as $id => $tInfo){
			$OrderTotal = $this->get($tInfo['type']);

			$addTotal = false;
			if (is_null($OrderTotal) === true){
				$OrderTotal = new OrderCreatorTotal();
				$OrderTotal->setModuleType($tInfo['type']);
				$addTotal = true;
			}

			$OrderTotal->setSortOrder($tInfo['sort_order']);
			$OrderTotal->setTitle($tInfo['title']);
			$OrderTotal->setValue($tInfo['value']);
			$OrderTotal->setText($currencies->format($tInfo['value'], true, $Editor->getCurrency(), $Editor->getCurrencyValue()));
			$OrderTotal->setModule($tInfo['type']);
			$OrderTotal->setMethod(null);

			if ($addTotal === true){
				$this->add($OrderTotal);
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
	public function addAllToCollection(Doctrine_Collection &$CollectionObj) {
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
	 * @param float $amount
	 */
	public function addToTotal($key, $amount) {
		$this->rewind();
		while($this->valid()){
			$orderTotal = $this->current();
			if ($orderTotal->getModuleType() == $key){
				$orderTotal->setValue($orderTotal->getValue() + $amount);
			}
			$this->next();
		}
	}
}

?>