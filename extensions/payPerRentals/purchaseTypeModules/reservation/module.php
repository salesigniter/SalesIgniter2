<?php
/*
	Product Purchase Type: Reservation

	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2011 I.T. Web Experts

	This script and it's source is not redistributable
*/

/**
 * Reservation Purchase Type
 * @package ProductPurchaseTypes
 */
require(__DIR__ . '/setters.php');
require(__DIR__ . '/getters.php');
require(__DIR__ . '/checkers.php');
require(__DIR__ . '/utilities.php');
require(__DIR__ . '/htmlOutput.php');

class PurchaseType_reservation extends PurchaseType_reservation_htmlOutput
{

	public function __construct($forceEnable = false) {
		$this->setTitle('Reservation');
		$this->setDescription('Reservation products that are reserved by date');

		$this->init(
			'reservation',
			$forceEnable,
			sysConfig::getDirFsCatalog() . 'extensions/payPerRentals/purchaseTypeModules/reservation/'
		);
	}

	/*
	 * Override parent class method so we can load the reservation information
	 */
	public function loadData($productId) {
		global $appExtension;
		if ($productId !== false){
			parent::loadData($productId);

			$Qdata = Doctrine_Query::create()
				->from('ProductsPayPerRental ppr')
				->leftJoin('ppr.ProductsPayPerRentalDiscounts pprd')
				->where('ppr.products_id = ?', $productId)
				->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
			if ($Qdata && sizeof($Qdata) > 0){
				$Data = $Qdata[0];

				$this->setPayPerRentalId($Data['pay_per_rental_id']);
				$this->setPriceDaily($Data['price_daily']);
				$this->setPriceWeekly($Data['price_weekly']);
				$this->setPriceMonthly($Data['price_monthly']);
				$this->setPriceSixMonth($Data['price_six_month']);
				$this->setPriceYear($Data['price_year']);
				$this->setPriceThreeYear($Data['price_three_year']);
				$this->setQuantity($Data['quantity']);
				$this->setComboProducts($Data['combo_products']);
				$this->setComboPrice($Data['combo_price']);
				$this->setMaxDays($Data['max_days']);
				$this->setMaxMonths($Data['max_months']);
				$this->setShipping($Data['shipping']);
				$this->setMaintenance($Data['maintenance']);
				$this->setOverbooking($Data['overbooking']);
				$this->setDepositAmount($Data['deposit_amount']);
				$this->setInsurance($Data['insurance']);
				$this->setMinRentalDays($Data['min_rental_days']);
				$this->setMinPeriod($Data['min_period']);
				$this->setMaxPeriod($Data['max_period']);
				$this->setMinType($Data['min_type']);
				$this->setMaxType($Data['max_type']);

				if (isset($Data['ProductsPayPerRentalDiscounts']) && sizeof($Data['ProductsPayPerRentalDiscounts']) > 0){
					$this->setDiscounts($Data['ProductsPayPerRentalDiscounts']);
				}
				if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_UPS_RESERVATION') == 'False'){
					$this->setShipModuleCode('zonereservation');
				}
				else {
					$this->setShipModuleCode('upsreservation');
				}

				$Module = OrderShippingModules::getModule($this->getShipModuleCode(), true);
				if (is_object($Module)){
					$this->setShipModuleCode($Module->getCode());
					$enabledShipping = explode(',', $this->getShipping());

					if (!empty($enabledShipping)){
						$this->setEnabledShipping($enabledShipping);
					}
				}
			}
		}
	}

	public function showOrderedProductInfo(OrderProduct &$orderedProduct, $showExtraInfo = true) {
		if ($showExtraInfo){
			$resData = $orderedProduct->getInfo('OrdersProductsReservation');
			if ($resData && $resData[0]['start_date']->getTimestamp() > 0){
				$resInfo = $this->formatOrdersReservationArray($resData);
				return PurchaseType_reservation_utilities::parse_reservation_info(
					$orderedProduct->getProductsId(),
					$resInfo
				);
			}
		}
		return '';
	}

	public function showShoppingCartProductInfo(ShoppingCartProduct $CartProduct, $settings = array()) {
		$options = array_merge(array(
			'showReservationInfo' => true
		), $settings);

		//print_r($orderedProduct);
		//itwExit();
		if ($options['showReservationInfo'] === true){
			$resData = $CartProduct->getData('reservationInfo');
			if ($resData && $resData['start_date']->getTimestamp() > 0){
				return PurchaseType_reservation_utilities::parse_reservation_info(
					$this->getProductId(),
					$resData
				);
			}
		}
		return '';
	}

	public function orderAfterEditProductName(OrderedProduct &$orderedProduct) {
		global $currencies;
		$return = '';
		$resInfo = null;
		if ($orderedProduct->hasInfo('OrdersProductsReservation')){
			$resData = $orderedProduct->getInfo('OrdersProductsReservation');
			$resInfo = $this->formatOrdersReservationArray($resData);
		}
		else {
			$resData = $orderedProduct->getInfo();
			//print_r($orderedProduct);
			if (isset($resData['reservationInfo'])){
				$resInfo = $resData['reservationInfo'];
			}
		}
		$id = $orderedProduct->getId();

		$return .= '<br /><small><b><i><u>' . sysLanguage::get('TEXT_INFO_RESERVATION_INFO') . '</u></i></b>&nbsp;' . '</small>';
		/*This part will have to be changed for events*/

		/**/

		if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_EVENTS') == 'False'){
			if (is_null($resInfo) === false){
				$startDate = $resInfo['start_date']->format(DATE_TIMESTAMP);
				$endDate = $resInfo['end_date']->format(DATE_TIMESTAMP);
				$return .= '<br /><small><i> - Dates ( Start,End ) <input type="text" class="ui-widget-content reservationDates" name="product[' . $id . '][reservation][dates]" value="' . $startDate . ',' . $endDate . '"></i></small><div class="selectDialog"></div>';
			}
			else {
				$return .= '<br /><small><i> - Dates ( Start,End ) <input type="text" class="ui-widget-content reservationDates" name="product[' . $id . '][reservation][dates]" value=""></i></small><div class="selectDialog"></div>';
			}
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

			foreach(PurchaseType_reservation_utilities::getGates() as $iGate){
				$gateb->addOption($iGate['gates_id'], $iGate['gate_name']);
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
								'days_after' => $method['days_after']
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

	public function checkoutAfterProductName(ShoppingCartProduct &$cartProduct) {
		if ($cartProduct->hasInfo('reservationInfo')){
			$resData = $cartProduct->getInfo('reservationInfo');
			if ($resData && $resData['start_date']->getTimestamp() > 0){
				return PurchaseType_reservation_utilities::parse_reservation_info($cartProduct->getIdString(), $resData);
			}
		}
		return '';
	}

	public function shoppingCartAfterProductName(ShoppingCartProduct &$cartProduct) {
		if ($cartProduct->hasInfo('reservationInfo')){
			$resData = $cartProduct->getInfo('reservationInfo');
			if ($resData && $resData['start_date']->getTimestamp() > 0){
				return PurchaseType_reservation_utilities::parse_reservation_info($cartProduct->getIdString(), $resData);
			}
		}
		return '';
	}

	public function formatOrdersReservationArray($resData) {
		$returningArray = array(
			'start_date' => (isset($resData[0]['start_date']) ? $resData[0]['start_date'] : new DateTime()),
			'end_date' => (isset($resData[0]['end_date']) ? $resData[0]['end_date'] : new DateTime()),
			'rental_state' => (isset($resData[0]['rental_state']) ? $resData[0]['rental_state'] : null),
			'date_shipped' => (isset($resData[0]['date_shipped']) ? $resData[0]['date_shipped'] : 0),
			'date_returned' => (isset($resData[0]['date_returned']) ? $resData[0]['date_returned'] : 0),
			'broken' => (isset($resData[0]['broken']) ? $resData[0]['broken'] : 0),
			'parent_id' => (isset($resData[0]['parent_id']) ? $resData[0]['parent_id'] : null),
			'deposit_amount' => $this->getDepositAmount(),
			'semester_name' => (isset($resData[0]['semester_name']) ? $resData[0]['semester_name'] : ''),
			'event_name' => (isset($resData[0]['event_name']) ? $resData[0]['event_name'] : ''),
			'event_gate' => (isset($resData[0]['event_gate']) ? $resData[0]['event_gate'] : ''),
			'event_date' => (isset($resData[0]['event_date']) ? $resData[0]['event_date'] : new DateTime()),
			'shipping' => array(
				'module' => 'reservation',
				'id' => (isset($resData[0]['shipping_method']) ? $resData[0]['shipping_method'] : 'method1'),
				'title' => (isset($resData[0]['shipping_method_title']) ? $resData[0]['shipping_method_title'] : null),
				'cost' => (isset($resData[0]['shipping_cost']) ? $resData[0]['shipping_cost'] : 0),
				'days_before' => (isset($resData[0]['shipping_days_before']) ? $resData[0]['shipping_days_before'] : 0),
				'days_after' => (isset($resData[0]['shipping_days_after']) ? $resData[0]['shipping_days_after'] : 0)
			)
		);

		EventManager::notify('ReservationFormatOrdersReservationArray', &$returningArray, $resData);
		return $returningArray;
	}

	public function hasInventory($myQty = 1) {

		if ($this->canUseInventory() === false){
			return ($this->isEnabled());
		}

		if ($this->getData('inventory_track_method') == 'barcode'){
			$this->setInvUnavailableStatus(PurchaseType_reservation_utilities::remove_item_by_value($this->getInvUnavailableStatus(),'R', false));
		}
		$invItems = $this->getInventoryItems();
		$hasInv = false;
		if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_DATE_SELECTION') != 'Using calendar after browsing products and clicking Reserve' && Session::exists('isppr_inventory_pickup') === false && sysConfig::get('EXTENSION_PAY_PER_RENTALS_CHOOSE_PICKUP') == 'True' && sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_EVENTS') == 'False'){
			return false;
		}

		if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_EVENTS') == 'True' && sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_EVENTS_QTY') == 'True'){

			if (Session::exists('isppr_event')){
				$QModel = Doctrine_Query::create()
					->from('Products')
					->where('products_id = ?', $this->getData('products_id'))
					->execute();
				if ($QModel){
					$QProductEvents = Doctrine_Query::create()
						->from('ProductQtyToEvents')
						->where('events_id = ?', Session::get('isppr_event'))
						->andWhere('products_model = ?', $QModel[0]['products_model'])
						->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
					if ($QProductEvents && $QProductEvents[0]['qty'] > 0){
						if($myQty === false){
						if (Session::exists('isppr_product_qty')){
							$checkedQty = Session::get('isppr_product_qty');
							}
							else {
								$checkedQty = 1;
							}
						}
						else {
							$checkedQty = $myQty;
						}
						$QRes = Doctrine_Query::create()
							->select('count(*) as total')
							->from('OrdersProducts op')
							->leftJoin('op.OrdersProductsReservation opr')
							->where('opr.event_date = ?', Session::get('isppr_event_date'))
							->andWhere('op.products_id = ?', $this->getData('products_id'))
							->andWhereIn('opr.rental_state', array('out', 'reserved'))
							->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
						if ($QRes){
							if ($QProductEvents[0]['qty'] < $checkedQty + $QRes[0]['total']){
								return false;
							}
						}
					}
					else {
						return false;
					}
				}
			}
		}

		if (isset($invItems) && ($invItems != false)){
			if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_DATE_SELECTION') != 'Using calendar after browsing products and clicking Reserve'){
				$timesArr = array();
				$i1 = 0;
				if (Session::exists('isppr_date_start')){
					$startCheck = Session::get('isppr_date_start');
					if (!empty($startCheck)){
						$startDate = date_parse($startCheck);
						$endDate = date_parse(Session::get('isppr_date_end'));
						if (Session::exists('isppr_event_multiple_dates')){
							$datesArr = Session::get('isppr_event_multiple_dates');
							foreach($datesArr as $iDate){
								$startDate = date_parse($iDate);
								$endDate = date_parse($iDate);
								$timesArr[$i1]['start_date'] = mktime(
									$startDate['hour'],
									$startDate['minute'],
									$startDate['second'],
									$startDate['month'],
									$startDate['day'],
									$startDate['year']
								);
								$timesArr[$i1]['end_date'] = mktime(
									$endDate['hour'],
									$endDate['minute'],
									$endDate['second'],
									$endDate['month'],
									$endDate['day'],
									$endDate['year']
								);
								$i1++;
							}
						}
						else {
							$timesArr[$i1]['start_date'] = mktime(
								$startDate['hour'],
								$startDate['minute'],
								$startDate['second'],
								$startDate['month'],
								$startDate['day'],
								$startDate['year']
							);
							$timesArr[$i1]['end_date'] = mktime(
								$endDate['hour'],
								$endDate['minute'],
								$endDate['second'],
								$endDate['month'],
								$endDate['day'],
								$endDate['year']
							);
							$i1++;
						}
					}
				}
				$noInvDates = array();
				foreach($timesArr as $iTime){
					$invElem = 0;
					foreach($invItems as $invInfo){
						$bookingInfo = array(
							'item_type' => 'barcode',
							'item_id' => $invInfo['id']
						);
						$bookingInfo['start_date'] = new SesDateTime($iTime['start_date']);
						$bookingInfo['end_date'] = new SesDateTime($iTime['end_date']);

						if (Session::exists('isppr_inventory_pickup')){
							$pickupCheck = Session::get('isppr_inventory_pickup');
							if (!empty($pickupCheck)){
								$bookingInfo['inventory_center_pickup'] = $pickupCheck;
							}
						}
						else {
							//check here if the invInfo has a specific inventory. If there are two or more
						}

						if (Session::exists('isppr_shipping_days_before')){
							$bookingInfo['start_date'] = $bookingInfo['start_date']->modify('- ' . Session::get('isppr_shipping_days_before') . ' Day');
						}
						if (Session::exists('isppr_shipping_days_after')){
							$bookingInfo['end_date'] = $bookingInfo['end_date']->modify('+ ' . Session::get('isppr_shipping_days_after') . ' Day');
						}

						$numBookings = ReservationUtilities::CheckBooking($bookingInfo);
						if ($numBookings == 0){
							$invElem++;
							//break;
						}
					}

					if($myQty === false){
						if (Session::exists('isppr_product_qty')){
							$bookingInfo['quantity'] = (int)Session::get('isppr_product_qty');
						}else{
							$bookingInfo['quantity'] = 1;
						}
					}else{
						$bookingInfo['quantity'] = $myQty;
					}
					if($invElem - $bookingInfo['quantity'] < 0){
						$hasInv = false;
					}else{
						$hasInv = true;
					}

					if($hasInv == false){
						$noInvDates[] = $iTime['start_date'];
					}
				}
				$hasInv = false;
				if ($i1 - count($noInvDates) > 0){
					$hasInv = true;
					if (Session::exists('noInvDates')){
						$myNoInvDates = Session::get('noInvDates');
						$myNoInvDates[$this->productInfo['id']] = $noInvDates;
					}
					else {
						$myNoInvDates[$this->productInfo['id']] = $noInvDates;
					}
					if (is_array($myNoInvDates) && count($myNoInvDates) > 0){
						Session::set('noInvDates', $myNoInvDates);
					}
					if (Session::exists('isppr_event_multiple_dates')){
						$datesArrb = Session::get('isppr_event_multiple_dates');

						array_filter($datesArrb, array('this', 'isIn'));
						Session::set('isppr_event_multiple_dates', $datesArrb);
					}
				}
			}
			else {
				return true;
			}
		}

		return $hasInv || (sysConfig::get('EXTENSION_PAY_PER_RENTALS_SHOW_STOCK') == 'True');
	}

	public function processRemoveFromCart() {
		global $ShoppingCart;
		if (isset($ShoppingCart->reservationInfo)){
			if ($ShoppingCart->countContents() <= 0){
				unset($ShoppingCart->reservationInfo);
			}
		}
	}

	public function updateStock($orderId, $orderProductId, ShoppingCartProduct &$cartProduct) {
		return false;
	}

	public function processAddToOrderOrCart(&$resInfo, &$pInfo) {
		global $App, $ShoppingCart;
		$shippingMethod = $resInfo['shipping_method'];
		$rShipping = false;
		if (!empty($shippingMethod) && ($shippingMethod != 'zonereservation')){
			if (isset($resInfo['quantity'])){
				$total_weight = (int)$resInfo['quantity'] * $pInfo['weight'];
			}
			else {
				$total_weight = $pInfo['weight'];
			}
			if (is_object($Module)){
				$quote = $Module->quote($shippingMethod, $total_weight);

				$rShipping = array(
					'title' => (isset($quote['methods'][0]['title']) ? $quote['methods'][0]['title'] : ''),
					'cost' => (isset($quote['methods'][0]['cost']) ? $quote['methods'][0]['cost'] : ''),
					'id' => (isset($quote['methods'][0]['id']) ? $quote['methods'][0]['id'] : ''),
					'module' => $shippingModule
				);
			}
			else {
				$rShipping = array(
					'title' => '',
					'cost' => '',
					'id' => '',
					'module' => $shippingModule
				);
			}

			if (isset($resInfo['days_before'])){
				$rShipping['days_before'] = $resInfo['days_before'];
			}

			if (isset($resInfo['days_after'])){
				$rShipping['days_after'] = $resInfo['days_after'];
			}
		}

		$pInfo['reservationInfo'] = array(
			'start_date' => $resInfo['start_date'],
			'end_date' => $resInfo['end_date'],
			'quantity' => $resInfo['quantity'],
			'shipping' => $rShipping
		);

		if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_EVENTS') == 'True'){
			$pInfo['reservationInfo']['event_date'] = $resInfo['event_date'];
			$pInfo['reservationInfo']['event_name'] = $resInfo['event_name'];
			if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_GATES') == 'True'){
				if (isset($resInfo['event_gate'])){
					$pInfo['reservationInfo']['event_gate'] = $resInfo['event_gate'];
				}
			}
		}
		if (isset($resInfo['semester_name'])){
			$pInfo['reservationInfo']['semester_name'] = $resInfo['semester_name'];
		}
		else {
			$pInfo['reservationInfo']['semester_name'] = '';
		}

		$pricing = $this->figureProductPricing($pInfo['reservationInfo']);

		if (isset($pricing)){
			$pInfo['price'] = $pricing['price'];
			$pInfo['reservationInfo']['deposit_amount'] = $this->getDepositAmount();
			if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_DATE_SELECTION') == 'Using calendar after browsing products and clicking Reserve'){
				$pInfo['final_price'] = $pricing['price'];
			}
			else {
				$pInfo['final_price'] = $pricing['price']; //+ $pInfo['reservationInfo']['deposit_amount'];
			}
		}
	}

	public function processAddToOrder(array &$pInfo) {
		if (isset($pInfo['OrdersProductsReservation'])){
			$infoArray = array(
				'shipping_method' => $pInfo['OrdersProductsReservation'][0]['shipping_method'],
				'start_date' => $pInfo['OrdersProductsReservation'][0]['start_date'],
				'end_date' => $pInfo['OrdersProductsReservation'][0]['end_date'],
				'days_before' => $pInfo['OrdersProductsReservation'][0]['shipping_days_before'],
				'days_after' => $pInfo['OrdersProductsReservation'][0]['shipping_days_after'],
				'quantity' => $pInfo['products_quantity']
			);
			if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_UPS_RESERVATION') == 'False'){
				$infoArray['shipping_module'] = 'zonereservation';
			}else{
				$infoArray['shipping_module'] = 'upsreservation';
			}
			if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_EVENTS') == 'True'){
				$infoArray['event_date'] = $pInfo['OrdersProductsReservation'][0]['event_date'];
				$infoArray['event_name'] = $pInfo['OrdersProductsReservation'][0]['event_name'];
				if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_GATES') == 'True'){
					$infoArray['event_gate'] = $pInfo['OrdersProductsReservation'][0]['event_gate'];
				}
			}
			$infoArray['semester_name'] = $pInfo['OrdersProductsReservation'][0]['semester_name'];
		}
		else {
			//$shipping_modules = OrderShippingModules::getModule('zonereservation');
			//$quotes = $shipping_modules->quote('method');
			$infoArray = array(
				'shipping_module' => isset($pInfo['reservationInfo']['shipping']['module'])?$pInfo['reservationInfo']['shipping']['module']:'zonereservation',
				'shipping_method' => isset($pInfo['reservationInfo']['shipping']['id'])?$pInfo['reservationInfo']['shipping']['id']:'method1',//?
				'start_date'      => isset($pInfo['reservationInfo']['start_date'])?$pInfo['reservationInfo']['start_date']:0,
				'end_date'        => isset($pInfo['reservationInfo']['end_date'])?$pInfo['reservationInfo']['end_date']:0,
				'days_before'      => isset($pInfo['reservationInfo']['days_before'])?$pInfo['reservationInfo']['days_before']:0,
				'days_after'        => isset($pInfo['reservationInfo']['days_after'])?$pInfo['reservationInfo']['days_after']:0,
				'quantity'        => isset($pInfo['reservationInfo']['quantity'])?$pInfo['reservationInfo']['quantity']:(isset($pInfo['products_quantity'])?$pInfo['products_quantity']:1)
			);
			if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_EVENTS') == 'True'){
				$infoArray['event_date'] = isset( $pInfo['reservationInfo']['event_date'])? $pInfo['reservationInfo']['event_date']:0;
				$infoArray['event_name'] = isset( $pInfo['reservationInfo']['event_name'])? $pInfo['reservationInfo']['event_name']:'';
				if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_GATES') == 'True'){
					$infoArray['event_gate'] = isset( $pInfo['reservationInfo']['event_gate'])? $pInfo['reservationInfo']['event_gate']:'';
				}
			}
			$infoArray['semester_name'] = isset( $pInfo['reservationInfo']['semester_name'])? $pInfo['reservationInfo']['semester_name']:'';
		}
		$this->processAddToOrderOrCart($infoArray, $pInfo);

		EventManager::notify('ReservationProcessAddToOrder', &$pInfo);
	}

	public function addToCartPrepare(array &$CartProductData) {
		$ReservationInfo = array();

		if (isset($_POST['start_date'])){
			$ReservationInfo['start_date'] = DateTime::createFromFormat('m/d/Y', $_POST['start_date']);
		}

		if (isset($_POST['end_date'])){
			$ReservationInfo['end_date'] = DateTime::createFromFormat('m/d/Y', $_POST['end_date']);
		}

		if (isset($_POST['rental_qty'])){
			$ReservationInfo['quantity'] = $_POST['rental_qty'];
		}

		if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_EVENTS') == 'True'){
			if (isset($_POST['event_date']) && isset($_POST['event_name'])){
				$ReservationInfo['event_date'] = $_POST['event_date'];
				$ReservationInfo['event_name'] = $_POST['event_name'];
				if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_GATES') == 'True'){
					$ReservationInfo['event_gate'] = $_POST['event_gate'];
				}
			}
		}

		if (isset($_POST['semester_name'])){
			$reservationInfo['semester_name'] = $_POST['semester_name'];
		}

		if (isset($pInfo['rental_shipping']) && $_POST['rental_shipping'] !== false){
			list($shippingModule, $shippingMethod) = explode('_', $_POST['rental_shipping']);
			$ReservationInfo['shipping']['module'] = $shippingModule;
			$ReservationInfo['shipping']['id'] = $shippingMethod;
		}

		if (!empty($shippingMethod) && ($shippingMethod != 'zonereservation')){
			if (isset($ReservationInfo['quantity'])){
				$total_weight = (int)$ReservationInfo['quantity'] * $CartProductData['weight'];
			}
			else {
				$total_weight = $CartProductData['weight'];
			}
			$Module = OrderShippingModules::getModule($shippingModule);
			if (is_object($Module)){
				$quote = $Module->quote($shippingMethod, $total_weight);

				$rShipping = array(
					'title' => (isset($quote['methods'][0]['title']) ? $quote['methods'][0]['title'] : ''),
					'cost' => (isset($quote['methods'][0]['cost']) ? $quote['methods'][0]['cost'] : ''),
					'id' => (isset($quote['methods'][0]['id']) ? $quote['methods'][0]['id'] : ''),
					'module' => $shippingModule
				);
			}
			else {
				$rShipping = array(
					'title' => '',
					'cost' => '',
					'id' => '',
					'module' => $shippingModule
				);
			}

			if (isset($_POST['days_before'])){
				$rShipping['days_before'] = $ReservationInfo['days_before'];
			}

			if (isset($_POST['days_after'])){
				$rShipping['days_after'] = $ReservationInfo['days_after'];
			}
		}

		$CartProductData['reservationInfo'] = $ReservationInfo;

		$pricing = $this->figureProductPricing($ReservationInfo);
		if (!empty($pricing)){
			$CartProductData['price'] = $pricing['price'];
			$CartProductData['reservationInfo']['deposit_amount'] = $this->getDepositAmount();
			if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_DATE_SELECTION') == 'Using calendar after browsing products and clicking Reserve'){
				$CartProductData['final_price'] = $pricing['price'];
			}
			else {
				$CartProductData['final_price'] = $pricing['price']; //+ $pInfo['reservationInfo']['deposit_amount'];
			}
		}

		//EventManager::notify('ReservationProcessAddToCart', &$CartProductData['reservationInfo']);
		//EventManager::notify('PurchaseTypeAddToCart', $this->getCode(), &$CartProductData, $this->pprInfo);
	}

	public function allowAddToCart(&$CartProductData){
		global $ShoppingCart, $messageStack;

		$allowed = true;
		$EnabledShipping = $this->getEnabledShippingMethods();
		$ShippingIsNone = $this->shippingIsNone();
		$ShippingIsStore = $this->shippingIsStore();

		foreach($ShoppingCart->getProducts() as $CartProduct){
			$ProductType = $CartProduct->getProductClass()->getProductTypeClass();
			if (method_exists($ProductType, 'getPurchaseType')){
				if ($ProductType->getPurchaseType()->getCode() == $this->getCode()){
					$ReservationInfo = $CartProduct->getData('reservationInfo');
					if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_DIFFERENT_SHIPPING_METHODS') == 'False'){
						if ($ReservationInfo['shipping']['id'] != $CartProductData['reservationInfo']['shipping']['id']){
							$allowed = false;
							$messageStack->addSession('pageStack', 'You are not allowed to use this level of service with this product. Please choose another level of service', 'error');
						}elseif (is_array($EnabledShipping)){
							if (!in_array($CartProductData['reservationInfo']['shipping']['id'], $EnabledShipping)){
								if (!$ShippingIsNone && !$ShippingIsStore){
									$allowed = false;
									$messageStack->addSession('pageStack', 'You are not allowed to use this level of service with this product. Please choose another level of service', 'error');
								}
							}
						}

						if ($allowed === false){
							break;
						}
					}
				}
			}
		}
		return $allowed;
	}

	public function processUpdateCart(array &$pInfo) {

		$reservationInfo =& $pInfo['reservationInfo'];
		if (isset($pInfo['reservationInfo']['shipping']['module']) && isset($pInfo['reservationInfo']['shipping']['id'])){

			$shipping_modules = OrderShippingModules::getModule($pInfo['reservationInfo']['shipping']['module']);
			$product = new Product($this->getProductId());
			if (isset($pInfo['reservationInfo']['quantity'])){
				$total_weight = (int)$pInfo['reservationInfo']['quantity'] * $product->getWeight();
			}
			else {
				$total_weight = $product->getWeight();
			}
			$quotes = $shipping_modules->quote($pInfo['reservationInfo']['shipping']['id'], $total_weight);
			$reservationInfo['shipping'] = array(
				'title' => isset($quotes[0]['methods'][0]['title']) ? $quotes[0]['methods'][0]['title'] : $quotes['methods'][0]['title'],
				'cost' => isset($quotes[0]['methods'][0]['cost']) ? $quotes[0]['methods'][0]['cost'] : $quotes['methods'][0]['cost'],
				'id' => isset($quotes[0]['methods'][0]['id']) ? $quotes[0]['methods'][0]['id'] : $quotes['methods'][0]['id'],
				'module' => $pInfo['reservationInfo']['shipping']['module'],
				'days_before' => $pInfo['reservationInfo']['shipping']['days_before'],
				'days_after' => $pInfo['reservationInfo']['shipping']['days_after']
			);
		}

		$pInfo['quantity'] = $reservationInfo['quantity'];

		$pricing = $this->figureProductPricing($pInfo['reservationInfo']);

		if (isset($pricing)){
			$pInfo['price'] = $pricing['price'];
			$pInfo['reservationInfo']['deposit_amount'] = $this->getDepositAmount();
			if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_DATE_SELECTION') != 'Using calendar after browsing products and clicking Reserve'){
				$pInfo['final_price'] = $pricing['price']; //+ $pInfo['reservationInfo']['deposit_amount'];
			}
			else {
				$pInfo['final_price'] = $pricing['price'];
			}
		}
	}

	public function onInsertOrderedProduct(ShoppingCartProduct $cartProduct, $orderId, OrdersProducts &$orderedProduct, &$products_ordered) {
		global $currencies, $onePageCheckout, $appExtension;
		$resInfo = $cartProduct->getInfo('reservationInfo');
		$pID = (int)$cartProduct->getIdString();

		if (!isset($resInfo['insurance'])){
			$resInfo['insurance'] = 0;
		}

		$insurance = $resInfo['insurance'];
		$eventName = '';
		$eventDate = '';
		if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_EVENTS') == 'True'){
			$eventName = $resInfo['event_name'];
			$eventDate = $resInfo['event_date'];
			if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_GATES') == 'True'){
				$eventGate = $resInfo['event_gate'];
			}
		}
		else {
			$eventName = '';
			if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_GATES') == 'True'){
				$eventGate = '';
			}
			$eventDate = '0000-00-00 00:00:00';
		}
		$semesterName = (isset($resInfo['semester_name']) ? $resInfo['semester_name'] : '');
		$terms = '<p>Terms and conditions:</p><br/>';
		if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_SAVE_TERMS') == 'True'){
			$infoPages = $appExtension->getExtension('infoPages');
			$termInfoPage = $infoPages->getInfoPage('conditions');
			$terms .= $termInfoPage['PagesDescription'][Session::get('languages_id')]['pages_html_text'];
			if (sysConfig::get('TERMS_INITIALS') == 'true' && Session::exists('agreed_terms')){
				$terms .= '<br/>Initials: ' . Session::get('agreed_terms');
			}
		}

		$trackMethod = $this->getTrackMethod();

		$Reservations =& $orderedProduct->OrdersProductsReservation;
		$rCount = 0;
		$excludedBarcode = array();
		$excludedQuantity = array();

		for($count = 0; $count < $resInfo['quantity']; $count++){
			$Reservations[$rCount]->start_date = $resInfo['start_date']->format(DATE_TIMESTAMP);
			$Reservations[$rCount]->end_date = $resInfo['end_date']->format(DATE_TIMESTAMP);
			$Reservations[$rCount]->insurance = $insurance;
			$Reservations[$rCount]->event_name = $eventName;
			if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_GATES') == 'True'){
				$Reservations[$rCount]->event_gate = $eventGate;
			}
			$Reservations[$rCount]->semester_name = $semesterName;
			$Reservations[$rCount]->event_date = $eventDate;
			$Reservations[$rCount]->track_method = $trackMethod;
			$Reservations[$rCount]->rental_state = 'reserved';
			if (isset($resInfo['shipping']['id']) && !empty($resInfo['shipping']['id'])){
				$Reservations[$rCount]->shipping_method_title = $resInfo['shipping']['title'];
				$Reservations[$rCount]->shipping_method = $resInfo['shipping']['id'];
				$Reservations[$rCount]->shipping_days_before = $resInfo['shipping']['days_before'];
				$Reservations[$rCount]->shipping_days_after = $resInfo['shipping']['days_after'];
				$Reservations[$rCount]->shipping_cost = $resInfo['shipping']['cost'];
			}

			if ($trackMethod == 'barcode'){
				$Reservations[$rCount]->barcode_id = $this->getAvailableBarcode($cartProduct, $this->getInventoryItems(), $excludedBarcode);
				$orderedProduct->Barcodes[$count]->barcode_id = $Reservations[$rCount]->barcode_id;
				$excludedBarcode[] = $Reservations[$rCount]->barcode_id;
				$Reservations[$rCount]->ProductsInventoryBarcodes->status = 'R';
			}
			elseif ($trackMethod == 'quantity'){
				$Reservations[$rCount]->quantity_id = $this->getAvailableQuantity($cartProduct, $this->getInventoryItems(), $excludedQuantity);
				$excludedQuantity[] = $Reservations[$rCount]->quantity_id;
				$Reservations[$rCount]->ProductsInventoryQuantity->available -= 1;
				$Reservations[$rCount]->ProductsInventoryQuantity->reserved += 1;
			}
			EventManager::notify('ReservationOnInsertOrderedProduct', $Reservations[$rCount], &$cartProduct);

			$rCount++;
		}
		if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_EVENTS') == 'False'){
			if (!isset($resInfo['semester_name']) || $resInfo['semester_name'] == ''){
				$products_ordered .= 'Reservation Info' .
					"\n\t" . 'Start Date: ' . $resInfo['start_date']->format(sysLanguage::getDateFormat('long')) .
					"\n\t" . 'End Date: ' . $resInfo['end_date']->format(sysLanguage::getDateFormat('long'));
			}
			else {
				$products_ordered .= 'Reservation Info' .
					"\n\t" . 'Semester Name: ' . $resInfo['semester_name'];
				;
			}
		}
		else {
			$products_ordered .= 'Reservation Info' .
				"\n\t" . 'Event Date: ' . $resInfo['event_date']->format(sysLanguage::getDateFormat('long')) .
				"\n\t" . 'Event Name: ' . $resInfo['event_name'];
			if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_GATES') == 'True'){
				$products_ordered .= "\n\t" . 'Event Gate: ' . $resInfo['event_gate'];
			}
		}

		if (isset($resInfo['shipping']) && !empty($resInfo['shipping']['title'])){
			$products_ordered .= "\n\t" . 'Shipping Method: ' . $resInfo['shipping']['title'] . ' (' . $currencies->format($resInfo['shipping']['cost']) . ')';
		}
		$products_ordered .= "\n\t" . 'Insurance: ' . $currencies->format($resInfo['insurance']);
		$products_ordered .= "\n";
		EventManager::notify('ReservationAppendOrderedProductsString', &$products_ordered, &$cartProduct);

		$orderedProduct->Orders->terms = $terms;
		$orderedProduct->purchase_type = $this->getCode();
		$orderedProduct->save();
	}

