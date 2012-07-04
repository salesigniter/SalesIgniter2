<?php
if (class_exists('OrderPurchaseTypeReservation') === false){
	require(sysConfig::getDirFsCatalog() . 'includes/classes/Order/ProductManager/PurchaseTypeModules/reservation/module.php');
}

/**
 * Reservation purchase type for the order creator class
 *
 * @package   Order\OrderCreator\ProductManager\Product\PurchaseTypeModules
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
			$newPrice += $ReservationInfo['insurance_cost'];
		}

		$OrderProduct->setPrice($newPrice);
		if ($this->hasEnoughInventory($ReservationInfo, $OrderProduct->getQuantity()) === false){
			$OrderProduct->needsConfirmation(true);
			$OrderProduct->setConfirmationMessage('This Product Does Not have Enough Inventory For The Selected Dates.');
		}
	}

	public function onUpdateOrderProduct(OrderCreatorProduct &$OrderedProduct)
	{
	}

	public function OrderCreatorProductManagerUpdateFromPost(OrderCreatorProduct &$OrderedProduct)
	{
	}

	public function OrderCreatorAfterProductName(OrderCreatorProduct $OrderedProduct, $allowEdit = true)
	{
		global $currencies;
		$return = '';
		$resInfo = null;
		if ($OrderedProduct->hasInfo('OrdersProductsReservation')){
			$resData = $OrderedProduct->getInfo('OrdersProductsReservation');
			$resInfo = $this->formatOrdersReservationArray($resData);
		}
		elseif ($OrderedProduct->hasInfo('ReservationInfo')) {
			$resInfo = $OrderedProduct->getInfo('ReservationInfo');
		}
		$id = $OrderedProduct->getId();
		$changeButton = htmlBase::newElement('button')
			->setText('Select Dates')
			->addClass('reservationDates');

		$return .= '<br /><small><b><i><u>' . sysLanguage::get('TEXT_INFO_RESERVATION_INFO') . '</u></i></b>&nbsp;' . '</small>';
		/*This part will have to be changed for events*/

		if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_EVENTS') == 'False'){
			if (is_null($resInfo) === false){
				$start = $resInfo['start_date'];
				$end = $resInfo['end_date'];
			}
			else {
				$start = new SesDateTime();
				$end = new SesDateTime();
			}
			$return .= '<br /><small><i>' .
				'- Start Date: <span class="res_start_date">' . $start->format(sysLanguage::getDateTimeFormat()) . '</span><br/>' .
				'- End Date: <span class="res_end_date">' . $end->format(sysLanguage::getDateTimeFormat()) . '</span>' .
				($allowEdit === true ? $changeButton->draw() .
					'<input type="hidden" class="ui-widget-content resDateHidden" name="product[' . $id . '][reservation][dates]" value="' . $start->format(sysLanguage::getDateTimeFormat()) . ',' . $end->format(sysLanguage::getDateTimeFormat()) . '">'
					: '') .
				'</i></small><div class="selectDialog"></div>';
		}
		else {
			$eventb = htmlBase::newElement('selectbox')
				->setName('product[' . $id . '][reservation][events]')
				->addClass('eventf');
			//->attr('id', 'eventz');
			$eventb->addOption('0', 'Select an Event');

			$Events = PurchaseType_reservation_utilities::getEvents();
			if ($Events){
				foreach($Events as $qev){
					$eventb->addOption($qev['events_id'], $qev['events_name']);
					if (isset($resInfo['event_name']) && $resInfo['event_name'] == $qev['events_name']){
						$eventb->selectOptionByValue($qev['events_id']);
					}
				}
			}

			$gateb = htmlBase::newElement('selectbox')
				->setName('gate')
				->addClass('gatef');
			$gateb->addOption('0', 'Autoselect Gate');

			$Gates = PurchaseType_reservation_utilities::getGates();
			if ($Gates){
				foreach($Gates as $iGate){
					$gateb->addOption($iGate['gates_id'], $iGate['gate_name']);
				}
			}

			if (isset($resInfo['event_name']) && !empty($resInfo['event_name'])){
				$QeventSelected = Doctrine_Query::create()
					->from('PayPerRentalEvents')
					->where('events_name = ?', $resInfo['event_name'])
					->fetchOne();

				if ($QeventSelected){
					$eventb->selectOptionByValue($QeventSelected->events_id);
				}
			}

			if (isset($resInfo['event_gate']) && !empty($resInfo['event_gate'])){
				$GateSelected = PurchaseType_reservation_utilities::getGates($resInfo['event_gate']);
				if ($GateSelected){
					$gateb->selectOptionByValue($GateSelected->gates_id);
				}
			}

			$return .= '<br /><small><i> - Events ' . $eventb->draw() . '</i></small>'; //use gates too in OC
			if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_GATES') == 'True'){
				$return .= '<br /><small><i> - Gates ' . $gateb->draw() . '</i></small>'; //use gates too in OC
			}
		}

		if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_UPS_RESERVATION') == 'False'){
			$Module = OrderShippingModules::getModule('zonereservation');
		}
		else {
			$Module = OrderShippingModules::getModule('upsreservation');
		}

		if ($this->shippingIsNone() === false && $this->shippingIsStore() === false){
			$shipInput = '';
			if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_EVENTS') == 'True'){
				$selectBox = htmlBase::newElement('selectbox')
					->addClass('ui-widget-content reservationShipping')
					->setName('product[' . $id . '][reservation][shipping]');

				if (isset($Module) && is_object($Module)){
					$quotes = $Module->quote();
					foreach($quotes['methods'] as $method){
						$selectBox->addOption(
							$method['id'],
							$method['title'] . ' ( ' . $currencies->format($method['cost']) . ' )',
							false,
							array(
								'days_before' => $method['days_before'],
								'days_after'  => $method['days_after']
							)
						);
					}
				}
			}
			else {
				$selectBox = htmlBase::newElement('input')
					->setType('hidden')
					->addClass('ui-widget-content reservationShipping')
					->setName('product[' . $id . '][reservation][shipping]');
			}
			if (is_null($resInfo) === false && isset($resInfo['shipping']) && $resInfo['shipping'] !== false && isset($resInfo['shipping']['title']) && !empty($resInfo['shipping']['title']) && isset($resInfo['shipping']['cost']) && !empty($resInfo['shipping']['cost'])){
				if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_EVENTS') == 'True'){
					$selectBox->selectOptionByValue($resInfo['shipping']['id']);
				}
				else {
					$selectBox->setValue($resInfo['shipping']['id']);
				}
				$shipInput = '<span class="reservationShippingText">' . $resInfo['shipping']['title'] . '</span>';
				$return .= '<br /><small><i> - ' . sysLanguage::get('TEXT_INFO_SHIPPING_METHOD') . ' ' . $selectBox->draw() . $shipInput . '</i></small>';
			}
		}
		//if (is_null($resInfo) === false && isset($resInfo['deposit_amount']) && $resInfo['deposit_amount'] > 0){
		if ($this->getDepositAmount() > 0){
			$return .= '<br /><small><i> - ' . sysLanguage::get('TEXT_INFO_DEPOSIT_AMOUNT') . ' ' . $currencies->format($this->getDepositAmount()) . '</i></small>';
		}
		//}

		EventManager::notify('ParseReservationInfoEdit', $return, $resInfo);
		return $return;
	}
}