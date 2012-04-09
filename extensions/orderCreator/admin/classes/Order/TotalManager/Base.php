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
	 * @return htmlElement_table
	 */
	public function edit() {
		global $Editor, $currencies, $total_weight;
		$orderTotalTable = htmlBase::newElement('newGrid')
			->addClass('orderTotalTable');

		$orderTotalTable->addButtons(array(
			htmlBase::newElement('button')->addClass('addOrderTotalButton')->usePreset('new'),
			htmlBase::newElement('button')->addClass('deleteOrderTotalButton')->usePreset('delete')->disable(),
			htmlBase::newElement('button')->addClass('moveOrderTotalButton')->attr('data-direction', 'up')
				->usePreset('moveup')->setText('Move Up')->disable(),
			htmlBase::newElement('button')->addClass('moveOrderTotalButton')->attr('data-direction', 'down')
				->usePreset('movedown')->setText('Move Down')->disable()
		));

		$orderTotalTable->addHeaderRow(array(
			'columns' => array(
				array('text' => 'Title'),
				array(
					'css'  => array('width' => '150px'),
					'text' => 'Value'
				),
				array(
					'css'  => array('width' => '225px'),
					'text' => 'Type'
				)
			)
		));
		$this->rewind();
		$totals = array();
		while($this->valid()){
			$totals[] = $this->current();
			$this->next();
		}
		usort($totals, function (OrderCreatorTotal $a, OrderCreatorTotal $b) {
			return ($a->getSortOrder() < $b->getSortOrder()) ? -1 : 1;
		});
		$count = 0;
		$totalTypes = array();
		foreach(OrderTotalModules::getModules() as $Module){
			$totalTypes[$Module->getCode()] = $Module->getTitle();
		}
		$totalTypes['custom'] = 'Custom';

		foreach($totals as $orderTotal){
			$editable = $orderTotal->isEditable();
			$totalType = $orderTotal->getModuleType();

			$hiddenField = '';
			$typeMenu = '<div class="orderTotalType">' . $totalType . '<input type="hidden" name="order_total[' . $count . '][type]" value="' . $totalType . '"></div>';
			$totalValue = '<div class="orderTotalValue" style="width:82px;text-align:left;"><span>' . $orderTotal->getValue() . '</span><input type="hidden" name="order_total[' . $count . '][value]" value="' . $orderTotal->getValue() . '"></div>';
			if ($editable === true){
				$typeMenu = htmlBase::newElement('selectbox')
					->addClass('orderTotalType')
					->setName('order_total[' . $count . '][type]');
				foreach($totalTypes as $k => $v){
					$typeMenu->addOption($k, $v);
				}

				$typeMenu->selectOptionByValue($totalType);
				$typeMenu = $typeMenu->draw();

				$hiddenField = '';
				if ($orderTotal->hasOrderTotalId()){
					$hiddenField .= '<input type="hidden" name="order_total[' . $count . '][id]" value="' . $orderTotal->getOrderTotalId() . '">';
				}

				$totalValue = '<input class="ui-widget-content orderTotalValue" type="text" size="10" name="order_total[' . $count . '][value]" value="' . $orderTotal->getValue() . '">';
			}

			if ($totalType == 'shipping'){
				$total_weight = $Editor->ProductManager->getTotalWeight();
				OrderShippingModules::setDeliveryAddress($Editor->AddressManager->getAddress('delivery'));

				$titleField = '<select name="order_total[' . $count . '][title]" style="width:98%;">';
				$Quotes = OrderShippingModules::quote();
				//print_r($Quotes);
				foreach($Quotes as $qInfo){
					$titleField .= '<optgroup label="' . $qInfo['module'] . '">';
					foreach($qInfo['methods'] as $mInfo){
						$titleField .= '<option value="' . $qInfo['id'] . '_' . $mInfo['id'] . '"' . ($orderTotal->getModule() == $qInfo['id'] && $orderTotal->getMethod() == $mInfo['id'] ? ' selected="selected"' : '') . '>' . $mInfo['title'] . ' ( Recommended Price: ' . $currencies->format($mInfo['cost']) . ' )</option>';
					}
					$titleField .= '</optgroup>';
				}

				$titleField .= '</select>';
			}
			else {
				if ($editable === true){
					$titleField = '<input class="ui-widget-content" type="text" style="width:98%;" name="order_total[' . $count . '][title]" value="' . $orderTotal->getTitle() . '">';
				}
				else {
					$titleField = '<div style="width:98%;text-align:left;">' . $orderTotal->getTitle() . '<input type="hidden" name="order_total[' . $count . '][title]" value="' . $orderTotal->getTitle() . '"></div>';
				}
			}

			$orderTotalTable->addBodyRow(array(
				'attr'    => array(
					'data-count' => $count,
					'data-code'  => $totalType
				),
				'columns' => array(
					array(
						'align' => 'center',
						'text'  => $hiddenField . $titleField
					),
					array(
						'align' => 'center',
						'text'  => $totalValue . '<input type="hidden" name="order_total[' . $count . '][sort_order]" class="totalSortOrder" value="' . $count . '"></span>'
					),
					array(
						'align' => 'right',
						'text'  => $typeMenu
					)
				)
			));
			$count++;
		}
		$orderTotalTable->attr('data-next_id', $count);
		return $orderTotalTable;
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