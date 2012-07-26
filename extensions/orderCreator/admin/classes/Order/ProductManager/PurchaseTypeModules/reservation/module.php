<?php
if (class_exists('OrderPurchaseTypeReservation') === false){
	require(sysConfig::getDirFsCatalog() . 'includes/classes/Order/ProductManager/PurchaseTypeModules/reservation/module.php');
}

/**
 * Reservation purchase type for the order creator class
 *
 * @package   Order\ProductManager
 * @author    Stephen Walker <stephen@itwebexperts.com>
 * @since     2.0
 * @copyright 2012 I.T. Web Experts
 * @license   http://itwebexperts.com/license/ses-license.php
 */

class OrderCreatorPurchaseTypeReservation extends OrderPurchaseTypeReservation
{

	public function allowAddToContents(OrderCreatorProduct $OrderProduct)
	{
		global $messageStack;
		$allow = false;
		if (isset($_POST['reservation_begin']) && isset($_POST['reservation_end'])){
			$allow = true;
		}
		else {
			if (!isset($_POST['reservation_begin'])){
				$messageStack->add('OrderCreator', 'No Start Date Entered', 'error');
			}

			if (!isset($_POST['reservation_end'])){
				$messageStack->add('OrderCreator', 'No End Date Entered', 'error');
			}
		}
		/**
		 * @TODO Add in reservation availability check
		 */

		if ($allow === true){
			$ReservationInfo = array(
				'start_date'      => $_POST['reservation_begin'],
				'start_time'      => $_POST['reservation_begin_time'],
				'end_date'        => $_POST['reservation_end'],
				'end_time'        => $_POST['reservation_end_time'],
				'weight'          => $OrderProduct->getWeight(),
				'shipping'        => false,
				'is_insured'      => (isset($_POST['hasInsurance']) === true),
				'insurance_cost'  => $this->getInsuranceCost(),
				'insurance_value' => $this->getInsuranceValue(),
				'deposit_amount'  => $this->getDepositAmount(),
				'days_before'     => (isset($_POST['days_before']) ? $_POST['days_before'] : 0),
				'days_after'      => (isset($_POST['days_after']) ? $_POST['days_after'] : 0),
			);

			$ReservationInfo['start_date'] = $ReservationInfo['start_date']->modify('-' . $ReservationInfo['days_before'] . ' Day');
			$ReservationInfo['end_date'] = $ReservationInfo['end_date']->modify('+' . $ReservationInfo['days_after'] . ' Day');

			if (isset($_POST['shipping_method'])){
				$Module = OrderShippingModules::getModule($_POST['shipping_method']);
				if (is_object($Module)){
					$quote = $Module->quote($shippingMethod, $OrderProduct->getWeight());

					$ReservationInfo['shipping'] = array(
						'title'  => (isset($quote['methods'][0]['title']) ? $quote['methods'][0]['title'] : ''),
						'cost'   => (isset($quote['methods'][0]['cost']) ? $quote['methods'][0]['cost'] : ''),
						'id'     => (isset($quote['methods'][0]['id']) ? $quote['methods'][0]['id'] : ''),
						'module' => $Module->getCode()
					);
				}
			}

			if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_EVENTS') == 'True'){
				$ReservationInfo['event_date'] = $_POST['event_date'];
				$ReservationInfo['event_name'] = $_POST['event_name'];
				if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_GATES') == 'True'){
					if (isset($_POST['event_gate'])){
						$ReservationInfo['event_gate'] = $_POST['event_gate'];
					}
				}
			}
			if (isset($_POST['semester_name'])){
				$ReservationInfo['semester_name'] = $_POST['semester_name'];
			}
			else {
				$ReservationInfo['semester_name'] = '';
			}

			if (isset($_POST['id']['reservation']) && !empty($_POST['id']['reservation'])){
				$attrValue = attributesUtil::getAttributeString($_POST['id']['reservation']);
				if (!empty($attrValue)){
					$ReservationInfo['aID_string'] = $attrValue;
				}
			}

			$this->setInfo($ReservationInfo);
		}

		return $allow;
	}

	public function onAddToContents(OrderCreatorProduct $OrderProduct)
	{
		$ReservationInfo = $this->getInfo();

		$newPrice = 0;
		$pricing = $this->figureProductPricing($ReservationInfo);
		if (!empty($pricing)){
			$newPrice += $pricing['price'];
		}

		if ($ReservationInfo['is_insured'] === true){
			//$newPrice += $ReservationInfo['insurance_cost'];
		}

		$OrderProduct->setPrice($newPrice);
		if ($this->hasEnoughInventory($OrderProduct->getQuantity()) === false){
			$OrderProduct->needsConfirmation(true);
			$OrderProduct->setConfirmationMessage('This Product Does Not have Enough Inventory For The Selected Dates.');
		}
	}

	public function onSaveProgress(OrderProduct $OrderProduct, &$SaleProduct)
	{
		global $appExtension, $_excludedBarcodes, $_excludedQuantities;
		$resInfo = $OrderProduct->getInfo('ReservationInfo');
		$infoPages = $appExtension->getExtension('infoPages');
		if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_SAVE_TERMS') == 'True'){
			$termInfoPage = $infoPages->getInfoPage('conditions');
		}
		for($i = 0; $i < $OrderProduct->getQuantity(); $i++){
			$Reservation = $SaleProduct->Reservations
				->getTable()
				->getRecord();
			$Reservation->start_date = $resInfo['start_date'];
			$Reservation->end_date = $resInfo['end_date'];
			$Reservation->insurance = (isset($resInfo['insurance']) ? $resInfo['insurance'] : 0);
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
		}
	}

	public function loadSessionData(array $PurchaseTypeJson)
	{
		$this->setInfo($PurchaseTypeJson);

		if (isset($PurchaseTypeJson['start_date'])){
			if (is_array($PurchaseTypeJson['start_date']) === true){
				$this->setInfo('start_date', SesDateTime::createFromArray($PurchaseTypeJson['start_date']));
			}
		}
		if (isset($PurchaseTypeJson['end_date'])){
			if (is_array($PurchaseTypeJson['end_date']) === true){
				$this->setInfo('end_date', SesDateTime::createFromArray($PurchaseTypeJson['end_date']));
			}
		}
	}
}