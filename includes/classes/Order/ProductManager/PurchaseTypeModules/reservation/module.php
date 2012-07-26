<?php
if (class_exists('PurchaseType_reservation') === false){
	require(sysConfig::getDirFsCatalog() . 'extensions/payPerRentals/purchaseTypeModules/reservation/module.php');
}

/**
 * Reservation purchase type for the order class
 *
 * @package   Order\ProductManager
 * @author    Stephen Walker <stephen@itwebexperts.com>
 * @since     2.0
 * @copyright 2012 I.T. Web Experts
 * @license   http://itwebexperts.com/license/ses-license.php
 */

class OrderPurchaseTypeReservation extends PurchaseType_reservation
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
			$resData = $this->getInfo();
			if ($resData && $resData['start_date']->getTimestamp() > 0){
				return PurchaseType_reservation_utilities::parse_reservation_info($resData);
			}
		}
		return '';
	}

	public function hasEnoughInventory($Qty)
	{
		//echo __FILE__ . '::' . __LINE__ . '::CHECKING QTY::' . $Qty . "\n";
		$return = true;
		/**
		 * If overbooking is allowed then there's no reason to check the inventory
		 */
		if ($this->getData('allow_overbooking') == 0){
			/**
			 * If there's enough available then no need to check the reserved/out statuses
			 */
			if ($this->_invItems[$this->getConfigData('INVENTORY_STATUS_AVAILABLE')]['total'] < $Qty){
				/**
				 * If it's not using serials then there's no need in checking
				 */
				if ($this->getData('use_serials') == 1){
					$excluded = array();
					for($i = 0; $i < $Qty; $i++){
						$AvailableBarcode = $this->getAvailableSerial($excluded);
						if ($AvailableBarcode > -1){
							$excluded[] = $AvailableBarcode;
						}
						else {
							$return = false;
							break;
						}
					}
				}
			}
		}

		return $return;
	}

	public function onGetEmailList(&$orderedProductsString)
	{
		global $currencies;
		$ReservationInfo = $this->getInfo();
		if ($ReservationInfo['start_date']->getTimestamp() > 0){
			$orderedProductsString .= "\t" . '- Reservation Info' . "\n" .
				"\t\t" . '- Start Date: ' . $ReservationInfo['start_date']->format(sysLanguage::getDateFormat('long')) . "\n" .
				"\t\t" . '- End Date: ' . $ReservationInfo['end_date']->format(sysLanguage::getDateFormat('long')) . "\n";

			if (isset($ReservationInfo['shipping']) && !empty($ReservationInfo['shipping']['title'])){
				$orderedProductsString .= "\t\t" . '- Shipping Method: ' . $ReservationInfo['shipping']['title'] . ' (' . $currencies->format($ReservationInfo['shipping']['cost']) . ')' . "\n";
			}
			$orderedProductsString .= "\t\t" . '- Insurance: ' . $currencies->format($ReservationInfo['insurance_cost']) . "\n";
		}
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
		global $appExtension, $_excludedBarcodes, $_excludedQuantities;

		//if ($AssignInventory === true){
		$resInfo = $this->getInfo();

		$infoPages = $appExtension->getExtension('infoPages');
		if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_SAVE_TERMS') == 'True'){
			$termInfoPage = $infoPages->getInfoPage('conditions');
		}

		$ReservedInventoryItem = $SaleProduct->Product->ProductsPurchaseTypes[$this->getCode()]->InventoryItems[$this->getConfigData('INVENTORY_STATUS_RESERVED')];
		$AvailableInventoryItem = $SaleProduct->Product->ProductsPurchaseTypes[$this->getCode()]->InventoryItems[$this->getConfigData('INVENTORY_STATUS_AVAILABLE')];

		for($i = 0; $i < $OrderProduct->getQuantity(); $i++){
			$Reservation = $SaleProduct->Reservations
				->getTable()
				->getRecord();
			$Reservation->products_id = $OrderProduct->getProductsId();
			$Reservation->start_date = $resInfo['start_date'];
			$Reservation->end_date = $resInfo['end_date'];
			$Reservation->insurance_cost = (isset($resInfo['insurance_cost']) ? $resInfo['insurance_cost'] : 0);
			$Reservation->insurance_value = (isset($resInfo['insurance_value']) ? $resInfo['insurance_value'] : 0);
			if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_EVENTS') == 'True'){
				$Reservation->event_name = $resInfo['event_name'];
				$Reservation->event_date = $resInfo['event_date'];
				if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_GATES') == 'True'){
					$Reservation->event_gate = $resInfo['event_gate'];
				}
			}
			$Reservation->semester_name = $resInfo['semester_name'];
			$Reservation->rental_state = 'reserved';
			if (isset($resInfo['shipping']['id']) && !empty($resInfo['shipping']['id'])){
				$Reservation->shipping_method_title = $resInfo['shipping']['title'];
				$Reservation->shipping_method = $resInfo['shipping']['id'];
				$Reservation->shipping_days_before = $resInfo['shipping']['days_before'];
				$Reservation->shipping_days_after = $resInfo['shipping']['days_after'];
				$Reservation->shipping_cost = $resInfo['shipping']['cost'];
			}

			if (isset($termInfoPage)){
				$Reservation->rental_terms = str_replace("\r", '', str_replace("\n", '', str_replace("\r\n", '', $termInfoPage['PagesDescription'][Session::get('languages_id')]['pages_html_text'])));
				if (sysConfig::get('TERMS_INITIALS') == 'true' && Session::exists('agreed_terms')){
					$Reservation->rental_terms .= '<br/>Initials: ' . Session::get('agreed_terms');
				}
			}

			EventManager::notify('ReservationOnInsertOrderedProduct', $Reservation, $OrderProduct);
			$SaleProduct->Reservations->add($Reservation);

			$ReservedInventoryItem->item_total += 1;
			$AvailableInventoryItem->item_total -= 1;

			if ($this->getData('use_serials') == 1){
				$Inventory = $SaleProduct->SaleInventory
					->getTable()
					->getRecord();
				$Inventory->serial_number = $this->getAvailableSerial(&$_excludedBarcodes);
				$SaleProduct->SaleInventory->add($Inventory);

				$AvailableInventoryItem->unlink('Serials', array($Inventory->Serial->id));
				$ReservedInventoryItem->link('Serials', array($Inventory->Serial->id));
			}
		}
		$AvailableInventoryItem->save();
		$ReservedInventoryItem->save();
		//}
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

		if (isset($PurchaseTypeJson['start_date'])){
			$this->setInfo('start_date', SesDateTime::createFromArray($PurchaseTypeJson['start_date']));
		}
		if (isset($PurchaseTypeJson['end_date'])){
			$this->setInfo('end_date', SesDateTime::createFromArray($PurchaseTypeJson['end_date']));
		}
	}
}