/*
 * Get Available Barcode Function
 */

	public function getPurchaseHtml($key) {
		global $currencies;
		$return = null;
		switch($key){
			case 'product_info':
				if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_SHOW_CALENDAR_PRODUCT_INFO') == 'False'){

					$priceTableHtml = '';
					//if ($canReserveDaily || $canReserveWeekly || $canReserveMonthly || $canReserve6Months || $canReserve1Year || $canReserve3Years || $canReserveHourly || $canReserveTwoHours || $canReserveFourHours){
					$priceTable = htmlBase::newElement('table')
						->setCellPadding(3)
						->setCellSpacing(0)
						->attr('align', 'center');

					foreach(PurchaseType_reservation_utilities::getRentalPricing($this->getPayPerRentalId()) as $iPrices){
						$priceHolder = htmlBase::newElement('span')
							->css(array(
							'font-size' => '1.3em',
							'font-weight' => 'bold'
						))
							->html($this->displayReservePrice($iPrices['price']));

						$perHolder = htmlBase::newElement('span')
							->css(array(
							'white-space' => 'nowrap',
							'font-size' => '1.1em',
							'font-weight' => 'bold'
						))
							->html($iPrices['PricePayPerRentalPerProductsDescription'][0]['price_per_rental_per_products_name']);

						$priceTable->addBodyRow(array(
							'columns' => array(
								array('addCls' => 'main', 'align' => 'right', 'text' => $priceHolder->draw()),
								array('addCls' => 'main', 'align' => 'left', 'text' => $perHolder->draw())
							)
						));
					}

					if ($this->getDepositAmount() > 0){
						$priceHolder = htmlBase::newElement('span')
							->css(array(
							'font-size' => '1.1em',
							'font-weight' => 'bold'
						))
							->html($currencies->format($this->getDepositAmount()));

						$infoIcon = htmlBase::newElement('icon')
							->setType('info')
							->attr('onclick', 'popupWindow(\'' . itw_app_link('appExt=infoPages&dialog=true', 'show_page', 'ppr_deposit_info') . '\',400,300);')
							->css(array(
							'display' => 'inline-block',
							'cursor' => 'pointer'
						));

						$perHolder = htmlBase::newElement('span')
							->css(array(
							'white-space' => 'nowrap',
							'font-size' => '1.0em',
							'font-weight' => 'bold'
						))
							->html(' - Deposit ' . $infoIcon->draw());

						$priceTable->addBodyRow(array(
							'columns' => array(
								array('addCls' => 'main', 'align' => 'right', 'text' => $priceHolder->draw()),
								array('addCls' => 'main', 'align' => 'left', 'text' => $perHolder->draw())
							)
						));
					}

					if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_SHOW_PRICES_DATES_BEFORE') == 'True' || sysConfig::get('EXTENSION_PAY_PER_RENTALS_DATE_SELECTION') == 'Using calendar after browsing products and clicking Reserve'){
						$priceTableHtmlPrices = $priceTable->draw();
					}
					else {
						$priceTableHtmlPrices = '';
					}
					//}

					if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_DATE_SELECTION') == 'Using calendar after browsing products and clicking Reserve'){
						$button = htmlBase::newElement('button')
							->setType('submit')
							->setName('reserve_now')
							->setText(sysLanguage::get('TEXT_BUTTON_PAY_PER_RENTAL'));

						if ($this->hasInventory() === false){
							$button->disable();
						}

						$link = itw_app_link('appExt=payPerRentals&products_id=' . $_GET['products_id'], 'build_reservation', 'default');

						$return = array(
							'form_action' => $link,
							'purchase_type' => $this,
							'allowQty' => false,
							'header' => $this->getTitle(),
							'content' => $priceTableHtmlPrices,
							'button' => $button
						);
					}
					else {
						$priceTable = htmlBase::newElement('table')
							->setCellPadding(3)
							->setCellSpacing(0)
							->attr('align', 'center');
						if (Session::exists('isppr_inventory_pickup') === false && Session::exists('isppr_city') === true && Session::get('isppr_city') != ''){
							$Qproducts = Doctrine_Query::create()
								->from('ProductsInventoryBarcodes b')
								->leftJoin('b.ProductsInventory i')
								->leftJoin('i.Products p')
								->leftJoin('b.ProductsInventoryBarcodesToInventoryCenters b2c')
								->leftJoin('b2c.ProductsInventoryCenters ic');

							$Qproducts->where('p.products_id=?', $_GET['products_id']);
							$Qproducts->andWhere('i.use_center = ?', '1');

							if (Session::exists('isppr_continent') === true && Session::get('isppr_continent') != ''){
								$Qproducts->andWhere('ic.inventory_center_continent = ?', Session::get('isppr_continent'));
							}
							if (Session::exists('isppr_country') === true && Session::get('isppr_country') != ''){
								$Qproducts->andWhere('ic.inventory_center_country = ?', Session::get('isppr_country'));
							}
							if (Session::exists('isppr_state') === true && Session::get('isppr_state') != ''){
								$Qproducts->andWhere('ic.inventory_center_state = ?', Session::get('isppr_state'));
							}
							if (Session::exists('isppr_city') === true && Session::get('isppr_city') != ''){
								$Qproducts->andWhere('ic.inventory_center_city = ?', Session::get('isppr_city'));
							}
							$Qproducts = $Qproducts->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
							$invCenter = -1;
							$isdouble = false;
							foreach($Qproducts as $iProduct){
								if ($invCenter == -1){
									$invCenter = $iProduct['ProductsInventoryBarcodesToInventoryCenters']['ProductsInventoryCenters']['inventory_center_id'];
								}
								elseif ($iProduct['ProductsInventoryBarcodesToInventoryCenters']['ProductsInventoryCenters']['inventory_center_id'] != $invCenter) {
									$isdouble = true;
									break;
								}
							}

							if (!$isdouble){
								Session::set('isppr_inventory_pickup', $Qproducts[0]['ProductsInventoryBarcodesToInventoryCenters']['ProductsInventoryCenters']['inventory_center_id']);
								$deleteS = true;
							}
						}

						if (Session::exists('isppr_selected') && Session::get('isppr_selected') == true){
							$start_date = '';
							$end_date = '';
							$event_date = '';
							$event_name = '';
							$event_gate = '';
							$pickup = '';
							$dropoff = '';
							$days_before = '';
							$days_after = '';
							if (Session::exists('isppr_shipping_days_before')){
								$days_before = Session::get('isppr_shipping_days_before');
							}
							if (Session::exists('isppr_shipping_days_after')){
								$days_after = Session::get('isppr_shipping_days_after');
							}
							if (Session::exists('isppr_date_start')){
								$start_date = Session::get('isppr_date_start');
							}
							if (Session::exists('isppr_date_end')){
								$end_date = Session::get('isppr_date_end');
							}
							if (Session::exists('isppr_event_date')){
								$event_date = Session::get('isppr_event_date');
							}
							if (Session::exists('isppr_event_name')){
								$event_name = Session::get('isppr_event_name');
							}

							if (Session::exists('isppr_event_gate')){
								$event_gate = Session::get('isppr_event_gate');
							}

							if (Session::exists('isppr_inventory_pickup')){
								$pickup = Session::get('isppr_inventory_pickup');
							}
							else {
								//check the inventory center for this one...if multiple output a text to show...select specific//use continent, city for comparison
							}
							if (Session::exists('isppr_inventory_dropoff')){
								$dropoff = Session::get('isppr_inventory_dropoff');
							}
							if (Session::exists('isppr_product_qty')){
								$qtyVal = (int)Session::get('isppr_product_qty');
							}
							else {
								$qtyVal = 1;
							}

							$payPerRentalButton = htmlBase::newElement('button')
								->setType('submit')
								->setText(sysLanguage::get('TEXT_BUTTON_RESERVE'))
								->addClass('inCart')
								->setName('add_reservation_product');

						if ($this->hasInventory()){

							$isR = false;
							$isRV = '';
							if (Session::exists('isppr_shipping_method')) {

								if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_UPS_RESERVATION') == 'False'){
									if (Session::exists('isppr_shipping_cost')) {
										$ship_cost = (float) Session::get('isppr_shipping_cost');
									}

								}else{
									if(isset($_POST['rental_shipping'])){
										$isR = true;
										$isRV = $_POST['rental_shipping'];
									}
									$_POST['rental_shipping'] = 'upsreservation_'. Session::get('isppr_shipping_method');
								}
							}else{
								//here i should check for use_ship
								if(sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_SHIP') == 'True'){
									$payPerRentalButton->disable()
									->addClass('no_shipping');
								}
							}
							$thePrice = 0;
							$rInfo = '';
							$price = $this->getReservationPrice($start_date, $end_date, $rInfo,'',(sysConfig::get('EXTENSION_PAY_PER_RENTALS_INSURE_ALL_PRODUCTS_AUTO') == 'True'));
							$thePrice += $price['price'];
							if(Session::exists('isppr_event_multiple_dates')){
								$thePrice = 0;
								$datesArr = Session::get('isppr_event_multiple_dates');

								foreach($datesArr as $iDate){
									$price = $this->getReservationPrice($iDate, $iDate, $rInfo,'',(sysConfig::get('EXTENSION_PAY_PER_RENTALS_INSURE_ALL_PRODUCTS_AUTO') == 'True'));
									$thePrice += $price['price'];
								}

							}

							$pricing = $currencies->format($qtyVal*$thePrice+ $ship_cost);
							if(!$isR){
								unset($_POST['rental_shipping']);
							}else{
								$_POST['rental_shipping'] = $isRV;
							}
							$pageForm =  htmlBase::newElement('div');

							if (isset($start_date)) {
								$htmlStartDate = htmlBase::newElement('input')
								->setType('hidden')
								->setName('start_date')
								->setValue($start_date);
							}

							if (isset($days_before)) {
								$htmlDaysBefore = htmlBase::newElement('input')
									->setType('hidden')
									->setName('days_before')
									->setValue($days_before);
							}

							if (isset($days_after)) {
								$htmlDaysAfter = htmlBase::newElement('input')
									->setType('hidden')
									->setName('days_after')
									->setValue($days_after);
							}

							if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_EVENTS') == 'True'){
								$htmlEventDate = htmlBase::newElement('input')
								->setType('hidden')
								->setName('event_date')
								->setValue($event_date);
								$htmlEventName = htmlBase::newElement('input')
								->setType('hidden')
								->setName('event_name')
								->setValue($event_name);
								if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_GATES') == 'True'){
									$htmlEventGate = htmlBase::newElement('input')
									->setType('hidden')
									->setName('event_gate')
									->setValue($event_gate);
								}
							}
							if (isset($pickup)) {
								$htmlPickup = htmlBase::newElement('input')
								->setType('hidden')
								->setName('pickup')
								->setValue($pickup);
							}
							if (isset($dropoff)) {
								$htmlDropoff = htmlBase::newElement('input')
								->setType('hidden')
								->setName('dropoff')
								->setValue($dropoff);
							}
							$htmlRentalQty = htmlBase::newElement('input');
							if(sysConfig::get('EXTENSION_PAY_PER_RENTALS_SHOW_QTY_LISTING') == 'False'){
								$htmlRentalQty->setType('hidden');
							}else{
								$htmlRentalQty->attr('size','3');
							}
							$htmlRentalQty->setName('rental_qty')
							->setValue($qtyVal);
							if(sysConfig::get('EXTENSION_PAY_PER_RENTALS_INSURE_ALL_PRODUCTS_AUTO') == 'True'){
								$htmlHasInsurance = htmlBase::newElement('input')
									->setType('hidden')
									->setName('hasInsurance')
									->setValue('1');
								$pageForm->append($htmlHasInsurance);
							}
							$htmlProductsId = htmlBase::newElement('input')
							->setType('hidden')
							->setName('products_id')
							->setValue($this->getData('products_id'));
							if (isset($end_date)) {
								$htmlEndDate = htmlBase::newElement('input')
								->setType('hidden')
								->setName('end_date')
								->setValue($end_date);
							}

							if (isset($htmlStartDate)) {
								$pageForm->append($htmlStartDate);
							}
							if (isset($htmlEndDate)) {
								$pageForm->append($htmlEndDate);
							}
							if (isset($htmlDaysBefore)) {
								$pageForm->append($htmlDaysBefore);
							}

							if (isset($htmlDaysAfter)) {
								$pageForm->append($htmlDaysAfter);
							}
							if (isset($htmlPickup)) {
								$pageForm->append($htmlPickup);
							}
							if (isset($htmlDropoff)) {
								$pageForm->append($htmlDropoff);
							}
							$pageForm->append($htmlRentalQty);
							$pageForm->append($htmlProductsId);

							if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_EVENTS') == 'True'){
								$pageForm->append($htmlEventDate)
								 ->append($htmlEventName);
								if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_GATES') == 'True'){
									$pageForm->append($htmlEventGate);
								}
							}

							if (Session::exists('isppr_shipping_method')){
								$htmlShippingDays = htmlBase::newElement('input')
								->setType('hidden')
								->setName('rental_shipping');
								if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_UPS_RESERVATION') == 'False'){
									$htmlShippingDays->setValue("zonereservation_" . Session::get('isppr_shipping_method'));
								}else{
									$htmlShippingDays->setValue("upsreservation_" . Session::get('isppr_shipping_method'));
								}
								$pageForm->append($htmlShippingDays);
							}

								$priceHolder = htmlBase::newElement('span')
									->css(array(
										'font-size' => '1.3em',
										'font-weight' => 'bold'
									))
									->html($pricing);

								$perHolder = htmlBase::newElement('span')
									->css(array(
										'white-space' => 'nowrap',
										'font-size' => '1.1em',
										'font-weight' => 'bold'
									))
									->html('Price per selected period');
							if(sysConfig::get('EXTENSION_PAY_PER_RENTALS_SHOW_PRICE_SELECTED_PERIOD_PRODUCT_INFO') == 'True'){
								$priceTable->addBodyRow(array(
									'columns' => array(
										array('addCls' => 'main', 'align' => 'right', 'text' => $priceHolder->draw()),
										array('addCls' => 'main', 'align' => 'left', 'text' => $perHolder->draw())
									)
								));
								$pageForm->append($priceTable);
							}
								$priceTableHtml = $pageForm->draw();
							$script = '';
						if(sysConfig::get('EXTENSION_PAY_PER_RENTALS_PRODUCT_INFO_DATES') == 'True'){
							ob_start();
							?>
						<script type="text/javascript">
							function nobeforeDays(date){
								today = new Date();
								if(today.getTime() <= date.getTime() - (1000 * 60 * 60 * 24 * <?php echo $datePadding;?> - (24 - date.getHours()) * 1000 * 60 * 60)){
									return [true,''];
								}else{
									return [false,''];
								}
							}
							function makeDatePicker(pickerID){
								var minRentalDays = <?php
                                if(sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_GLOBAL_MIN_RENTAL_DAYS') == 'True'){
									echo (int)sysConfig::get('EXTENSION_PAY_PER_RENTALS_MIN_RENTAL_DAYS');
									$minDays = (int)sysConfig::get('EXTENSION_PAY_PER_RENTALS_MIN_RENTAL_DAYS');
								}else{
									$minDays = 0;
									echo '0';
								}
									if(Session::exists('button_text')){
										$butText = Session::get('button_text');
									}else{
										$butText = '';
									}
									?>;
								var selectedDateId = null;
								var startSelectedDate;

								var dates = $(pickerID+' .dstart,'+pickerID+' .dend').datepicker({
									dateFormat: '<?php echo getJsDateFormat(); ?>',
									changeMonth: true,
									beforeShowDay: nobeforeDays,
									onSelect: function(selectedDate) {

										var option = this.id == "dstart" ? "minDate" : "maxDate";
										if($(this).hasClass('dstart')){
											myid = "dstart";
											option = "minDate";
										}else{
											myid = "dend";
											option = "maxDate";
										}
										var instance = $(this).data("datepicker");
										var date = $.datepicker.parseDate(instance.settings.dateFormat || $.datepicker._defaults.dateFormat, selectedDate, instance.settings);

										var dateC = new Date('<?php echo (Session::exists('isppr_curDate')?Session::get('isppr_curDate'):'01-01-2011');?>');
										if(date.getTime() == dateC.getTime()){
											if(myid == "dstart"){
												$(this).closest('form').find('.hstart').html('<?php echo (Session::exists('isppr_selectOptionscurdays')?Session::get('isppr_selectOptionscurdays'):'1');?>');
											}else{
												$(this).closest('form').find('.hend').html('<?php echo (Session::exists('isppr_selectOptionscurdaye')?Session::get('isppr_selectOptionscurdaye'):'1');?>');
											}
										}else{
											if(myid == "dstart"){
												$(this).closest('form').find('.hstart').html('<?php echo (Session::exists('isppr_selectOptionsnormaldays')?Session::get('isppr_selectOptionsnormaldays'):'1');?>');
											}else{
												$(this).closest('form').find('.hend').html('<?php echo (Session::exists('isppr_selectOptionsnormaldaye')?Session::get('isppr_selectOptionsnormaldaye'):'1');?>');
											}
										}


										if(myid == "dstart"){
											var days = "0";
											if ($(this).closest('form').find('select.pickupz option:selected').attr('days')){
												days = $(this).closest('form').find('select.pickupz option:selected').attr('days');
											}
											//startSelectedDate = new Date(selectedDate);
											dateFut = new Date(date.setDate(date.getDate() + parseInt(days)));
											dates.not(this).datepicker("option", option, dateFut);
										}
										f = true;
										if(myid == "dend"){
											datest = new Date(selectedDate);
											if ($(this).closest('form').find('.dstart').val() != ''){
												startSelectedDate = new Date($(this).closest('form').find('.dstart').val());
												if (datest.getTime() - startSelectedDate.getTime() < minRentalDays *24*60*60*1000){
													alert('<?php echo sprintf(sysLanguage::get('EXTENSION_PAY_PER_RENTALS_ERROR_MIN_DAYS'), $minDays);?>');
													$(this).val('');
													f = false;
												}
											}else{
												f = false;
											}
										}

										if (selectedDateId != this.id && selectedDateId != null && f){
											selectedDateId = null;
										}
										if (f){
											selectedDateId = this.id;
										}

									}
								});
							}
							$(document).ready(function (){
								$('.no_dates_selected').each(function(){$(this).click(function(){

									$( '<div id="dialog-mesage" title="Choose Dates"><input class="tField" name="tField" ><div class="destBD"><span class="start_text">Start: </span><input class="picker dstart" name="dstart" ></div><div class="destBD"><span class="end_text">End: </span><input class="picker dend" name="dend" ></div><?php echo sysConfig::get('EXTENSION_PAY_PER_RENTALS_INFOBOX_CONTENT');?></div>' ).dialog({
										modal: false,
										autoOpen: true,
										open: function (e, ui){
											makeDatePicker('#dialog-mesage');
											$(this).find('.tField').hide();
										},
										buttons: {
											Submit: function() {

												$('.dstart').val($(this).find('.dstart').val());
												$('.dend').val($(this).find('.dend').val());
												$('.rentbbut').trigger('click');
												$(this).dialog( "close" );
											}
										}
									});

									return false;
								})});
								$('.no_inventory').each(function(){$(this).click(function(){

									$( '<div id="dialog-mesage" title="No Inventory"><span style="color:red;font-size:18px;"><?php echo sysLanguage::get('EXTENSION_PAY_PER_RENTALS_ERROR_NO_INVENTORY_FOR_SELECTED_DATES');?></span></div>' ).dialog({
										modal: true,
										buttons: {
											Ok: function() {
												$( this ).dialog( "close" );
											}
										}
									});

									return false;
								})});
							});
						</script>
						<?php
	  					$script = ob_get_contents();
							ob_end_clean();
						}
								$return = array(
									'form_action' => itw_app_link('appExt=payPerRentals&products_id=' . $_GET['products_id'], 'build_reservation', 'default'),
									'purchase_type' => $this->getCode(),
									'allowQty' => false,
									'header' => $this->getTitle(),
									'content' => $priceTableHtmlPrices . $priceTableHtml . $script,
									'button' => $payPerRentalButton
								);
							}
						}else{
						$payPerRentalButton = htmlBase::newElement('button')
						->setType('submit')
						->setText(sysLanguage::get('TEXT_BUTTON_RESERVE'));

						if ($this->hasInventory() === false && Session::exists('isppr_selected') && Session::get('isppr_selected') == true){
							$payPerRentalButton->addClass('no_inventory');
							$payPerRentalButton->setText(sysLanguage::get('TEXT_BUTTON_RESERVE_OUT_OF_STOCK'));
						}else{
							$payPerRentalButton->addClass('no_dates_selected');
						}
						$script = '';
					if(sysConfig::get('EXTENSION_PAY_PER_RENTALS_PRODUCT_INFO_DATES') == 'True'){	
					    ob_start();
					    ?>
				<script type="text/javascript">
					function nobeforeDays(date){
						today = new Date();
						if(today.getTime() <= date.getTime() - (1000 * 60 * 60 * 24 * <?php echo $datePadding;?> - (24 - date.getHours()) * 1000 * 60 * 60)){
							return [true,''];
						}else{
							return [false,''];
						}
					}
					function makeDatePicker(pickerID){
						var minRentalDays = <?php
                        if(sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_GLOBAL_MIN_RENTAL_DAYS') == 'True'){
							echo (int)sysConfig::get('EXTENSION_PAY_PER_RENTALS_MIN_RENTAL_DAYS');
							$minDays = (int)sysConfig::get('EXTENSION_PAY_PER_RENTALS_MIN_RENTAL_DAYS');
						}else{
							$minDays = 0;
							echo '0';
						}
							if(Session::exists('button_text')){
								$butText = Session::get('button_text');
							}else{
								$butText = '';
							}
							?>;
						var selectedDateId = null;
						var startSelectedDate;

						var dates = $(pickerID+' .dstart,'+pickerID+' .dend').datepicker({
							dateFormat: '<?php echo getJsDateFormat(); ?>',
							changeMonth: true,
							beforeShowDay: nobeforeDays,
							onSelect: function(selectedDate) {

								var option = this.id == "dstart" ? "minDate" : "maxDate";
								if($(this).hasClass('dstart')){
									myid = "dstart";
									option = "minDate";
								}else{
									myid = "dend";
									option = "maxDate";
								}
								var instance = $(this).data("datepicker");
								var date = $.datepicker.parseDate(instance.settings.dateFormat || $.datepicker._defaults.dateFormat, selectedDate, instance.settings);

								var dateC = new Date('<?php echo (Session::exists('isppr_curDate')?Session::get('isppr_curDate'):'01-01-2011');?>');
								if(date.getTime() == dateC.getTime()){
									if(myid == "dstart"){
										$(this).closest('form').find('.hstart').html('<?php echo (Session::exists('isppr_selectOptionscurdays')?Session::get('isppr_selectOptionscurdays'):'1');?>');
									}else{
										$(this).closest('form').find('.hend').html('<?php echo (Session::exists('isppr_selectOptionscurdaye')?Session::get('isppr_selectOptionscurdaye'):'1');?>');
									}
								}else{
									if(myid == "dstart"){
										$(this).closest('form').find('.hstart').html('<?php echo (Session::exists('isppr_selectOptionsnormaldays')?Session::get('isppr_selectOptionsnormaldays'):'1');?>');
									}else{
										$(this).closest('form').find('.hend').html('<?php echo (Session::exists('isppr_selectOptionsnormaldaye')?Session::get('isppr_selectOptionsnormaldaye'):'1');?>');
									}
								}


								if(myid == "dstart"){
									var days = "0";
									if ($(this).closest('form').find('select.pickupz option:selected').attr('days')){
										days = $(this).closest('form').find('select.pickupz option:selected').attr('days');
									}
									//startSelectedDate = new Date(selectedDate);
									dateFut = new Date(date.setDate(date.getDate() + parseInt(days)));
									dates.not(this).datepicker("option", option, dateFut);
								}
								f = true;
								if(myid == "dend"){
									datest = new Date(selectedDate);
									if ($(this).closest('form').find('.dstart').val() != ''){
										startSelectedDate = new Date($(this).closest('form').find('.dstart').val());
										if (datest.getTime() - startSelectedDate.getTime() < minRentalDays *24*60*60*1000){
											alert('<?php echo sprintf(sysLanguage::get('EXTENSION_PAY_PER_RENTALS_ERROR_MIN_DAYS'), $minDays);?>');
											$(this).val('');
											f = false;
										}
									}else{
										f = false;
									}
								}

								if (selectedDateId != this.id && selectedDateId != null && f){
									selectedDateId = null;
								}
								if (f){
									selectedDateId = this.id;
								}

							}
						});
					}
					$(document).ready(function (){
						$('.no_dates_selected').each(function(){$(this).click(function(){
							$( '<div id="dialog-mesage" title="Choose Dates"><input class="tField" name="tField" ><div class="destBD"><span class="start_text">Start: </span><input class="picker dstart" name="dstart" ></div><div class="destBD"><span class="end_text">End: </span><input class="picker dend" name="dend" ></div><?php echo sysConfig::get('EXTENSION_PAY_PER_RENTALS_INFOBOX_CONTENT');?></div>' ).dialog({
								modal: false,
								autoOpen: true,
								open: function (e, ui){
									makeDatePicker('#dialog-mesage');
									$(this).find('.tField').hide();
								},
								buttons: {
									Submit: function() {

										$('.dstart').val($(this).find('.dstart').val());
										$('.dend').val($(this).find('.dend').val());
										$('.rentbbut').trigger('click');
										$(this).dialog( "close" );
									}
								}
							});

							return false;
						})});
						$('.no_inventory').each(function(){$(this).click(function(){

							$( '<div id="dialog-mesage" title="No Inventory"><span style="color:red;font-size:18px;"><?php echo sysLanguage::get('EXTENSION_PAY_PER_RENTALS_ERROR_NO_INVENTORY_FOR_SELECTED_DATES');?></span></div>' ).dialog({
								modal: true,
								buttons: {
									Ok: function() {
										$( this ).dialog( "close" );
									}
								}
							});

							return false;
						})});
					});
				</script>
					<?php
					$script = ob_get_contents();
					ob_end_clean();
					}
							$return = array(
								'form_action' => '#',
								'purchase_type' => $this,
								'allowQty' => false,
								'header' => $this->getTitle(),
							'content'       => $priceTableHtmlPrices . $script,
								'button' => $payPerRentalButton
							);
						}
					}
				}
				else {
					ob_start();
					require(sysConfig::getDirFsCatalog() . 'extensions/payPerRentals/catalog/base_app/build_reservation/pages/default.php');
					echo '<script type="text/javascript" src="' . sysConfig::getDirWsCatalog() . 'extensions/payPerRentals/catalog/base_app/build_reservation/javascript/default.js' . '"></script>';
					$pageHtml = ob_get_contents();
					ob_end_clean();
					$return = array(
						'form_action' => '',
						'purchase_type' => $this,
						'allowQty' => false,
						'header' => $this->getTitle(),
						'content' => $pageHtml,
						'button' => ''
					);
					//echo $pageHtml;
				}
				break;
		}
		return $return;
	}

	public function displayReservePrice($price) {
		global $currencies;
		return $currencies->display_price($price, $this->getTaxRate());
	}

	public function getPricingTable($includeShipping = false, $includeSelect = false, $includeButton = false) {
		global $currencies;
		$table = '';
		if ($this->hasInventory($this->getCode())){

			$table .= '<table cellpadding="0" cellspacing="0" border="0">';

			foreach(PurchaseType_reservation_utilities::getRentalPricing($this->getPayPerRentalId()) as $iPrices){
				$table .= '<tr>' .
					'<td class="main">' . $iPrices['PricePayPerRentalPerProductsDescription'][0]['price_per_rental_per_products_name'] . ': </td>' .
					'<td class="main">' . $this->displayReservePrice($iPrices['price']) . '</td>' .

					'</tr>';
			}

			$table .= '</table>';
		}
		return $table;
	}

	public function buildSemesters($semDates) {

		$QPeriods = Doctrine_Query::create()
			->from('ProductsPayPerPeriods')
			->where('products_id=?', $this->getProductId())
			->andWhere('price > 0')
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
		$table = '';
		if (count($QPeriods) > 0){
			ob_start();
			?>
		<table cellpadding="0" cellspacing="0" border="0">
			<tr>
				<td class="main" colspan="2">
					<?php
					$CalOrSemester = htmlBase::newElement('radio')
						->addGroup(array(
						'checked' => 1,
						'separator' => '<br />',
						'name' => 'cal_or_semester',
						'data' => array(
							array(
								'label' => sysLanguage::get('TEXT_USE_CALENDAR'),
								'labelPosition' => 'before',
								'addCls' => 'iscal',
								'value' => '1'
							),
							array(
								'label' => sysLanguage::get('TEXT_USE_SEMESTER'),
								'labelPosition' => 'before',
								'addCls' => 'issem',
								'value' => '0'
							)
						)
					));
					echo $CalOrSemester->draw();
					?>

				</td>
			</tr>
			<tr class="semRow">
				<td class="main" colspan="2">
					<?php
					$selectSem = htmlBase::newElement('selectbox')
						->setName('semester_name')
						->setLabel(sysLanguage::get('TEXT_SELECT_PERIOD'))
						->setLabelPosition('before')
						->attr('class', 'selected_period');
					$selectSem->addOption('', sysLanguage::get('TEXT_SELECT_SEMESTER'));

					foreach($semDates as $sDate){

						$attr = array(
							array(
								'name' => 'start_date',
								'value' => $sDate['start_date']
							),
							array(
								'name' => 'end_date',
								'value' => $sDate['end_date']
							)
						);
						$selectSem->addOptionWithAttributes($sDate['period_name'], $sDate['period_name'], $attr);
					}
					$moreInfo = htmlBase::newElement('a')
						->attr('id', 'moreInfoSem')
						->html(sysLanguage::get('TEXT_MORE_INFO_SEM'));
					echo $selectSem->draw();//.$moreInfo;
					?>

				</td>
			</tr>
		</table>
		<?php
			$table = ob_get_contents();
			ob_end_clean();
		}
		return $table;
	}

	public function buildShippingTable() {
		global $userAccount, $ShoppingCart, $App;

		if ($this->getShipping() === false) {
			return;
		}

		if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_UPS_RESERVATION') == 'False'){
			$Module = OrderShippingModules::getModule($this->shipModuleCode);
			$dontShow = '';
			$selectedMethod = '';
			$weight = 0;
			if ($Module && $Module->getType() == 'Order' && $App->getEnv() == 'catalog'){
				foreach($ShoppingCart->getProducts() as $cartProduct){
					if ($cartProduct->hasInfo('reservationInfo') === true){
						$weight += $cartProduct->getWeight();
					}
				}
			}

			$product = new Product($this->getProductId());
			if (isset($_POST['rental_qty'])){
				$prod_weight = (int)$_POST['rental_qty'] * $product->getWeight();
			}
			else {
				$prod_weight = $product->getWeight();
			}

			$weight += $prod_weight;

			$quotes = ($Module ? array($Module->quote($selectedMethod, $weight)) : array());
			$table = '<div class="shippingTable" style="display:' . $dontShow . '">';
			if ($quotes && sizeof($quotes[0]['methods']) > 0){
				$table .= sysLanguage::get('PPR_SHIPPING_SELECT') . $this->parseQuotes($quotes);
			}
			$table .= '</div>';
		}
		elseif (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_UPS_RESERVATION') == 'True' && sysConfig::get('EXTENSION_PAY_PER_RENTALS_CHECK_GOOGLE_ZONES_BEFORE') == 'False') {
			$table = '<div class="shippingUPS"><table cellpadding="0" cellspacing="0" border="0">';

			$table .= '<tr id="shipMethods">' .
				'<td class="main">' . sysLanguage::get('PPR_SHIPPING_SELECT') . ':</td>' .
				'<td class="main" id="rowquotes">' . '</td>' .
				'</tr>';

			$checkAddressButton = htmlBase::newElement('button')
				->usePreset('continue')
				->setId('getQuotes')
				->setName('getQuotes')
				->setText(sysLanguage::get('TEXT_BUTTON_GET_QUOTES'));

			$getQuotes = htmlBase::newElement('div');

			$checkAddressBox = htmlBase::newElement('div');
			if ($App->getEnv() == 'catalog'){
				$addressBook = $userAccount->plugins['addressBook'];
				$shippingAddress = $addressBook->getAddress('delivery');
			}
			else {
				global $Editor;
				$shippingAddress = $Editor->AddressManager->getAddress('delivery')->toArray();
			}

			$checkAddressBox->html('<table border="0" cellspacing="2" cellpadding="2" id="fullAddress">' .
				'<tr>' .
				'<td>' . sysLanguage::get('ENTRY_STREET_ADDRESS') . '</td>' .
				'<td>' . tep_draw_input_field('street_address', $shippingAddress['entry_street_address'], 'id="street_address"') . '</td>' .
				'</tr>' .
				'<tr>' .
				'<td>' . sysLanguage::get('ENTRY_CITY') . '</td>' .
				'<td>' . tep_draw_input_field('city', $shippingAddress['entry_city'], 'id="city"') . '</td>' .
				'</tr>' .
				'<tr>' .
				'<td>' . sysLanguage::get('ENTRY_STATE') . '</td>' .
				'<td id="stateCol">' . tep_draw_input_field('state', $shippingAddress['entry_state'], 'id="state"') . '</td>' .
				'</tr>' .
				'<tr>' .
				'<td>' . sysLanguage::get('ENTRY_POST_CODE') . '</td>' .
				'<td>' . tep_draw_input_field('postcode', $shippingAddress['entry_postcode'], 'id="postcode1"') . '</td>' .
				'</tr>' .
				'<tr>' .
				'<td>' . sysLanguage::get('ENTRY_COUNTRY') . '</td>' .
				'<td>' . tep_get_country_list('country', isset($shippingAddress['entry_country']) ? $shippingAddress['entry_country'] : sysConfig::get('STORE_COUNTRY'), 'id="countryDrop"') . '</td>' .
				'</tr>' .
				'</table>');
			$checkAddressBoxZip = htmlBase::newElement('div');
			$checkAddressBoxZip->html('<table border="0" cellspacing="2" cellpadding="2" id="zipAddress">' .
				'<tr>' .
				'<td>' . sysLanguage::get('ENTRY_POST_CODE') . '</td>' .
				'<td>' . tep_draw_input_field('postcode', $shippingAddress['entry_postcode'], 'id="postcode2"') . '</td>' .
				'</tr>' .
				'</table>');
			$hiddenField = htmlBase::newElement('input')
				->setType('hidden')
				->setId('pid')
				->setValue($_GET['products_id']);

			$getQuotes->append($checkAddressBox)
				->append($checkAddressBoxZip)
				->append($hiddenField)
				->append($checkAddressButton);

			$table .= '<tr style="text-align:center">' .
				'<td colspan="2" class="main" style="text-align:center">' . sysLanguage::get('TEXT_BEFORE_QUOTES') . $getQuotes->draw() . '</td>' .
				'</tr>';
			$table .= '</table></div>';
		}

		return $table;
	}

	public function parseQuotes($quotes) {
		global $currencies, $userAccount, $App;
		$table = '';
		if ($this->getShipping() !== false){
			$table = '<table cellpadding="0" cellspacing="0" border="0">';

			$newMethods = array();

			foreach($quotes[0]['methods'] as $mInfo){
				if (!in_array($mInfo['id'], $this->getShippingArray())){
					continue;
				}
				$newMethods[] = $mInfo;
			}
			$quotes[0]['methods'] = $newMethods;
			$this->getMaxShippingDays = -1;
			for($i = 0, $n = sizeof($quotes); $i < $n; $i++){
				$table .= '<tr>' .
					'<td><table border="0" width="100%" cellspacing="0" cellpadding="2">' .

					'<tr>' .
					'<td class="main" colspan="3"><b>' . $quotes[$i]['module'] . '</b>&nbsp;' . (isset($quotes[$i]['icon']) && ($quotes[$i]['icon'] != '') ? $quotes[$i]['icon'] : '') . '</td>' .
					'</tr>';

				for($j = 0, $n2 = sizeof($quotes[$i]['methods']); $j < $n2; $j++){

					if ($quotes[$i]['methods'][$j]['default'] == 1){
						$checked = true;
					}
					else {
						$checked = false;
					}

					if ($this->getMaxShippingDays < $quotes[$i]['methods'][$j]['days_before']){
						$this->getMaxShippingDays = (int)$quotes[$i]['methods'][$j]['days_before'];
					}
					if ($this->getMaxShippingDays < $quotes[$i]['methods'][$j]['days_after']){
						$this->getMaxShippingDays = (int)$quotes[$i]['methods'][$j]['days_after'];
					}

					$minRental = '';
					$minRentalMessage = '';
					if (!empty($quotes[$i]['methods'][$j]['min_rental_number']) && $quotes[$i]['methods'][$j]['min_rental_number'] > 0){
						$minRentalPeriod1 = ReservationUtilities::getPeriodTime($quotes[$i]['methods'][$j]['min_rental_number'], $quotes[$i]['methods'][$j]['min_rental_type']) * 60 * 1000;
						$minRental = 'min_rental="' . $minRentalPeriod1 . '"';
						$minRentalMessage = '<div id="' . $minRentalPeriod1 . '" style="display:none;">' . sysLanguage::get('PPR_ERR_AT_LEAST') . ' ' . $quotes[$i]['methods'][$j]['min_rental_number'] . ' ' . ReservationUtilities::getPeriodType($quotes[$i]['methods'][$j]['min_rental_type']) . ' ' . sysLanguage::get('PPR_ERR_DAYS_RESERVED') . '</div>';
					}

					$table .= '<tr class="shipmethod row_' . $quotes[$i]['methods'][$j]['id'] . '">' .
						'<td class="main" width="75%">' . $quotes[$i]['methods'][$j]['title'] . '</td>';

					if (($n > 1) || ($n2 > 1)){
						//$radioShipping = tep_draw_radio_field('rental_shipping', $quotes[$i]['id'] . '_' . $quotes[$i]['methods'][$j]['id'], $checked, 'days_before="' . $quotes[$i]['methods'][$j]['days_before'] . '" days_after="' . $quotes[$i]['methods'][$j]['days_after'] . '"');
						$radioShipping = '<input type="radio" ' . (($checked == true) ? 'checked="checked"' : '') . ' name="rental_shipping" value="' . $quotes[$i]['id'] . '_' . $quotes[$i]['methods'][$j]['id'] . '" days_before="' . $quotes[$i]['methods'][$j]['days_before'] . '" days_after="' . $quotes[$i]['methods'][$j]['days_after'] . '" ' . $minRental . '>' . $minRentalMessage;

						$table .= '<td class="main" class="cost_' . $quotes[$i]['methods'][$j]['id'] . '">' . $currencies->format(tep_add_tax($quotes[$i]['methods'][$j]['showCost'], (isset($quotes[$i]['tax']) ? $quotes[$i]['tax'] : 0))) . '</td>' .
							'<td class="main" align="right">' . $radioShipping . '</td>';
					}
					else {
						$radioShipping = '<input type="radio" ' . (($checked == true) ? 'checked="checked"' : '') . ' name="rental_shipping" value="' . $quotes[$i]['id'] . '_' . $quotes[$i]['methods'][$j]['id'] . '" days_before="' . $quotes[$i]['methods'][$j]['days_before'] . '" days_after="' . $quotes[$i]['methods'][$j]['days_after'] . '" ' . $minRental . '>' . $minRentalMessage;
						$table .= '<td class="main" class="cost_' . $quotes[$i]['methods'][$j]['id'] . '">' . $currencies->format(tep_add_tax($quotes[$i]['methods'][$j]['showCost'], (isset($quotes[$i]['tax']) ? $quotes[$i]['tax'] : 0))) . '</td>' .
							'<td class="main" align="right">' . $radioShipping . '</td>';
					}

					$table .= '</tr>';
				}
				$table .= '</table></td>' .
					'</tr>';
			}

			if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_CHECK_GOOGLE_ZONES_BEFORE') == 'True' && $App->getEnv() == 'catalog'){
				$table1 = '<div class="checkgooglezones"><table cellpadding="0" cellspacing="0" border="0">';

				$checkAddressButton = htmlBase::newElement('button')
					->usePreset('continue')
					->setId('checkAddress')
					->setName('checkAdress')
					->setText(sysLanguage::get('TEXT_BUTTON_CHECK_ADDRESS'));

				$changeAddressButton = htmlBase::newElement('button')
					->usePreset('continue')
					->setId('changeAddress')
					->setName('changeAdress')
					->setText(sysLanguage::get('TEXT_BUTTON_CHANGE_ADDRESS'));

				$changeAddress = htmlBase::newElement('div');

				$checkAddressBox = htmlBase::newElement('div');

				$addressBook = $userAccount->plugins['addressBook'];
				$shippingAddress = $addressBook->getAddress('delivery');
				if (Session::exists('PPRaddressCheck')){
					$pprAddress = Session::get('PPRaddressCheck');
					$street = $pprAddress['address']['street_address'];
					$city = $pprAddress['address']['city'];
					$country = $pprAddress['address']['country'];
					$state = $pprAddress['address']['state'];
					$zip = $pprAddress['address']['postcode'];
				}
				else {
					$street = $shippingAddress['entry_street_address'];
					$city = $shippingAddress['entry_city'];
					$state = $shippingAddress['entry_state'];
					$zip = $shippingAddress['entry_postcode'];
					$country = isset($shippingAddress['entry_country']) ? $shippingAddress['entry_country'] : sysConfig::get('STORE_COUNTRY');
				}
				$checkAddressBox->html('<table border="0" cellspacing="2" cellpadding="2" id="googleAddress">' .
					'<tr>' .
					'<td>' . sysLanguage::get('ENTRY_STREET_ADDRESS') . '</td>' .
					'<td>' . tep_draw_input_field('street_address', $street, 'id="street_addressCheck"') . '</td>' .
					'</tr>' .
					'<tr>' .
					'<td>' . sysLanguage::get('ENTRY_CITY') . '</td>' .
					'<td>' . tep_draw_input_field('city', $city, 'id="cityCheck"') . '</td>' .
					'</tr>' .
					'<tr>' .
					'<td>' . sysLanguage::get('ENTRY_STATE') . '</td>' .
					'<td id="stateColCheck">' . tep_draw_input_field('state', $state, 'id="stateCheck"') . '</td>' .
					'</tr>' .
					'<tr>' .
					'<td>' . sysLanguage::get('ENTRY_POST_CODE') . '</td>' .
					'<td>' . tep_draw_input_field('postcode', $zip, 'id="postcode1Check"') . '</td>' .
					'</tr>' .
					'<tr>' .
					'<td>' . sysLanguage::get('ENTRY_COUNTRY') . '</td>' .
					'<td>' . tep_get_country_list('country', $country, 'id="countryDropCheck"') . '</td>' .
					'</tr>' .
					'</table>');

				ob_start();
				?>
			<script type="text/javascript">
				$(document).ready(function () {
					<?php
					if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_CHECK_GOOGLE_ZONES_BEFORE') == 'True' && $App->getEnv() == 'catalog'){
						if (Session::exists('PPRaddressCheck') === false){
							?>
							$('#googleAddress').show();
							$('#checkAddress').show();
							$('#changeAddress').hide();
							$('.dateRow').hide();

							$('#checkAddress').click(function (e) {
								e.preventDefault();
								var $this = $(this);
								showAjaxLoader($this, 'small');

								$.ajax({
									cache:false,
									dataType:'json',
									url:js_app_link('appExt=payPerRentals&app=build_reservation&appPage=default&rType=ajax&action=checkAddress'),
									data:$('*', $('#googleAddress')).serialize(),
									type:'post',
									success:function (data) {
										removeAjaxLoader($this);
										if (data.success == true){
											$('#checkAddress').hide();
											$('#googleAddress').hide();
											$('#changeAddress').show();
											$('.dateRow').show();
											var isHidden = false;
											$('.shipmethod').each(function () {
												var hidemethod = true;
												for(i = 0; i < data.methods.length; i++){
													if ($(this).hasClass('row_' + data.methods[i]) == true){
														hidemethod = false;
														break;
													}
												}
												if (hidemethod == true){
													$(this).find('input').removeAttr('checked');
													isHidden = true;
													$(this).hide();
												}
												else {
													$(this).show();
												}
											});

											$('.shipmethod').each(function () {
												if (isHidden){
													if ($(this).is(':visible')){
														$(this).find('input').attr('checked', 'checked');
														return false;
													}
												}
											});

										}
										else {
											alert(data.message);
										}

									}
								});
							});

							$('#countryDropCheck').change(function () {
								var $stateColumn = $('#stateColCheck');
								showAjaxLoader($stateColumn);

								$.ajax({
									cache:true,
									url:js_app_link('appExt=payPerRentals&app=build_reservation&appPage=default&rType=ajax&action=getCountryZones'),
									data:'cID=' + $(this).val() + '&zName=' + $('#stateColCheck input').val(),
									dataType:'html',
									success:function (data) {
										removeAjaxLoader($stateColumn);
										$('#stateColCheck').html(data);
									}
								});
							});

							$('#countryDropCheck').trigger('change');

							<?php
						}
						else {
							?>
							$('#checkAddress').trigger('click');
							$('#checkAddress').hide();
							$('#googleAddress').hide();
							$('#changeAddress').show();
							$('.dateRow').show();
							$('#changeAddress').click(function () {
								$('#googleAddress').show();
								$('#checkAddress').show();
								$('#changeAddress').hide();
								$('.dateRow').hide();
							});
							<?php
						}
					}
					?>
				});
			</script>
			<?php
				$script = ob_get_contents();
				ob_end_clean();

				$changeAddress->append($checkAddressBox)
					->append($checkAddressButton)
					->append($changeAddressButton);

				$table1 .= '<tr style="text-align:center">' .
					'<td colspan="2" class="main" style="text-align:center">' . $changeAddress->draw() . '</td>' .
					'</tr>';
				$table1 .= '</table></div>';
				$table .= '<tr><td>' . $table1 . $script . '</td></tr>';
			}
			$table .= '</table>';
		}
		return $table;
	}

	public function getHiddenFields() {
		global $appExtension;
		$result1 = array();

		$extAttributes = $appExtension->getExtension('attributes');
		if ($extAttributes && $extAttributes->isEnabled()){
			if (isset($_POST[$extAttributes->inputKey])){
				if (isset($_POST[$extAttributes->inputKey]['reservation'])){
					foreach($_POST[$extAttributes->inputKey]['reservation'] as $oID => $vID){
						$result1[] = tep_draw_hidden_field('id[reservation][' . $oID . ']', $vID);
					}
				}
				Session::remove('postedVars');
			}
		}
		//print_r($hiddenFields);echo 'hhh';
		$hiddenFields = array();
		EventManager::notify('PurchaseTypeHiddenFields', &$hiddenFields);
		$result = array_merge($result1, $hiddenFields);
		if (isset($result) && is_array($result)){
			return implode("\n", $result);
		}
	}


	public function findBestPrice($dateArray) {
		global $currencies;
		$this->addDays($dateArray['start'], $dateArray['end']);
		$price = 0;
		$startTime = $dateArray['start']->getTimestamp();
		$endTime = $dateArray['end']->getTimestamp();
		if(sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_FULL_DAYS') == 'True'){
  			if(sysConfig::get('EXTENSION_PAY_PER_RENTALS_MORE_HOURS_ONE_DAY') == 'True'){
				if(isset($_POST['start_time']) && isset($_POST['end_time']) && $_POST['end_time'] > $_POST['start_time']){
					$endTime += (60*60*24);
				}
			}
		}

		$nMinutes = (($endTime - $startTime) / 60);
		$minutesArray = array();

		$pprTypes = array();
		$pprTypesDesc = array();
		foreach(PurchaseType_reservation_utilities::getRentalTypes() as $iType){
			$pprTypes[$iType['pay_per_rental_types_id']] = $iType['minutes'];
			$pprTypesDesc[$iType['pay_per_rental_types_id']] = $iType['pay_per_rental_types_name'];
		}

		$checkStoreId = 0;
		if (isset($dateArray['store_id'])){
			$checkStoreId = $dateArray['store_id'];
		}elseif (Session::exists('current_store_id')){
			$checkStoreId = Session::exists('current_store_id');
		}
		foreach(PurchaseType_reservation_utilities::getRentalPricing($this->getPayPerRentalId()) as $iPrices){
			$discount = false;
			if (isset($this->Discounts[$checkStoreId])){
				foreach($this->Discounts[$checkStoreId] as $dInfo){
					if ($dInfo['ppr_type'] == $iPrices['pay_per_rental_types_id']){
						$checkFrom = $dInfo['discount_from'] * $pprTypes[$dInfo['ppr_type']];
						$checkTo = $dInfo['discount_to'] * $pprTypes[$dInfo['ppr_type']];
						if ($nMinutes >= $checkFrom && $nMinutes <= $checkTo){
							if ($dInfo['discount_type'] == 'percent'){
								$discount = ($iPrices['price'] * ($dInfo['discount_amount']/100));
							}else{
								$discount = $dInfo['discount_amount'];
							}
						}
					}
				}
			}
			$minutesArray[$iPrices['number_of']*$pprTypes[$iPrices['pay_per_rental_types_id']]] = ($discount !== false ? $iPrices['price'] - $discount : $iPrices['price']);
			$messArr[$iPrices['number_of'] * $pprTypes[$iPrices['pay_per_rental_types_id']]] = $iPrices['number_of'] . ' ' . $pprTypesDesc[$iPrices['pay_per_rental_types_id']];
		}
		ksort($minutesArray);
		ksort($messArr);

		$firstMinUnity = $messArr[key($messArr)];
		$firstMinMinutes = key($messArr);
		$myKeys = array_keys($minutesArray);
		$message = sysLanguage::get('PPR_PRICE_BASED_ON');
		//if(count($myKeys) > 1) {
		$is_bigger = true;
		for($i = 0; $i < count($myKeys); $i++){
			if ($myKeys[$i] > $nMinutes){
				$biggerPrice = $minutesArray[$myKeys[$i]];
				if ($i > 0){
					$normalPrice = (float)($minutesArray[$myKeys[$i - 1]] / $myKeys[$i - 1]) * $nMinutes;
				}
				else {
					$normalPrice = -1;
				}
				if ($normalPrice > $biggerPrice || $normalPrice == -1){
					$price = $biggerPrice;
					$message .= '1X' . substr($messArr[$myKeys[$i]], 0, strlen($messArr[$myKeys[$i]]) - 1) . '@' . $currencies->format($minutesArray[$myKeys[$i]]);
				}
				else {
					$price = $normalPrice;
					$message .= (int)($nMinutes / $myKeys[$i - 1]) . 'X' . $messArr[$myKeys[$i - 1]] . '@' . $currencies->format($minutesArray[$myKeys[$i - 1]]) . '/' . substr($messArr[$myKeys[$i - 1]], 0, strlen($messArr[$myKeys[$i - 1]]) - 1);
					if ($nMinutes % $myKeys[$i - 1] > 0){
						$message .= ' + ' . number_format($nMinutes % $myKeys[$i - 1] / $firstMinMinutes, 2) . 'X' . $firstMinUnity . '@' . $currencies->format((float)($minutesArray[$myKeys[$i - 1]] / $myKeys[$i - 1] * $firstMinMinutes)) . '/' . $firstMinUnity;
					}
				}
				$is_bigger = false;
				break;
			}
		}
		if ($is_bigger){
			$i = count($myKeys) - 1;
			$normalPrice = (float)($minutesArray[$myKeys[$i]] / $myKeys[$i]) * $nMinutes;
			$price = $normalPrice;
			$message .= (int)($nMinutes / $myKeys[$i]) . 'X' . $messArr[$myKeys[$i]] . '@' . $currencies->format($minutesArray[$myKeys[$i]]) . '/' . substr($messArr[$myKeys[$i]], 0, strlen($messArr[$myKeys[$i]]) - 1);
			if ($nMinutes % $myKeys[$i] > 0){
				$message .= ' + ' . number_format($nMinutes % $myKeys[$i] / $firstMinMinutes, 2) . ' X' . $firstMinUnity . '@' . $currencies->format((float)($minutesArray[$myKeys[$i]] / $myKeys[$i] * $firstMinMinutes)) . '/' . $firstMinUnity;
			}
		}

		$return['price'] = round($price, 2);
		if (sysconfig::get('EXTENSION_PAY_PER_RENTALS_SHORT_PRICE') == 'False'){
			$return['message'] = $message;
		}
		else {
			$return['message'] = '';
		}
		return $return;
	}

	public function addDays(DateTime &$sdate, DateTime &$edate) {
		$days = 0;

		if ($sdate != $edate){
			switch(sysConfig::get('EXTENSION_PAY_PER_RENTALS_LENGTH_METHOD')){
				case 'First':
					//$sdate = date('Y-m-d H:i:s', strtotime('+1 days', strtotime($sdate)));
					break;
				case 'Last':
					//$edate = date('Y-m-d H:i:s', strtotime('-1 days', strtotime($edate)));
					break;
				case 'Both':
					$edate->add(new DateInterval('P1D')); //Add one day
					//$edate = date('Y-m-d H:i:s', strtotime('+1 days', strtotime($edate)));
					break;
				case 'None':
					$sdate->add(new DateInterval('P1D')); //Add one day
					//$sdate = date('Y-m-d H:i:s', strtotime('+1 days', strtotime($sdate)));
					//$edate = date('Y-m-d H:i:s', strtotime('-1 days', strtotime($edate)));
					break;
			}
        }
	    if(sysConfig::get('EXTENSION_PAY_PER_RENTALS_CALCULATE_DISABLED_DAYS') == 'False'){
		    $startTime = $sdate->getTimestamp();
		    $endTime = $edate->getTimestamp();
		    $disabledDays = array_filter(sysConfig::explode('EXTENSION_PAY_PER_RENTALS_DISABLED_DAYS', ','));
		    while ($startTime <= $endTime) {
			    $dayOfWeek = date('D', $startTime);
			    if(in_array($dayOfWeek, $disabledDays)){
				    $sdate = date('Y-m-d H:i:s', strtotime('+1 days', strtotime($sdate)));
			    }
			    $startTime += 60 * 60 * 24;
		    }
	    }
	}

	public function getReservationPrice(DateTime $start, DateTime $end, &$rInfo = '', $semName = '', $includeInsurance = false, $onlyShow = true) {
		global $currencies;
		$productPricing = array();

		if ($rInfo != '' && isset($rInfo['shipping']) && isset($rInfo['shipping']['cost'])){
			$productPricing['shipping'] = $rInfo['shipping']['cost'];
		}
		elseif (isset($_POST['rental_shipping']) && tep_not_null($_POST['rental_shipping']) && $_POST['rental_shipping'] != 'undefined') {
			$shippingMethod = explode('_', $_POST['rental_shipping']);
			$Module = OrderShippingModules::getModule($shippingMethod[0]);
			$product = new Product($this->getProductId());
			if (isset($_POST['rental_qty'])){
				$total_weight = (int)$_POST['rental_qty'] * $product->getWeight();
			}
			else {
				$total_weight = $product->getWeight();
			}
			$quote = $Module->quote($shippingMethod[1], $total_weight);

			if ($quote['methods'][0]['cost'] > 0){
				$productPricing['shipping'] = (float)$quote['methods'][0]['cost'];
			}
		}

		$dateArray = array(
			'start' => $start,
			'end' => $end
		);
		if (is_array($rInfo) && isset($rInfo['store_id'])){
			$dateArray['store_id'] = $rInfo['store_id'];
		}

		$f = true;
		if (isset($rInfo['semester_name']) && $rInfo['semester_name'] == ''){
			$f = true;
		}
		else {
			if (!isset($rInfo['semester_name'])){
				$f = true;
			}
			else {
				$f = false;
			}
		}
		if ($semName == '' && $f){
			$returnPrice = $this->findBestPrice($dateArray);
		}
		else {
			if ($semName == ''){
				$semName = $rInfo['semester_name'];
			}
			$returnPrice['price'] = $this->getPriceSemester($semName);
			$returnPrice['message'] = sysLanguage::get('PPR_PRICE_BASED_ON_SEMESTER') . $semName . ' ';
		}

		if (is_array($returnPrice)){

			if (isset($productPricing['shipping'])){
				if ($onlyShow){
					$returnPrice['price'] += $productPricing['shipping'];
				}
				$returnPrice['message'] .= ' + ' . $currencies->format($productPricing['shipping']) . ' ' . sysLanguage::get('EXTENSION_PAY_PER_RENTALS_CALENDAR_SHIPPING');
			}
			if ($this->getDepositAmount() > 0){
				if ($onlyShow){
					$returnPrice['price'] += $this->getDepositAmount();
				}
				$returnPrice['message'] .= ' + ' . $currencies->format($this->getDepositAmount()) . ' ' . sysLanguage::get('EXTENSION_PAY_PER_RENTALS_CALENDAR_DEPOSIT');
			}

			if (isset($rInfo['insurance'])){
				if ($onlyShow){
					$returnPrice['price'] += (float)$rInfo['insurance'];
				}
			}
			elseif ($includeInsurance) {
				$payPerRentals = Doctrine_Query::create()
					->select('insurance')
					->from('ProductsPayPerRental')
					->where('products_id = ?', $this->getProductId())
					->fetchOne();
				$rInfo['insurance'] = $payPerRentals->insurance;
				$returnPrice['price'] += (float)$rInfo['insurance'];
				$returnPrice['message'] .= ' + ' . $currencies->format($rInfo['insurance']) . ' ' . sysLanguage::get('EXTENSION_PAY_PER_RENTALS_CALENDAR_INSURANCE');
			}

			EventManager::notify('PurchaseTypeAfterSetup', &$returnPrice);
		}
		//echo 'RETURNPRICE::';print_r($dateArray);print_r($returnPrice);
		return $returnPrice;
	}

	public function figureProductPricing(&$pID_string, $externalResInfo = false) {
		global $ShoppingCart;

		if ($externalResInfo === true){
			$rInfo = $ShoppingCart->reservationInfo;
		}
		elseif (is_array($pID_string)) {
			$rInfo =& $pID_string;
		}

		$pricing = $this->getReservationPrice($rInfo['start_date'], $rInfo['end_date'], &$rInfo,(isset($_POST['semester_name'])?$_POST['semester_name']:''),(isset($_POST['hasInsurance'])?true:false));

		return $pricing;
	}

	public function formatDateArr($format, $date) {
		return date($format, mktime($date['hour'], $date['minute'], $date['second'], $date['month'], $date['day'], $date['year']));
	}

	public function showProductListing($col, $options = array()) {
		global $currencies, $appExtension;
		$return = false;
		if ($col == 'productsPriceReservation'){
			$options = array_merge(array(
				'showBuyButton' => true
			), $options);
			$tableRow = array();
			if ($appExtension->isEnabled('payPerRentals') === true){

				if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_DATE_SELECTION') == 'Using calendar after browsing products and clicking Reserve' && $this->hasInventory()){
					$payPerRentalButton = htmlBase::newElement('button')
						->setText(sysLanguage::get('TEXT_BUTTON_PAY_PER_RENTAL'))
						->setHref(
						itw_app_link(
							tep_get_all_get_params(array('action', 'products_id')) .
								'action=reserve_now&products_id=' . $this->getData('products_id')
						),
						true
					);
					$extraContent = '';
					EventManager::notify('ProductListingModuleShowBeforeShow', 'reservation', $this, &$payPerRentalButton, &$extraContent);

					if ($options['showBuyButton'] === false){
						$payPerRentalButton = '';
					}else{
						$payPerRentalButton = $payPerRentalButton->draw();
					}
					$i = 1;
					foreach(PurchaseType_reservation_utilities::getRentalPricing($this->getPayPerRentalId()) as $iPrices){
						$tableRow[$i] = '<tr>
                                    <td class="main">' . $iPrices['PricePayPerRentalPerProductsDescription'][0]['price_per_rental_per_products_name'] . ':</td>
                                    <td class="main">' . $this->displayReservePrice($iPrices['price']) . '</td>
				                  </tr>';
						$i++;
					}

					if (sizeof($tableRow) > 0){
						$tableRow[0] = '<tr>
					   <td class="main" colspan="2" style="font-size:.8em;" align="center">' . $extraContent . $payPerRentalButton . '</td>
					  </tr>';
						ksort($tableRow);
					}
				}
				elseif (sysConfig::get('EXTENSION_PAY_PER_RENTALS_DATE_SELECTION') != 'Using calendar after browsing products and clicking Reserve') {
					$isav = false;
					$deleteS = false;
					$isdouble = false;
					if (Session::exists('isppr_inventory_pickup') === false && Session::exists('isppr_city') === true && Session::get('isppr_city') != ''){
						$Qproducts = Doctrine_Query::create()
							->from('ProductsInventoryBarcodes b')
							->leftJoin('b.ProductsInventory i')
							->leftJoin('i.Products p')
							->leftJoin('b.ProductsInventoryBarcodesToInventoryCenters b2c')
							->leftJoin('b2c.ProductsInventoryCenters ic');

						$Qproducts->where('p.products_id=?', $this->getProductId());
						$Qproducts->andWhere('i.use_center = ?', '1');

						if (Session::exists('isppr_continent') === true && Session::get('isppr_continent') != ''){
							$Qproducts->andWhere('ic.inventory_center_continent = ?', Session::get('isppr_continent'));
						}
						if (Session::exists('isppr_country') === true && Session::get('isppr_country') != ''){
							$Qproducts->andWhere('ic.inventory_center_country = ?', Session::get('isppr_country'));
						}
						if (Session::exists('isppr_state') === true && Session::get('isppr_state') != ''){
							$Qproducts->andWhere('ic.inventory_center_state = ?', Session::get('isppr_state'));
						}
						if (Session::exists('isppr_city') === true && Session::get('isppr_city') != ''){
							$Qproducts->andWhere('ic.inventory_center_city = ?', Session::get('isppr_city'));
						}
						$Qproducts = $Qproducts->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
						$invCenter = -1;
						//print_r($Qproducts);
						foreach($Qproducts as $iProduct){
							if ($invCenter == -1){
								$invCenter = $iProduct['ProductsInventoryBarcodesToInventoryCenters']['ProductsInventoryCenters']['inventory_center_id'];
							}
							elseif ($iProduct['ProductsInventoryBarcodesToInventoryCenters']['ProductsInventoryCenters']['inventory_center_id'] != $invCenter) {
								$isdouble = true;
								break;
							}
						}

						if (!$isdouble){
							Session::set('isppr_inventory_pickup', $Qproducts[0]['ProductsInventoryBarcodesToInventoryCenters']['ProductsInventoryCenters']['inventory_center_id']);
							$deleteS = true;
						}
					}
					$hasInventory = $this->hasInventory();
					if (Session::exists('isppr_selected') && Session::get('isppr_selected') == true && $hasInventory){
						$start_date = '';
						$end_date = '';
						$event_date = '';
						$event_name = '';
						$event_gate = '';
						$pickup = '';
						$dropoff = '';
						$days_before = '';
						$days_after = '';
						if (Session::exists('isppr_shipping_days_before')){
							$days_before = Session::get('isppr_shipping_days_before');
						}
						if (Session::exists('isppr_shipping_days_after')){
							$days_after = Session::get('isppr_shipping_days_after');
						}
						if (Session::exists('isppr_date_start')){
							$start_date = Session::get('isppr_date_start');
						}
						if (Session::exists('isppr_date_end')){
							$end_date = Session::get('isppr_date_end');
						}
						if (Session::exists('isppr_event_date')){
							$event_date = Session::get('isppr_event_date');
						}
						if (Session::exists('isppr_event_name')){
							$event_name = Session::get('isppr_event_name');
						}
						if (Session::exists('isppr_event_gate')){
							$event_gate = Session::get('isppr_event_gate');
						}
						if (Session::exists('isppr_inventory_pickup')){
							$pickup = Session::get('isppr_inventory_pickup');
						}
						else {
							//check the inventory center for this one $productClass->getID()
						}
						if (Session::exists('isppr_inventory_dropoff')){
							$dropoff = Session::get('isppr_inventory_dropoff');
						}
						if (Session::exists('isppr_product_qty')){
							$qtyVal = (int)Session::get('isppr_product_qty');
						}
						else {
							$qtyVal = 1;
						}

						$payPerRentalButton = htmlBase::newElement('button')
							->setType('submit')
							->setText(sysLanguage::get('TEXT_BUTTON_RESERVE'))
							->addClass('inCart')
							->setName('add_reservation_product');
						$isav = true;
						if (Session::exists('isppr_shipping_cost')){
							$ship_cost = (float)Session::get('isppr_shipping_cost');
						}
						$depositAmount = $this->getDepositAmount();
						$thePrice = 0;
						$rInfo = '';
						$price = $this->getReservationPrice($start_date, $end_date, $rInfo,'', (sysConfig::get('EXTENSION_PAY_PER_RENTALS_INSURE_ALL_PRODUCTS_AUTO') == 'True'));
						$thePrice += $price['price'];
						if(Session::exists('isppr_event_multiple_dates')){
							$thePrice = 0;
							$datesArr = Session::get('isppr_event_multiple_dates');

							foreach($datesArr as $iDate){
								$price = $this->getReservationPrice($iDate, $iDate,$rInfo,'',(sysConfig::get('EXTENSION_PAY_PER_RENTALS_INSURE_ALL_PRODUCTS_AUTO') == 'True'));
								$thePrice += $price['price'];
							}

						}
						$i2 = 1;
						if(Session::exists('noInvDates')){
							$myNoInvDates = Session::get('noInvDates');
							if(isset($myNoInvDates[$this->getData('products_id')]) && is_array($myNoInvDates[$this->getData('products_id')]) && count($myNoInvDates[$this->getData('products_id')]) > 0){
								$tableRow[$i2] = '<tr>
										<td class="main" colspan="2">' . '<b>Item not available:</b>' . '</td>
									  </tr>';
								$i2++;
								foreach($myNoInvDates[$this->getData('products_id')] as $iDate){
									$tableRow[$i2] = '<tr>
										<td class="main" colspan="2" style="color:red">' . strftime(sysLanguage::getDateFormat('long'),$iDate) . '</td>
									  </tr>';
									$i2++;
								}
							}
						}

						$pricing = $currencies->format($qtyVal * $thePrice - $qtyVal * $depositAmount + $ship_cost);

						$pageForm = htmlBase::newElement('form')
							->attr('name', 'build_reservation')
							->attr('action', itw_app_link('appExt=payPerRentals&products_id=' . $this->getData('products_id'), 'build_reservation', 'default'))
							->attr('method', 'post');

						if (isset($start_date)){
							$htmlStartDate = htmlBase::newElement('input')
								->setType('hidden')
								->setName('start_date')
								->setValue($start_date);
						}
						if (isset($days_before)) {
							$htmlDaysBefore = htmlBase::newElement('input')
								->setType('hidden')
								->setName('days_before')
								->setValue($days_before);
						}

						if (isset($days_after)) {
							$htmlDaysAfter = htmlBase::newElement('input')
								->setType('hidden')
								->setName('days_after')
								->setValue($days_after);
						}

						if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_EVENTS') == 'True'){
							$htmlEventDate = htmlBase::newElement('input')
								->setType('hidden')
								->setName('event_date')
								->setValue($event_date);
							$htmlEventName = htmlBase::newElement('input')
								->setType('hidden')
								->setName('event_name')
								->setValue($event_name);
							if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_GATES') == 'True') {
								$htmlEventGates = htmlBase::newElement('input')
									->setType('hidden')
									->setName('event_gate')
									->setValue($event_gate);
							}
						}
						if (isset($pickup)){
							$htmlPickup = htmlBase::newElement('input')
								->setType('hidden')
								->setName('pickup')
								->setValue($pickup);
						}
						if (isset($dropoff)){
							$htmlDropoff = htmlBase::newElement('input')
								->setType('hidden')
								->setName('dropoff')
								->setValue($dropoff);
						}
						$htmlRentalQty = htmlBase::newElement('input');
						if(sysConfig::get('EXTENSION_PAY_PER_RENTALS_SHOW_QTY_LISTING') == 'False'){
							$htmlRentalQty->setType('hidden');
						}else{
							$htmlRentalQty->attr('size','3');
						}
						$htmlRentalQty->setName('rental_qty')
							->setValue($qtyVal);

						if(sysConfig::get('EXTENSION_PAY_PER_RENTALS_INSURE_ALL_PRODUCTS_AUTO') == 'True'){
							$htmlHasInsurance = htmlBase::newElement('input')
								->setType('hidden')
								->setName('hasInsurance')
								->setValue('1');
							$pageForm->append($htmlHasInsurance);
						}
						$htmlProductsId = htmlBase::newElement('input')
							->setType('hidden')
							->setName('products_id')
							->setValue($this->getData('products_id'));
						if (isset($end_date)){
							$htmlEndDate = htmlBase::newElement('input')
								->setType('hidden')
								->setName('end_date')
								->setValue($end_date);
						}

						if (isset($htmlStartDate)){
							$pageForm->append($htmlStartDate);
						}
						if (isset($htmlEndDate)){
							$pageForm->append($htmlEndDate);
						}
						if (isset($htmlDaysBefore)) {
							$pageForm->append($htmlDaysBefore);
						}

						if (isset($htmlDaysAfter)) {
							$pageForm->append($htmlDaysAfter);
						}
						if (isset($htmlPickup)){
							$pageForm->append($htmlPickup);
						}
						if (isset($htmlDropoff)){
							$pageForm->append($htmlDropoff);
						}
						$pageForm->append($htmlRentalQty);
						$pageForm->append($htmlProductsId);
						$pageForm->append($payPerRentalButton);

						if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_EVENTS') == 'True'){
							$pageForm->append($htmlEventDate)
								->append($htmlEventName);
							if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_GATES') == 'True') {
								$pageForm->append($htmlEventGates);
							}
						}
						$ship_cost = 0;
						$isR = false;
						$isRV = '';
						if (Session::exists('isppr_shipping_method')){
							$htmlShippingDays = htmlBase::newElement('input')
								->setType('hidden')
								->setName('rental_shipping');
							if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_UPS_RESERVATION') == 'False'){
								$htmlShippingDays->setValue("zonereservation_" . Session::get('isppr_shipping_method'));
								if (Session::exists('isppr_shipping_cost')) {
									$ship_cost = (float) Session::get('isppr_shipping_cost');
								}

							}else{
								$htmlShippingDays->setValue("upsreservation_" . Session::get('isppr_shipping_method'));
								if(isset($_POST['rental_shipping'])){
									$isR = true;
									$isRV = $_POST['rental_shipping'];
								}
								$_POST['rental_shipping'] = 'upsreservation_'. Session::get('isppr_shipping_method');
							}
							$pageForm->append($htmlShippingDays);
						}

						if(sysConfig::get('EXTENSION_PAY_PER_RENTALS_SHOW_RATES_PPR_BEFORE') == 'True'){
							foreach($this->getRentalPricing() as $iPrices){
								$tableRow[$i2] = '<tr>
									<td class="main" colspan="2">' .$iPrices['PricePayPerRentalPerProductsDescription'][0]['price_per_rental_per_products_name'].': '. $this->displayReservePrice($iPrices['price']) . '</td>
								  </tr>';
								$i2++;
							}
						}

						$tableRow[$i2] = '<tr>
							<td class="main"><nobr>Price:</nobr></td>
							<td class="main">' . $pricing . '</td>
						</tr>';
						$extraContent = '';
						EventManager::notify('ProductListingModuleShowBeforeShow', 'reservation', $this, &$payPerRentalButton, &$extraContent);

						if (sizeof($tableRow) > 0){
							$tableRow[0] = '<tr>
						   <td class="main" colspan="2" style="font-size:.8em;" align="center">' . $extraContent.  $pageForm->draw() . '</td>
						  </tr>';
							ksort($tableRow);
						}
					}
					if ($isdouble){
						unset($tableRow);
						$isav = true;
						$start_date = '';
						$end_date = '';
						$ship_cost = 0;
						$depositAmount = 0;
						if (Session::exists('isppr_date_start')){
							$start_date = Session::get('isppr_date_start');
						}
						if (Session::exists('isppr_date_end')){
							$end_date = Session::get('isppr_date_end');
						}
						if (Session::exists('isppr_shipping_cost')){
							$ship_cost = (float)Session::get('isppr_shipping_cost');
						}
						if (Session::exists('isppr_product_qty')){
							$qtyVal = (int)Session::get('isppr_product_qty');
						}
						else {
							$qtyVal = 1;
						}
						if ($start_date != '' && $end_date != ''){
							$depositAmount = $this->getDepositAmount();
							$isR = false;
							$isRV = '';
							if (Session::exists('isppr_shipping_method')) {
								if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_UPS_RESERVATION') == 'False'){
									if (Session::exists('isppr_shipping_cost')) {
										$ship_cost = (float) Session::get('isppr_shipping_cost');
									}
								}else{
									if(isset($_POST['rental_shipping'])){
										$isR = true;
										$isRV = $_POST['rental_shipping'];
									}
									$_POST['rental_shipping'] = 'upsreservation_'. Session::get('isppr_shipping_method');
								}
							}

							$thePrice = 0;
							$price = $this->getReservationPrice($start_date, $end_date);
							$thePrice += $price['price'];
							if(Session::exists('isppr_event_multiple_dates')){
								$thePrice = 0;
								$datesArr = Session::get('isppr_event_multiple_dates');

								foreach($datesArr as $iDate){
									$price = $this->getReservationPrice($iDate, $iDate);
									$thePrice += $price['price'];
								}
							}

							$pricing = $currencies->format($qtyVal * $thePrice - $qtyVal * $depositAmount + $ship_cost);
							if(!$isR){
								unset($_POST['rental_shipping']);
							}else{
								$_POST['rental_shipping'] = $isRV;
							}
							$tableRow[1] = '<tr>
							<td class="main"><nobr>Price:</nobr></td>
							<td class="main">' . $pricing . '</td>
							</tr>';
						}
						$payPerRentalButton = htmlBase::newElement('button')
							->setType('submit')
							->setText(sysLanguage::get('TEXT_BUTTON_RESERVE'))
							->setId('doubleDatesSelected')
							->setName('double_dates_selected');
						$extraContent = '';
						EventManager::notify('ProductListingModuleShowBeforeShow', 'reservation', $this, &$payPerRentalButton, &$extraContent);
						$tableRow[0] = '<tr>
					  	 <td class="main" colspan="2" style="font-size:.8em;" align="center">' . $extraContent.  $payPerRentalButton->draw() . '</td>
						  </tr>';
						ksort($tableRow);
					}
					if ($deleteS){
						//Session::remove('isppr_selected');
						Session::remove('isppr_inventory_pickup');
					}
					if (!$isav){
						$payPerRentalButton = htmlBase::newElement('button')
							->setType('submit')
							->setText(sysLanguage::get('TEXT_BUTTON_RESERVE'));

								if(Session::exists('isppr_selected') == false || Session::get('isppr_selected') == false){
									$payPerRentalButton
										->setName('no_dates_selected');
								}else{
									$payPerRentalButton
										->setName('no_inventory');
								}
								$extraContent = '';
								EventManager::notify('ProductListingModuleShowBeforeShow', 'reservation', $this, &$payPerRentalButton, &$extraContent);

								$tableRow[0] = '<tr>
					   <td class="main" colspan="2" style="font-size:.8em;" align="center">' . $extraContent . $payPerRentalButton->draw() . '</td>
					  </tr>';
						ksort($tableRow);
					}
				}
			}

			if (sizeof($tableRow) > 0){
				$return = '<table cellpadding="2" cellspacing="0" border="0">' .
					implode('', $tableRow) .
					'</table>';
			}
		}
		return $return;
	}

	public function getOrderedProductBarcode(array $pInfo) {
		return $pInfo['OrdersProductsReservation'][0]['ProductsInventoryBarcodes']['barcode'];
		$barcode = array();
		foreach($pInfo['OrdersProductsReservation'] as $res){
			$barcode[] = $res['ProductsInventoryBarcodes']['barcode'];
		}
		return $barcode;
	}

	public function displayOrderedProductBarcode(array $pInfo) {
		$barcode = '';
		foreach($pInfo['OrdersProductsReservation'] as $res){
			$barcode .= $res['ProductsInventoryBarcodes']['barcode'] . '<br/>';
		}
		return $barcode;
	}

	/*
	 * @TODO: Figure out something better
	 */
	public function getPrice($priceName = '1 Day'){
		global $currencies;

		$pprId = $this->getPayPerRentalId();
		foreach(PurchaseType_reservation_utilities::getRentalPricing($pprId) as $priceInfo){
			$price = $priceInfo['price'];
			$partName = $priceInfo['PricePayPerRentalPerProductsDescription'][0]['price_per_rental_per_products_name'];
			if (!isset($prices[$partName])){
				$prices[$partName] = 0;
			}
			$prices[$partName] += $price;
		}

		return $prices[$priceName];
	}

	public function getExportTableColumns(){
		return array(
			'status',
			'inventory_controller',
			'inventory_track_method',
			'tax_class_id'
		);
	}

	public function processProductImport($ProductType, &$Product, $item){
		parent::processProductImport($ProductType, $Product, $item);
		$PurchaseTypes =& $Product->ProductsPurchaseTypes;
		$colNameAdd = $ProductType . '_' . $this->getCode();

		$depositAmount = (isset($items['v_' . $colNameAdd . '_deposit_amount']) ? $items['v_' . $colNameAdd . '_deposit_amount'] : false);
		$insurance = (isset($items['v_' . $colNameAdd . '_insurance']) ? $items['v_' . $colNameAdd . '_insurance'] : false);
		$shippingMethods = (isset($items['v_' . $colNameAdd . '_shipping']) ? $items['v_' . $colNameAdd . '_shipping'] : false);

		$PayPerRental =& $Product->ProductsPayPerRental;
		if (isset($items['v_' . $colNameAdd . '_overbooking'])) {
			if ($items['v_' . $colNameAdd . '_overbooking'] == 'No') {
				$PayPerRental->overbooking = '0';
			} else {
				$PayPerRental->overbooking = '1';
			}
		} else {
			$PayPerRental->overbooking = '0';
		}

		/*$Product->products_auth_method = (
		isset($items['v_' . $colNameAdd . '_auth_method'])
			? $items['v_' . $colNameAdd . '_auth_method']
			: 'auth'
		);

		$Product->products_auth_charge = (
		isset($items['v_' . $colNameAdd . '_auth_charge'])
			? $items['v_' . $colNameAdd . '_auth_charge']
			: '0.0000'
		);*/

		$PayPerRental->deposit_amount = (float) ($depositAmount !== false ? $depositAmount : '0');
		$PayPerRental->insurance = (float) ($insurance !== false ? $insurance : '0');
		$PayPerRental->shipping = $shippingMethods !== false ? $shippingMethods : '';
		//$PayPerRental->save();

		/*import hidden dates*/
		$Product->PayPerRentalHiddenDates->delete();
		$j = 0;
		while(true){
			if(isset($items['v_' . $colNameAdd . '_hidden_start_date_'.$j])){
				if(!empty($items['v_' . $colNameAdd . '_hidden_start_date_'.$j])){
					$PayPerRentalHiddenDates = new PayPerRentalHiddenDates();
					$PayPerRentalHiddenDates->hidden_start_date = date('Y-m-d', strtotime($items['v_' . $colNameAdd . '_hidden_start_date_'.$j]));
					$PayPerRentalHiddenDates->hidden_end_date = date('Y-m-d', strtotime($items['v_' . $colNameAdd . '_hidden_end_date_'.$j]));

					$Product->PayPerRentalHiddenDates->add($PayPerRentalHiddenDates);
				}
			}else{
				break;
			}
			$j++;
		}
		/*end import hidden dates*/
		$i = 0;
		while (true) {

			if (isset($items['v_' . $colNameAdd . '_period_' . $i])) {
				if (!empty($items['v_' . $colNameAdd . '_period_' . $i])) {
					$Periods = Doctrine_Core::getTable('PayPerRentalPeriods');
					$PeriodPrices = Doctrine_Core::getTable('ProductsPayPerPeriods');
					$Period = $Periods->findOneByPeriodName($items['v_' . $colNameAdd . '_period_' . $i]);
					if (!$Period) {
						$Period = $Periods->getRecord();
						$Period->period_name = $items['v_' . $colNameAdd . '_period_' . $i];
						$Period->save();
						$PeriodPrice = $PeriodPrices->getRecord();
					} else {
						$PeriodPrice = $PeriodPrices->findOneByPeriodIdAndProductsId($Period->period_id, $Product->products_id);
						if (!$PeriodPrice) {
							$PeriodPrice = $PeriodPrices->getRecord();
						}
					}
					$PeriodPrice->products_id = $Product->products_id;
					$PeriodPrice->period_id = $Period->period_id;
					$PeriodPrice->price = $items['v_' . $colNameAdd . '_period_price_' . $i];
					$PeriodPrice->save();
				}
			} else {
				break;
			}
			$i++;
		}

		$j=0;
		$PricePerRentalPerProducts = Doctrine_Core::getTable('PricePerRentalPerProducts');
		Doctrine_Query::create()
			->delete('PricePerRentalPerProducts')
			->andWhere('pay_per_rental_id =?', $PayPerRental->pay_per_rental_id)
			->execute();
		$QPayPerRentalTypes = Doctrine_Query::create()
			->from('PayPerRentalTypes')
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
		$htypes = array();
		foreach ($QPayPerRentalTypes as $iType) {
			$htypes[$iType['pay_per_rental_types_id']] = $iType['pay_per_rental_types_name'];
		}

		while (true) {
			if (isset($items['v_' . $colNameAdd . '_time_period_number_of_' . $j])) {
				if (!empty($items['v_' . $colNameAdd . '_time_period_number_of_' . $j])) {

					$PricePerProduct = $PricePerRentalPerProducts->create();
					$Description = $PricePerProduct->PricePayPerRentalPerProductsDescription;

					foreach (sysLanguage::getLanguages() as $lInfo) {
						$langId = $lInfo['id'];
						if (isset($items['v_' . $colNameAdd . '_time_period_desc_' . $langId . '_' . $j]) && !empty($items['v_' . $colNameAdd . '_time_period_desc_' . $langId . '_' . $j])) {
							$Description[$langId]->language_id = $langId;
							$Description[$langId]->price_per_rental_per_products_name = $items['v_' . $colNameAdd . '_time_period_desc_' . $langId . '_' . $j];
						}
					}

					$type = '';
					foreach ($htypes as $itypeID => $itypeName) {
						if ($itypeName == $items['v_' . $colNameAdd . '_time_period_type_name_' . $j]) {
							$type = $itypeID;
							break;
						}
					}

					$PricePerProduct->price = $items['v_' . $colNameAdd . '_time_period_price_' . $j];
					$PricePerProduct->number_of = $items['v_' . $colNameAdd . '_time_period_number_of_' . $j];
					$PricePerProduct->pay_per_rental_types_id = $type;
					$PricePerProduct->pay_per_rental_id = $PayPerRental->pay_per_rental_id;
					$PricePerProduct->save();
				}
			} else {
				break;
			}
			$j++;
		}
	}

	public function addExportQueryConditions($ProductType, &$QfileLayout){
		parent::addExportQueryConditions($ProductType, $QfileLayout);

		$colNameAdd = $ProductType . '_' . $this->getCode();
		$QfileLayout->leftJoin('p.ProductsPayPerRental ppr')
			->addSelect('ppr.shipping as v_' . $colNameAdd . '_shipping')
			->addSelect('ppr.max_days as v_' . $colNameAdd . '_max_days')
			->addSelect('ppr.max_months as v_' . $colNameAdd . '_max_months')
			->addSelect('ppr.overbooking as v_' . $colNameAdd . '_overbooking')
			->addSelect('ppr.insurance as v_' . $colNameAdd . '_insurance')
			->addSelect('ppr.deposit_amount as v_' . $colNameAdd . '_deposit_amount');
	}

	public function addExportHeaderColumns($ProductType, &$HeaderRow){
		parent::addExportHeaderColumns($ProductType, $HeaderRow);

		$colNameAdd = $ProductType . '_' . $this->getCode();
		/*export hidden dates*/
		$QHiddenDatesMAX = Doctrine_Query::create()
			->select('COUNT(*) as hiddenmax')
			->from('PayPerRentalHiddenDates')
			->groupby('products_id')
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
		$maxVal = -1;
		foreach($QHiddenDatesMAX as $iMax){
			if($iMax['hiddenmax'] > $maxVal){
				$maxVal = $iMax['hiddenmax'];
			}
		}

		for($j=0;$j<$maxVal;$j++){
			$HeaderRow->addColumn('v_' . $colNameAdd . '_hidden_start_date_'. $j);
			$HeaderRow->addColumn('v_' . $colNameAdd . '_hidden_end_date_'. $j);
		}
		/*end of export*/

		$QPricePerRentalProductsMAX = Doctrine_Query::create()
			->select('COUNT(*) as pprmax')
			->from('PricePerRentalPerProducts')
			->where('pay_per_rental_id > 0')
			->groupBy('pay_per_rental_id')
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
		$maxVal = -1;
		foreach($QPricePerRentalProductsMAX as $iMax){
			if($iMax['pprmax'] > $maxVal){
				$maxVal = $iMax['pprmax'];
			}
		}

		for($j=0;$j<$maxVal;$j++){
			$HeaderRow->addColumn('v_' . $colNameAdd . '_time_period_number_of_'. $j);
			$HeaderRow->addColumn('v_' . $colNameAdd . '_time_period_type_name_'. $j);
			$HeaderRow->addColumn('v_' . $colNameAdd . '_time_period_price_'. $j);
			foreach(sysLanguage::getLanguages() as $lInfo){
				$HeaderRow->addColumn('v_' . $colNameAdd . '_time_period_desc_'.$lInfo['id'].'_'. $j);
			}
		}

		$HeaderRow->addColumn('v_' . $colNameAdd . '_deposit_amount');
		$HeaderRow->addColumn('v_' . $colNameAdd . '_shipping');
		$HeaderRow->addColumn('v_' . $colNameAdd . '_max_days');
		$HeaderRow->addColumn('v_' . $colNameAdd . '_max_months');
		$HeaderRow->addColumn('v_' . $colNameAdd . '_insurance');
		$HeaderRow->addColumn('v_' . $colNameAdd . '_overbooking');

		$QPeriods = Doctrine_Query::create()
			->from('PayPerRentalPeriods')
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
		$i = 0;
		foreach($QPeriods as $iPeriod){
			$HeaderRow->addColumn('v_' . $colNameAdd . '_period_'. $i);
			$HeaderRow->addColumn('v_' . $colNameAdd . '_period_price_'. $i);
			$i++;
		}
	}

	public function addExportRowColumns($ProductType, &$CurrentRow, $pInfo){
		parent::addExportRowColumns($ProductType, $CurrentRow, $pInfo);

		$colNameAdd = $ProductType . '_' . $this->getCode();
		if ($pInfo['v_' . $colNameAdd . '_overbooking'] == '0'){
			$CurrentRow->addColumn('No', 'v_' . $colNameAdd . '_overbooking');
		}else{
			$CurrentRow->addColumn('Yes', 'v_' . $colNameAdd . '_overbooking');
		}
		$product_id = $pInfo['products_id'];

		/*export hidden dates*/
		$QHiddsenDates = Doctrine_Query::create()
			->from('PayPerRentalHiddenDates')
			->where('products_id=?', $product_id)
			->execute(array(),  Doctrine_Core::HYDRATE_ARRAY);
		$j = 0;
		foreach($QHiddsenDates as $iHidden){
			$CurrentRow->addColumn($iHidden['hidden_start_date'], 'v_' . $colNameAdd . '_hidden_start_date_'.$j);
			$CurrentRow->addColumn($iHidden['hidden_end_date'], 'v_' . $colNameAdd . '_hidden_end_date_'.$j);
			$j++;
		}

		/*end export hidden dates*/

		$QPPR = Doctrine_Query::create()
			->from('ProductsPayPerRental pprp')
			->where('products_id=?', $product_id)
			->execute(array(),  Doctrine_Core::HYDRATE_ARRAY);

		if(isset($QPPR[0]['pay_per_rental_id'])){
			$QPricePerRentalProducts = Doctrine_Query::create()
				->from('PricePerRentalPerProducts pprp')
				->leftJoin('pprp.PricePayPerRentalPerProductsDescription pprpd')
				->where('pay_per_rental_id =?',$QPPR[0]['pay_per_rental_id'])
				->orderBy('price_per_rental_per_products_id')
				->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
			$j=0;
			$QPayPerRentalTypes = Doctrine_Query::create()
				->from('PayPerRentalTypes')
				->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
			$htypes = array();
			foreach($QPayPerRentalTypes as $iType){
				$htypes[$iType['pay_per_rental_types_id']] = $iType['pay_per_rental_types_name'];
			}
			foreach($QPricePerRentalProducts as $iPrice){
				$CurrentRow->addColumn($iPrice['number_of'], 'v_' . $colNameAdd . '_time_period_number_of_'.$j);
				$CurrentRow->addColumn($htypes[$iPrice['pay_per_rental_types_id']], 'v_' . $colNameAdd . '_time_period_type_name_'.$j);
				$CurrentRow->addColumn($iPrice['price'], 'v_' . $colNameAdd . '_time_period_price_'.$j);

				foreach(sysLanguage::getLanguages() as $lInfo){

					foreach($iPrice['PricePayPerRentalPerProductsDescription'] as $desc){
						if($lInfo['id'] == $desc['language_id']){
							$CurrentRow->addColumn($desc['price_per_rental_per_products_name'], 'v_' . $colNameAdd . '_time_period_desc_'.$lInfo['id'].'_'. $j);
						}
					}
				}
				$j++;
			}
		}

		$QPeriods = Doctrine_Query::create()
			->from('ProductsPayPerPeriods')
			->where('products_id=?', $product_id)
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

		$QPeriodsNames = Doctrine_Query::create()
			->from('PayPerRentalPeriods')
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
		$periodNames =  array();
		foreach($QPeriodsNames as $iPeriod){
			$periodNames[$iPeriod['period_id']] = $iPeriod['period_name'];
		}

		$i = 0;
		foreach($QPeriods as $iPeriod){
			$CurrentRow->addColumn($periodNames[$iPeriod['period_id']], 'v_' . $colNameAdd . '_period_'.$i);
			$CurrentRow->addColumn($iPeriod['price'], 'v_' . $colNameAdd . '_period_price_'.$i);
			$i++;
		}
	}

	public function productImportAppendLog(&$Product, &$productLogArr){
		$productLogArr = array_merge($productLogArr, array(
			'Pay Per Rental Price Daily:'         => $Product->ProductsPayPerRental->price_daily,
			'Pay Per Rental Price Weekly:'        => $Product->ProductsPayPerRental->price_weekly,
			'Pay Per Rental Price Monthly:'       => $Product->ProductsPayPerRental->price_monthly,
			'Pay Per Rental Price 6 Month:'       => $Product->ProductsPayPerRental->price_six_month,
			'Pay Per Rental Price Year:'          => $Product->ProductsPayPerRental->price_year,
			'Pay Per Rental Price 3 Year:'        => $Product->ProductsPayPerRental->price_three_year,
			//'Pay Per Rental Auth Method:'         => $Product->products_auth_method,
			'Pay Per Rental Insurance:'           => $Product->ProductsPayPerRental->insurance,
			'Pay Per Rental Deposit Amount:'      => $Product->ProductsPayPerRental->deposit_amount,
			'Pay Per Rental Shipping Methods:'    => $Product->ProductsPayPerRental->shipping,
			'Pay Per Rental Max Days:'            => $Product->ProductsPayPerRental->max_days,
			'Pay Per Rental Max Months:'          => $Product->ProductsPayPerRental->max_months,
			'Pay Per Rental Overbooking Allowed:' => $Product->ProductsPayPerRental->overbooking,
		));

	}
}

?>