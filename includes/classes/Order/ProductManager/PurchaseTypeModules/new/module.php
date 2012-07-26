<?php
if (class_exists('PurchaseType_new') === false){
	require(sysConfig::getDirFsCatalog() . 'includes/modules/purchaseTypeModules/new/module.php');
}

/**
 * New purchase type for the order class
 *
 * @package   Order\ProductManager
 * @author    Stephen Walker <stephen@itwebexperts.com>
 * @since     2.0
 * @copyright 2012 I.T. Web Experts
 * @license   http://itwebexperts.com/license/ses-license.php
 */

class OrderPurchaseTypeNew extends PurchaseType_new
{

	/**
	 * @var array
	 */
	protected $pInfo = array();

	/**
	 * @param $k
	 * @return mixed
	 */
	public function getInfo($k = null)
	{
		return ($k === null ? $this->pInfo : $this->pInfo[$k]);
	}

	/**
	 * @param      $k
	 * @param null $v
	 */
	public function setInfo($k, $v = null)
	{
		if ($v === null){
			$this->pInfo = $k;
		}
		else {
			$this->pInfo[$k] = $v;
		}
	}

	/**
	 * @param $k
	 * @return bool
	 */
	public function hasInfo($k)
	{
		return isset($this->pInfo[$k]);
	}

	public function showProductInfo($showExtraInfo = true)
	{
		//echo __FILE__ . '::' . __LINE__ . '<pre>SHOW_EXTRA::' . (int)$showExtraInfo;print_r($this->getInfo());
		if ($showExtraInfo){
		}
		return '';
	}

	public function hasEnoughInventory($Qty)
	{
		//echo __FILE__ . '::' . __LINE__ . '::CHECKING QTY::' . $Qty . "\n";
		$return = ($this->_invItems[$this->getConfigData('INVENTORY_STATUS_AVAILABLE')]['total'] >= $Qty);
		if ($return === false && $this->getData('use_serials') == 1){
			$return = sizeof($this->_invItems[$this->getConfigData('INVENTORY_STATUS_AVAILABLE')]['serials']) >= $Qty;
		}

		return $return;
	}

	public function onGetEmailList(&$orderedProductsString)
	{
	}

	public function prepareSave()
	{
		$toEncode = $this->getInfo();
		return $toEncode;
	}

	/**
	 * Cannot typehint $SaleProduct due to the possibility of packages extension being installed
	 * and its' products are from another table with the same columns
	 *
	 * @param OrderProduct                                                            $OrderProduct
	 * @param AccountsReceivableSalesProducts|AccountsReceivableSalesProductsPackaged $SaleProduct
	 * @param bool                                                                    $AssignInventory
	 */
	public function onSaveSale(OrderProduct $OrderProduct, &$SaleProduct, $AssignInventory = false)
	{
		$PurchasedInventoryItem = $SaleProduct->Product->ProductsPurchaseTypes[$this->getCode()]->InventoryItems[$this->getConfigData('INVENTORY_STATUS_PURCHASED')];
		$AvailableInventoryItem = $SaleProduct->Product->ProductsPurchaseTypes[$this->getCode()]->InventoryItems[$this->getConfigData('INVENTORY_STATUS_AVAILABLE')];

		$Serials = $this->_invItems[$this->getConfigData('INVENTORY_STATUS_AVAILABLE')]['serials'];
		for($i = 0; $i < $OrderProduct->getQuantity(); $i++){
			$PurchasedInventoryItem->item_total += 1;
			$AvailableInventoryItem->item_total -= 1;

			if ($this->getData('use_serials') == 1){
				$Inventory = $SaleProduct->SaleInventory
					->getTable()
					->getRecord();
				$Inventory->serial_number = $Serials[$i];
				$SaleProduct->SaleInventory->add($Inventory);

				$AvailableInventoryItem->unlink('Serials', array($Inventory->Serial->id));
				$PurchasedInventoryItem->link('Serials', array($Inventory->Serial->id));
			}
		}
		$AvailableInventoryItem->save();
		$PurchasedInventoryItem->save();
	}

	/**
	 * Cannot typehint due to the possibility of packages extension being installed
	 * and its' products are from another table with the same columns
	 *
	 * @param AccountsReceivableSalesProducts|AccountsReceivableSalesProductsPackaged $Product
	 * @param array                                                                   $PurchaseTypeJson
	 */
	public function loadDatabaseData($Product, array $PurchaseTypeJson)
	{
		$this->setInfo($PurchaseTypeJson);
	}
}