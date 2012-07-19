<?php
/**
 * Totals manager for the order class
 *
 * @package   Order\TotalManager
 * @author    Stephen Walker <stephen@itwebexperts.com>
 * @since     1.0
 * @copyright 2012 I.T. Web Experts
 * @license   http://itwebexperts.com/license/ses-license.php
 */

class OrderTotalManager
{

	/**
	 * @var OrderTotal[]
	 */
	protected $totals = array();

	/**
	 *
	 */
	public function __construct()
	{
	}

	/**
	 * This function is overridden in all other total managers that need to use
	 * their own custom total class
	 *
	 * @return OrderTotal
	 */
	public function getTotalClass()
	{
		return new OrderTotal();
	}

	public function prepareSave(){
		$toEncode = array();
		foreach($this->getAll() as $Total){
			$toEncode[] = $Total->prepareSave();
		}
		return $toEncode;
	}

	/**
	 * load()
	 *
	 * Takes a JSON encoded string from the database and sends it to the
	 * order total class to be loaded.
	 *
	 * @internal The total_json value is already decoded to an associative array in the model
	 * @param AccountsReceivableSalesTotals $Totals
	 */
	public function loadDatabaseData($Totals)
	{
		foreach($Totals as $Total){
			$OrderTotal = $this->getTotalClass();
			$OrderTotal->loadDatabaseData($Total->total_json);

			$this->add($OrderTotal);
		}
	}

	/**
	 * onSaveSale()
	 *
	 * Goes through all totals and adds them to the database
	 *
	 * @param AccountsReceivableSalesTotals $SaleTotals
	 */
	public function onSaveSale(AccountsReceivableSalesTotals &$SaleTotals)
	{
		foreach($this->getAll() as $Total){
			$SaleTotal = $SaleTotals
				->getTable()
				->getRecord();

			$Total->onSaveSale($SaleTotal);

			$SaleTotals->add($SaleTotal);
		}
	}

	/**
	 * @param OrderTotal $orderTotal
	 */
	public function add(OrderTotal $orderTotal)
	{
		$this->totals[$orderTotal
			->getModule()
			->getCode()] = $orderTotal;
	}

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
	 * @param string $type
	 * @return float|null
	 */
	public function getTotalValue($type)
	{
		$OrderTotal = $this->get($type);
		if ($OrderTotal !== false){
			return (float)$OrderTotal
				->getModule()
				->getValue();
		}
		return null;
	}

	/**
	 * @param $code
	 * @return bool
	 */
	public function has($code)
	{
		return (isset($this->totals[$code]) === true);
	}

	/**
	 * @param string $moduleType
	 * @return bool|OrderTotal
	 */
	public function &get($moduleType)
	{
		if (!isset($this->totals[$moduleType])){
			$this->totals[$moduleType] = $this->getTotalClass();
			$this->totals[$moduleType]->setModule($moduleType);
		}
		return $this->totals[$moduleType];
	}

	/**
	 * @return OrderTotal[]
	 */
	public function getAll()
	{
		$TotalsSorted = $this->totals;
		//echo '<pre>';print_r($TotalsSorted);
		usort($TotalsSorted, function ($a, $b)
		{
			return ($a
				->getModule()
				->getDisplayOrder() < $b
				->getModule()
				->getDisplayOrder() ? -1 : 1);
		});
		return $TotalsSorted;
	}

	/**
	 * @return htmlElement_table
	 */
	public function show()
	{
		$orderTotalTable = htmlBase::newElement('table')
			->setCellPadding(2)
			->setCellSpacing(0);

		foreach($this->totals as $OrderTotal){
			$orderTotalTable->addBodyRow(array(
				'columns' => array(
					array(
						'align' => 'right',
						'text'  => $OrderTotal
							->getModule()
							->getTitle()
					),
					array(
						'align' => 'right',
						'text'  => $OrderTotal
							->getModule()
							->getText()
					)
				)
			));
		}

		return $orderTotalTable;
	}

	/**
	 * @param OrderProductManager $ProductManager
	 */
	public function onProductAdded(OrderProductManager $ProductManager)
	{
		foreach($this->getAll() as $Module){
			//echo __FILE__ . '::' . __LINE__ . '<br>';
			//echo '<div style="margin-left:15px;">';
			$Module->onProductAdded($ProductManager);
			//echo '</div>';
		}
	}

	/**
	 * @param OrderProductManager $ProductManager
	 */
	public function onProductUpdated(OrderProductManager $ProductManager)
	{
		foreach($this->getAll() as $Module){
			//echo __FILE__ . '::' . __LINE__ . '::' . $Module->getModule()->getTitle() . '<br>';
			//echo '<div style="margin-left:15px;">';
			$Module->onProductUpdated($ProductManager);
			//echo '</div>';
		}
	}

	/**
	 * getEmailList()
	 *
	 * Returns a string broken up into multiple lines of the totals in the sale
	 *
	 * @return string
	 */
	public function getEmailList()
	{
		$orderTotals = '';
		foreach($this->getAll() as $OrderTotal){
			$orderTotals .= strip_tags($OrderTotal
				->getModule()
				->getTitle()) . ' ' . strip_tags($OrderTotal
				->getModule()
				->getText()) . "\n";
		}
		return $orderTotals;
	}

	public function onExport($addColumns, &$CurrentRow, &$HeaderRow)
	{
		foreach($this->getAll() as $OrderTotal){
			$Code = $OrderTotal->getModule()->getCode();
			if ($HeaderRow->hasColumn('v_total_' . $Code) === false){
				$HeaderRow->addColumn('v_total_' . $Code);
			}
			$OrderTotal->onExport($addColumns, &$CurrentRow, &$HeaderRow);
		}
	}
}

require(__DIR__ . '/Total.php');
