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
 *
 * @package ProductPurchaseTypes
 */
require(__DIR__ . '/utilities.php');

/**
 * These are here to provide a global variable, otherwise the excluded won't have
 * it's state kept and it will allow barcodes to be assigned to more than one reservation
 *
 * This is a side effect of making the order object not save anything until the very last moment
 * so that we can use a print_r statement to debug the code
 *
 * Do not remove this
 */
$_excludedBarcodes = array();
$_excludedQuantities = array();

class PurchaseType_reservation extends PurchaseTypeBase
{

	protected $pprInfo = array();

	protected $enabledShipping = false;

	protected $shipModuleCode = 'zonereservation';

	protected $Discounts = array();

	public function __construct($forceEnable = false)
	{
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
	public function loadData($productId)
	{
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
				$this->setQuantity($Data['quantity']);
				/* @DEPRECATED */
				$this->setMaxDays($Data['max_days']);
				/* @DEPRECATED */
				$this->setMaxMonths($Data['max_months']);
				$this->setShipping($Data['shipping']);
				$this->setMaintenance($Data['maintenance']);
				$this->setOverbooking($Data['overbooking']);
				$this->setDepositAmount($Data['deposit_amount']);
				$this->setInsuranceValue($Data['insurance_value']);
				$this->setInsuranceCost($Data['insurance_cost']);
				/* @DEPRECATED */
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

	public function getPayPerRentalId() { return $this->pprInfo['pay_per_rental_id']; }

	public function getQuantity() { return $this->pprInfo['quantity']; }

	public function getShipping() { return $this->pprInfo['shipping']; }

	public function getShippingArray() { return explode(',', $this->pprInfo['shipping']); }

	public function getOverbooking() { return $this->pprInfo['overbooking']; }

	/* @DEPRECATED */
	public function getInsurance() { return $this->getInsuranceCost(); }

	public function getInsuranceValue() { return $this->pprInfo['insurance_value']; }

	public function getInsuranceCost() { return $this->pprInfo['insurance_cost']; }

	public function getMinPeriod() { return $this->pprInfo['min_period']; }

	public function getMaxPeriod() { return $this->pprInfo['max_period']; }

	public function getMinType() { return $this->pprInfo['min_type']; }

	public function getMaxType() { return $this->pprInfo['max_type']; }

	public function getDiscounts() { return $this->Discounts; }

	public function getShipModuleCode() { return $this->shipModuleCode; }

	public function getMaintenance() { return $this->pprInfo['maintenance']; }

	/* @DEPRECATED */
	public function getMinRentalDays() { return $this->pprInfo['min_rental_days']; }

	public function getEnabledShippingMethods() { return $this->enabledShipping; }

	public function getMaxShippingDays(DateTime $StartDate)
	{
		return ReservationUtilities::getMaxShippingDays(
			$this->getData('products_id'),
			$StartDate,
			$this->overBookingAllowed()
		);
	}

	public function setPayPerRentalId($val) { $this->pprInfo['pay_per_rental_id'] = $val; }

	public function setQuantity($val) { $this->pprInfo['quantity'] = $val; }

	/* @DEPRECATED */
	public function setMaxDays($val) { $this->pprInfo['max_days'] = $val; }

	/* @DEPRECATED */
	public function setMaxMonths($val) { $this->pprInfo['max_months'] = $val; }

	public function setShipping($val) { $this->pprInfo['shipping'] = $val; }

	public function setOverbooking($val) { $this->pprInfo['overbooking'] = $val; }

	public function setDepositAmount($val) { $this->pprInfo['deposit_amount'] = $val; }

	/* @DEPRECATED */
	public function setInsurance($val) { $this->pprInfo['insurance'] = $val; }

	public function setInsuranceValue($val) { $this->pprInfo['insurance_value'] = $val; }

	public function setInsuranceCost($val) { $this->pprInfo['insurance_cost'] = $val; }

	/* @DEPRECATED */
	public function setMinRentalDays($val) { $this->pprInfo['min_rental_days'] = $val; }

	public function setMinPeriod($val) { $this->pprInfo['min_period'] = $val; }

	public function setMaxPeriod($val) { $this->pprInfo['max_period'] = $val; }

	public function setMinType($val) { $this->pprInfo['min_type'] = $val; }

	public function setMaxType($val) { $this->pprInfo['max_type'] = $val; }

	public function setDiscounts($val) { $this->Discounts = $val; }

	public function setShipModuleCode($val) { $this->shipModuleCode = $val; }

	public function setEnabledShipping($val) { $this->enabledShipping = $val; }

	public function setMaintenance($val) { $this->pprInfo['maintenance'] = $val; }

	public function shippingIsStore() { return ($this->getShipping() == 'store'); }

	public function shippingIsNone() { return ($this->getShipping() == 'false'); }

	public function hasDiscounts() { return (empty($this->Discounts) === false); }

	public function checkAvailableBarcodes($Product)
	{
		$barcodes = array();
		for($i = 0; $i < $Product->getQuantity(); $i++){
			$barcodeId = $this->getAvailableSerial($Product, $barcodes);
			if ($barcodeId > -1){
				$barcodes[] = $barcodeId;
			}
		}
		return (sizeof($barcodes) > $Product->getQuantity());
	}

	public function checkoutAfterProductName(ShoppingCartProduct &$cartProduct)
	{
		if ($cartProduct->hasInfo('ReservationInfo')){
			$resData = $cartProduct->getInfo('ReservationInfo');
			if ($resData && $resData['start_date']->getTimestamp() > 0){
				return $this->parse_reservation_info($cartProduct->getIdString(), $resData);
			}
		}
	}

	public function shoppingCartAfterProductName(ShoppingCartProduct &$cartProduct)
	{
		if ($cartProduct->hasInfo('ReservationInfo')){
			$resData = $cartProduct->getInfo('ReservationInfo');
			if ($resData && $resData['start_date']->getTimestamp() > 0){
				return $this->parse_reservation_info($cartProduct->getIdString(), $resData);
			}
		}
	}

	public function formatOrdersReservationArray($resData)
	{
		$returningArray = array(
			'start_date'       => (isset($resData['start_date']) ? $resData['start_date'] : new DateTime()),
			'end_date'         => (isset($resData['end_date']) ? $resData['end_date'] : new DateTime()),
			'rental_state'     => (isset($resData['rental_state']) ? $resData['rental_state'] : null),
			'date_shipped'     => (isset($resData['date_shipped']) ? $resData['date_shipped'] : 0),
			'date_returned'    => (isset($resData['date_returned']) ? $resData['date_returned'] : 0),
			'broken'           => (isset($resData['broken']) ? $resData['broken'] : 0),
			'parent_id'        => (isset($resData['parent_id']) ? $resData['parent_id'] : null),
			'deposit_amount'   => $this->getDepositAmount(),
			'semester_name'    => (isset($resData['semester_name']) ? $resData['semester_name'] : ''),
			'event_name'       => (isset($resData['event_name']) ? $resData['event_name'] : ''),
			'event_gate'       => (isset($resData['event_gate']) ? $resData['event_gate'] : ''),
			'event_date'       => (isset($resData['event_date']) ? $resData['event_date'] : new DateTime()),
			'shipping'         => array(
				'module'      => 'reservation',
				'id'          => (isset($resData['shipping_method']) ? $resData['shipping_method'] : 'method1'),
				'title'       => (isset($resData['shipping_method_title']) ? $resData['shipping_method_title'] : null),
				'cost'        => (isset($resData['shipping_cost']) ? $resData['shipping_cost'] : 0),
				'days_before' => (isset($resData['shipping_days_before']) ? $resData['shipping_days_before'] : 0),
				'days_after'  => (isset($resData['shipping_days_after']) ? $resData['shipping_days_after'] : 0)
			)
		);

		EventManager::notify('ReservationFormatOrdersReservationArray', &$returningArray, $resData);
		return $returningArray;
	}

	public function showShoppingCartProductInfo(ShoppingCartProduct $CartProduct, $settings = array())
	{
		$options = array_merge(array(
			'showReservationInfo' => true
		), $settings);

		//print_r($orderedProduct);
		//itwExit();
		if ($options['showReservationInfo'] === true){
			$resData = $CartProduct->getData('ReservationInfo');
			if ($resData && $resData['start_date']->getTimestamp() > 0){
				return PurchaseType_reservation_utilities::parse_reservation_info($resData);
			}
		}
		return '';
	}

	public function orderAfterEditProductName(OrderedProduct &$orderedProduct)
	{
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
			if (isset($resData['ReservationInfo'])){
				$resInfo = $resData['ReservationInfo'];
			}
		}
		$id = $orderedProduct->getId();

		$return .= '<br /><small><b><i><u>' . sysLanguage::get('TEXT_INFO_RESERVATION_INFO') . '</u></i></b>&nbsp;' . '</small>';
		/*This part will have to be changed for events*/

		/**/

		if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_EVENTS') == 'False'){
			if (is_null($resInfo) === false){
				$startDate = $resInfo['start_date'];
				$endDate = $resInfo['end_date']->format(DATE_TIMESTAMP);
			}
			else {
				$startDate = new SesDateTime();
				$endDate = new SesDatetime();
			}
			$changeButton = htmlBase::newElement('button')
				->setText('Select Dates')
				->addClass('reservationDates');
			$return .= '<br /><small><i> - Start Date: <span class="res_start_date">' . $startDate->format(DATE_TIMESTAMP) . '</span><br/>- End Date: <span class="res_end_date">' . $endDate->format(DATE_TIMESTAMP) . '</span>' . $changeButton->draw() . '<input type="hidden" class="ui-widget-content resDateHidden" name="product[' . $id . '][reservation][dates]" value="' . $startDate->format(DATE_TIMESTAMP) . ',' . $endDate->format(DATE_TIMESTAMP) . '"></i></small><div class="selectDialog"></div>';
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
					if (isset($resInfo['event_gate']) && $resInfo['event_gate'] == $iGate['events_name']){
						$gateb->selectOptionByValue($iGate['events_id']);
					}
				}
			}

			$return .= '<br /><small><i> - Events ' . $eventb->draw() . '</i></small>'; //use gates too in OC
			$htmlHasInsurance = htmlBase::newElement('input')
				->setType('checkbox')
				->setLabel('Has insurance')
				->setLabelPosition('after')
				->setName('eventInsurance')
				->addClass('eventInsurance')
				->setValue('1');
			$return .= '<br/>' . $htmlHasInsurance->draw();
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

	public function hasInventory($myQty = 1)
	{

		if ($this->canUseInventory() === false){
			return ($this->isEnabled());
		}
		$hasInv = false;
		if ($this->overBookingAllowed()){
			return true;
		}
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
						if ($myQty === false){
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

		if (!empty($this->_invItems)){
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
							if (isset($datesArr[0]) && !empty($datesArr[0])){
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
					foreach($this->_invItems[$this->getConfigData('INVENTORY_STATUS_AVAILABLE')] as $SerialNumber){
						$bookingInfo = array(
							'serial'   => $SerialNumber
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

					if ($myQty === false){
						if (Session::exists('isppr_product_qty')){
							$bookingInfo['quantity'] = (int)Session::get('isppr_product_qty');
						}
						else {
							$bookingInfo['quantity'] = 1;
						}
					}
					else {
						$bookingInfo['quantity'] = $myQty;
					}
					if ($invElem - $bookingInfo['quantity'] < 0){
						$hasInv = false;
					}
					else {
						$hasInv = true;
					}

					if ($hasInv == false){
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

						array_filter($datesArrb, array($this, 'isIn'));
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

	private function isIn($var)
	{
		if (in_array($var, Session::get('noInvDates'))){
			return false;
		}
		return true;
	}

	public function updateStock($orderId, $orderProductId, ShoppingCartProduct &$cartProduct)
	{
		return false;
	}

	public function processRemoveFromCart()
	{
		global $ShoppingCart;
		if (isset($ShoppingCart->reservationInfo)){
			if ($ShoppingCart->countContents() <= 0){
				unset($ShoppingCart->reservationInfo);
			}
		}
	}

	public function processAddToOrderOrCart(&$resInfo, &$pInfo)
	{
		global $App, $ShoppingCart;

		$pInfo['ReservationInfo'] = array(
			'start_date'    => $resInfo['start_date'],
			'end_date'      => $resInfo['end_date'],
			'quantity'      => $resInfo['quantity']
		);

		if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_EVENTS') == 'True'){
			$pInfo['ReservationInfo']['event_date'] = $resInfo['event_date'];
			$pInfo['ReservationInfo']['event_name'] = $resInfo['event_name'];
			if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_GATES') == 'True'){
				if (isset($resInfo['event_gate'])){
					$pInfo['ReservationInfo']['event_gate'] = $resInfo['event_gate'];
				}
			}
		}
		if (isset($resInfo['semester_name'])){
			$pInfo['ReservationInfo']['semester_name'] = $resInfo['semester_name'];
		}
		else {
			$pInfo['ReservationInfo']['semester_name'] = '';
		}

		$pricing = $this->figureProductPricing($pInfo['ReservationInfo']);

		$shippingMethod = $resInfo['shipping_method'];
		$rShipping = false;
		if (isset($shippingMethod) && !empty($shippingMethod) && ($shippingMethod != 'zonereservation') && ($shippingMethod != 'upsreservation')){
			$shippingModule = $resInfo['shipping_module'];
			$Module = OrderShippingModules::getModule($shippingModule);
			$totalPrice = 0;
			$weight = 0;
			if (is_object($Module) && $Module->getType() == 'Order' && $App->getEnv() == 'catalog'){

				foreach($ShoppingCart->getProducts() as $cartProduct){
					if ($cartProduct->hasInfo('ReservationInfo') === true){
						$reservationInfo1 = $cartProduct->getInfo();
						$cost = 0;
						if (isset($reservationInfo1['ReservationInfo']['shipping']['cost'])){
							$cost = $reservationInfo1['ReservationInfo']['shipping']['cost'];
						}
						$totalPrice += $cartProduct->getFinalPrice(true) * $cartProduct->getQuantity() - $cost * $cartProduct->getQuantity();
						$weight += $cartProduct->getWeight();
						if (isset($reservationInfo1['ReservationInfo']['shipping']) && isset($reservationInfo1['ReservationInfo']['shipping']['module']) && $reservationInfo1['ReservationInfo']['shipping']['module'] == 'zonereservation' && $reservationInfo1['ReservationInfo']['shipping']['module'] == 'upsreservation'){
							$reservationInfo1['ReservationInfo']['shipping']['id'] = $shippingMethod;
							$cartProduct->updateInfo($reservationInfo1, false);
						}
					}
				}
			}
			if (isset($resInfo['quantity'])){
				$total_weight = (int)$resInfo['quantity'] * $pInfo['weight'];
			}
			else {
				$total_weight = $pInfo['weight'];
			}
			if (isset($pricing)){
				$totalPrice += $pricing['price'];
			}

			if (is_object($Module)){
				$quote = $Module->quote($shippingMethod, $total_weight, $totalPrice);

				$rShipping = array(
					'title'  => (isset($quote['methods'][0]['title']) ? $quote['methods'][0]['title'] : ''),
					'cost'   => (isset($quote['methods'][0]['cost']) ? $quote['methods'][0]['cost'] : ''),
					'id'     => (isset($quote['methods'][0]['id']) ? $quote['methods'][0]['id'] : ''),
					'module' => $shippingModule
				);
			}
			else {
				$rShipping = array(
					'title'  => '',
					'cost'   => '',
					'id'     => '',
					'module' => $shippingModule
				);
			}

			if (isset($resInfo['days_before'])){
				$rShipping['days_before'] = $resInfo['days_before'];
			}

			if (isset($resInfo['days_after'])){
				$rShipping['days_after'] = $resInfo['days_after'];
			}

			if (is_object($Module) && $Module->getType() == 'Order' && $App->getEnv() == 'catalog' && sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_ONE_SHIPPING_METHOD') == 'True'){

				foreach($ShoppingCart->getProducts() as $cartProduct){
					if ($cartProduct->hasInfo('ReservationInfo') === true){

						$reservationInfo1 = $cartProduct->getInfo();
						$cost = 0;
						if (isset($reservationInfo1['ReservationInfo']['shipping']['cost'])){
							$cost = $reservationInfo1['ReservationInfo']['shipping']['cost'];
						}
						$reservationInfo1['ReservationInfo']['shipping'] = $rShipping;
						$reservationInfo1['price'] -= $cost;
						$reservationInfo1['final_price'] -= $cost;

						$cartProduct->updateInfo($reservationInfo1, false);
					}
				}
			}
		}

		$pInfo['ReservationInfo']['shipping'] = $rShipping;

		if (isset($pricing)){
			$pInfo['price'] = $pricing['price'];
			$pInfo['ReservationInfo']['deposit_amount'] = $this->getDepositAmount();
			if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_DATE_SELECTION') == 'Using calendar after browsing products and clicking Reserve'){
				$pInfo['final_price'] = $pricing['price'];
			}
			else {
				$pInfo['final_price'] = $pricing['price']; //+ $pInfo['ReservationInfo']['deposit_amount'];
			}
		}
	}

	public function processAddToOrder(array &$pInfo)
	{
		if (isset($pInfo['OrdersProductsReservation'])){
			$infoArray = array(
				'shipping_method'   => $pInfo['OrdersProductsReservation'][0]['shipping_method'],
				'start_date'        => $pInfo['OrdersProductsReservation'][0]['start_date'],
				'end_date'          => $pInfo['OrdersProductsReservation'][0]['end_date'],
				'days_before'       => $pInfo['OrdersProductsReservation'][0]['days_before'],
				'days_after'        => $pInfo['OrdersProductsReservation'][0]['days_after'],
				'quantity'          => $pInfo['products_quantity']
			);
			if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_UPS_RESERVATION') == 'False'){
				$infoArray['shipping_module'] = 'zonereservation';
			}
			else {
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
				'shipping_method'   => 'method1', //?
				'start_date'        => new SesDateTime(),
				'end_date'          => new SesDateTime(),
				'days_before'       => 0,
				'days_after'        => 0,
				'quantity'          => $pInfo['products_quantity']
			);
			if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_UPS_RESERVATION') == 'False'){
				$infoArray['shipping_module'] = 'zonereservation';
			}
			else {
				$infoArray['shipping_module'] = 'upsreservation';
			}
			if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_EVENTS') == 'True'){
				$infoArray['event_date'] = new SesDateTime();
				$infoArray['event_name'] = '';
				if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_GATES') == 'True'){
					$infoArray['event_gate'] = '';
				}
			}
			$infoArray['semester_name'] = '';
		}
		$this->processAddToOrderOrCart($infoArray, $pInfo);

		EventManager::notify('ReservationProcessAddToOrder', &$pInfo);
	}

	public function addToCartPrepare(array &$CartProductData)
	{
		if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_UPS_RESERVATION') == 'False'){
			$shippingInfo = array(
				'zonereservation',
				'zonereservation'
			);
		}
		else {
			$shippingInfo = array(
				'upsreservation',
				'upsreservation'
			);
		}

		if (isset($_POST['rental_shipping']) && $_POST['rental_shipping'] !== false){
			$shippingInfo = explode('_', $_POST['rental_shipping']);
		}

		if (isset($_POST['start_date']) && isset($_POST['end_date']) && isset($_POST['days_before']) && isset($_POST['days_after'])){
			$ReservationInfo = array(
				'shipping_module' => $shippingInfo[0],
				'shipping_method' => $shippingInfo[1],
				'start_date'      => DateTime::createFromFormat(sysLanguage::getDateFormat('short'), $_POST['start_date']),
				'end_date'        => DateTime::createFromFormat(sysLanguage::getDateFormat('short'), $_POST['end_date']),
				'days_before'     => $_POST['days_before'],
				'days_after'      => $_POST['days_after'],
				'quantity'        => $_POST['rental_qty']
			);
		}
		else {
			$ReservationInfo = array(
				'shipping_module' => $CartProductData['ReservationInfo']['shipping']['module'],
				'shipping_method' => $CartProductData['ReservationInfo']['shipping']['id'],
				'start_date'      => $CartProductData['ReservationInfo']['start_date'],
				'end_date'        => $CartProductData['ReservationInfo']['end_date'],
				'days_before'     => $CartProductData['ReservationInfo']['days_before'],
				'days_after'      => $CartProductData['ReservationInfo']['days_after'],
				'quantity'        => $CartProductData['ReservationInfo']['quantity']
			);
		}

		if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_EVENTS') == 'True'){
			if (isset($_POST['event_date']) && isset($_POST['event_name'])){
				$ReservationInfo['event_date'] = $_POST['event_date'];
				$ReservationInfo['event_name'] = $_POST['event_name'];
				if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_GATES') == 'True'){
					$ReservationInfo['event_gate'] = $_POST['event_gate'];
				}
			}
			else {
				$ReservationInfo['event_date'] = $CartProductData['ReservationInfo']['event_date'];
				$ReservationInfo['event_name'] = $CartProductData['ReservationInfo']['event_name'];
				if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_GATES') == 'True'){
					$ReservationInfo['event_gate'] = $CartProductData['ReservationInfo']['event_gate'];
				}
			}
		}

		if (isset($_POST['semester_name'])){
			$ReservationInfo['semester_name'] = $_POST['semester_name'];
		}
		else {
			$ReservationInfo['semester_name'] = $CartProductData['ReservationInfo']['semester_name'];
		}

		$this->processAddToOrderOrCart($ReservationInfo, $CartProductData);

		EventManager::notify('ReservationProcessAddToCart', &$CartProductData['ReservationInfo']);
	}

	/**
	 * @param array $CartProductData
	 * @return bool
	 */
	public function allowAddToCart(array $CartProductData)
	{
		global $ShoppingCart;
		$allowed = true;

		if ($this->getConfigData('INVENTORY_ENABLED') == 'True'){
			if ($this->overBookingAllowed() === true){
				$allowed = true;
			}
			//elseif ($this->getConfigData('INVENTORY_SHOPPING_CART_VERIFY') == 'True'){
			//	$allowed = ($this->getCurrentStock() >= $CartProductData['quantity']);
			//}
		}

		if ($allowed === true){
			$EnabledShipping = $this->getEnabledShippingMethods();
			$ShippingIsNone = $this->shippingIsNone();
			$ShippingIsStore = $this->shippingIsStore();

			foreach($ShoppingCart->getProducts() as $CartProduct){
				$ProductType = $CartProduct
					->getProductClass()
					->getProductTypeClass();
				if (method_exists($ProductType, 'getPurchaseType')){
					if ($ProductType
						->getPurchaseType()
						->getCode() == $this->getCode()
					){
						$ReservationInfo = $CartProduct->getData('ReservationInfo');
						if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_DIFFERENT_SHIPPING_METHODS') == 'False'){
							if ($ReservationInfo['shipping']['id'] != $CartProductData['ReservationInfo']['shipping']['id']){
								$allowed = false;
								$this->addError('You are not allowed to use this level of service with this product. Please choose another level of service');
							}
							elseif (is_array($EnabledShipping)) {
								if (!in_array($CartProductData['ReservationInfo']['shipping']['id'], $EnabledShipping)){
									if (!$ShippingIsNone && !$ShippingIsStore){
										$allowed = false;
										$this->addError('You are not allowed to use this level of service with this product. Please choose another level of service');
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
		}
		else {
			$this->addError('The Product Does Not Have Enough Inventory To Fill The Request.');
		}

		return $allowed;
	}

	public function processUpdateCart(array &$pInfo)
	{
		global $ShoppingCart, $App;
		$reservationInfo =& $pInfo['ReservationInfo'];

		$pInfo['quantity'] = $reservationInfo['quantity'];

		$pricing = $this->figureProductPricing($reservationInfo);

		if (isset($pInfo['ReservationInfo']['shipping']['module']) && isset($pInfo['ReservationInfo']['shipping']['id'])){

			$shipping_modules = OrderShippingModules::getModule($pInfo['ReservationInfo']['shipping']['module']);

			$totalPrice = 0;
			$weight = 0;
			if (is_object($shipping_modules) && $shipping_modules->getType() == 'Order' && $App->getEnv() == 'catalog'){

				foreach($ShoppingCart->getProducts() as $cartProduct){
					if ($cartProduct->hasInfo('ReservationInfo') === true && $cartProduct->getUniqID() != $pInfo['uniqID']){
						$reservationInfo1 = $cartProduct->getInfo('ReservationInfo');
						$cost = 0;
						if (isset($reservationInfo1['shipping']['cost'])){
							$cost = $reservationInfo1['shipping']['cost'];
						}
						$totalPrice += $cartProduct->getFinalPrice(true) * $cartProduct->getQuantity() - $cost * $cartProduct->getQuantity();
						$weight += $cartProduct->getWeight();
					}
				}
			}

			if (isset($pInfo['ReservationInfo']['quantity'])){
				$total_weight = (int)$pInfo['ReservationInfo']['quantity'] * $pInfo['weight'];
			}
			else {
				$total_weight = $pInfo['weight'];
			}
			if (isset($pricing)){
				$totalPrice += (float)$pricing['price'];
			}

			$quotes = $shipping_modules->quote($pInfo['ReservationInfo']['shipping']['id'], $total_weight + $weight, $totalPrice);
			$reservationInfo['shipping'] = array(
				'title'        => isset($quotes[0]['methods'][0]['title']) ? $quotes[0]['methods'][0]['title'] : $quotes['methods'][0]['title'],
				'cost'         => isset($quotes[0]['methods'][0]['cost']) ? $quotes[0]['methods'][0]['cost'] : $quotes['methods'][0]['cost'],
				'id'           => isset($quotes[0]['methods'][0]['id']) ? $quotes[0]['methods'][0]['id'] : $quotes['methods'][0]['id'],
				'module'       => $pInfo['ReservationInfo']['shipping']['module'],
				'days_before'  => $pInfo['ReservationInfo']['shipping']['days_before'],
				'days_after'   => $pInfo['ReservationInfo']['shipping']['days_after']
			);
		}

		if (isset($pricing)){
			$pInfo['price'] = $pricing['price'];
			$pInfo['ReservationInfo']['deposit_amount'] = $this->getDepositAmount();
			if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_DATE_SELECTION') != 'Using calendar after browsing products and clicking Reserve'){
				$pInfo['final_price'] = $pricing['price']; //+ $pInfo['ReservationInfo']['deposit_amount'];
			}
			else {
				$pInfo['final_price'] = $pricing['price'];
			}
		}
	}

	/*
	 * Get Available Barcode Function
	 */

	public function getAvailableSerial(&$excluded, $usableSerials = array())
	{
		$barcodeID = -1;
		$ReservationInfo = $this->pInfo;
		//echo __FILE__ . '::' . __LINE__ . '::<pre>';print_r($this);

		if (is_array($excluded) === false){
			$excluded = array();
		}

		if ($barcodeID == -1){
			/**
			 * @TODO: This really needs to be standardized
			 */
			if (isset($ReservationInfo['shipping_days_before'])){
				$shippingDaysBefore = (int)$ReservationInfo['shipping_days_before'];
			}
			elseif (isset($ReservationInfo['shipping']['days_before'])) {
				$shippingDaysBefore = (int)$ReservationInfo['shipping']['days_before'];
			}
			elseif (isset($ReservationInfo['days_before'])) {
				$shippingDaysBefore = (int)$ReservationInfo['days_before'];
			}
			else {
				$shippingDaysBefore = 0;
			}

			/**
			 * @TODO: This really needs to be standardized
			 */
			if (isset($ReservationInfo['shipping_days_after'])){
				$shippingDaysAfter = (int)$ReservationInfo['shipping_days_after'];
			}
			elseif (isset($ReservationInfo['shipping']['days_after'])) {
				$shippingDaysAfter = (int)$ReservationInfo['shipping']['days_after'];
			}
			elseif (isset($ReservationInfo['days_after'])) {
				$shippingDaysAfter = (int)$ReservationInfo['days_after'];
			}
			else {
				$shippingDaysAfter = 0;
			}

			$startDate = $ReservationInfo['start_date']->modify('-' . $shippingDaysBefore . ' Day');
			$endDate = $ReservationInfo['end_date']->modify('+' . $shippingDaysAfter . ' Day');

			if (is_array($this->_invItems)){
				$AvailableItems = $this->_invItems[$this->getConfigData('INVENTORY_STATUS_AVAILABLE')]['serials'];
				if (sizeof($AvailableItems) > 0){
					foreach($AvailableItems as $SerialNumber){
						if (in_array($SerialNumber, $excluded) === false){
							$barcodeID = $SerialNumber;
							break;
						}
					}
				}

				if ($barcodeID == -1){
					$checkSerials = array();
					if (isset($this->_invItems[$this->getConfigData('INVENTORY_STATUS_RESERVED')]['serials'])){
						$checkSerials = array_merge($checkSerials, $this->_invItems[$this->getConfigData('INVENTORY_STATUS_RESERVED')]['serials']);
					}
					if (isset($this->_invItems[$this->getConfigData('INVENTORY_STATUS_OUT')]['serials'])){
						$checkSerials = array_merge($checkSerials, $this->_invItems[$this->getConfigData('INVENTORY_STATUS_OUT')]['serials']);
					}

					/**
					 * If there's no serials then we cannot check anything
					 */
					if (sizeof($checkSerials) == 0){
						return -1;
					}

					foreach($checkSerials as $SerialNumber){
						if (count($usableSerials) == 0 || in_array($SerialNumber, $usableBarcodes)){
							if (is_array($excluded) && in_array($SerialNumber, $excluded)){
								continue;
							}

							$bookingInfo = array(
								'serial_number'    => $SerialNumber,
								'start_date'       => $startDate,
								'end_date'         => $endDate
							);
							if (Session::exists('isppr_inventory_pickup')){
								$pickupCheck = Session::get('isppr_inventory_pickup');
								if (!empty($pickupCheck)){
									$bookingInfo['inventory_center_pickup'] = $pickupCheck;
								}
							}
							$bookingInfo['quantity'] = 1;
							//if allow overbooking is enabled what barcode should be chosen.. I think any is good.
							$bookingCount = ReservationUtilities::CheckBooking($bookingInfo);
							if ($bookingCount <= 0 || sysConfig::get('EXTENSION_PAY_PER_RENTALS_SHOW_STOCK') == 'True'){
								$barcodeID = $SerialNumber;
								break;
							}
						}
					}
				}
			}
		}

		if ($barcodeID != -1){
			$excluded[] = $barcodeID;
		}
		return $barcodeID;
	}

	public function getPurchaseHtml($key)
	{
		global $currencies;
		$return = null;
		switch($key){
			case 'product_info':
				//if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_SHOW_CALENDAR_PRODUCT_INFO') == 'False') {

				$priceTableHtml = '';
				//if ($canReserveDaily || $canReserveWeekly || $canReserveMonthly || $canReserve6Months || $canReserve1Year || $canReserve3Years || $canReserveHourly || $canReserveTwoHours || $canReserveFourHours){
				$priceTable = htmlBase::newElement('table')
					->setCellPadding(3)
					->setCellSpacing(0)
					->attr('align', 'center');

				foreach(PurchaseType_reservation_utilities::getRentalPricing($this->getPayPerRentalId()) as $iPrices){
					$priceHolder = htmlBase::newElement('span')
						->css(array(
						'font-size'   => '1.3em',
						'font-weight' => 'bold'
					))
						->html($this->displayReservePrice($iPrices['price']));

					$perHolder = htmlBase::newElement('span')
						->css(array(
						'white-space' => 'nowrap',
						'font-size'   => '1.1em',
						'font-weight' => 'bold'
					))
						->html($iPrices['PricePayPerRentalPerProductsDescription'][0]['price_per_rental_per_products_name']);

					$priceTable->addBodyRow(array(
						'columns' => array(
							array(
								'addCls' => 'main',
								'align'  => 'right',
								'text'   => $priceHolder->draw()
							),
							array(
								'addCls' => 'main',
								'align'  => 'left',
								'text'   => $perHolder->draw()
							)
						)
					));
				}

				if ($this->getDepositAmount() > 0){
					$priceHolder = htmlBase::newElement('span')
						->css(array(
						'font-size'   => '1.1em',
						'font-weight' => 'bold'
					))
						->html($currencies->format($this->getDepositAmount()));

					$infoIcon = htmlBase::newElement('icon')
						->setType('info')
						->attr('onclick', 'popupWindow(\'' . itw_app_link('appExt=infoPages&dialog=true', 'show_page', 'ppr_deposit_info') . '\',400,300);')
						->css(array(
						'display' => 'inline-block',
						'cursor'  => 'pointer'
					));

					$perHolder = htmlBase::newElement('span')
						->css(array(
						'white-space' => 'nowrap',
						'font-size'   => '1.0em',
						'font-weight' => 'bold'
					))
						->html(' - Deposit ' . $infoIcon->draw());

					$priceTable->addBodyRow(array(
						'columns' => array(
							array(
								'addCls' => 'main',
								'align'  => 'right',
								'text'   => $priceHolder->draw()
							),
							array(
								'addCls' => 'main',
								'align'  => 'left',
								'text'   => $perHolder->draw()
							)
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
						->addClass('pprResButton')
						->setText(sysLanguage::get('TEXT_BUTTON_PAY_PER_RENTAL'));

					if ($this->hasInventory() === false){
						$button->disable();
					}

					if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_ZIPCODES_SHIPPING') == 'True'){
						ob_start();
						?>
					<script type="text/javascript">
						$(document).ready(function () {
							var hasZip = <?php echo (Session::exists('zipClient') == false ? 'false' : 'true');?>;
							$('.pprResButton').click(function () {
								var self = $(this);
								if (hasZip == false){
									$('<div id="dialog-mesage-ppr" title="Select Zip"><div class="zipBD"><span class="zip_text">Zip: </span><input class="zipInput" name="zipClient" ></div></div>').dialog({
										modal    : false,
										autoOpen : true,
										buttons  : {
											Submit : function () {
												var dial = $(this);
												$.ajax({
													cache    : false,
													url      : js_app_link('appExt=multiStore&app=zip&appPage=default&action=selectZip'),
													type     : 'post',
													data     : $('#dialog-mesage-ppr *').serialize(),
													dataType : 'json',
													success  : function (data) {
														hasZip = true;
														dial.dialog("close");
														self.click();
													}
												});
											}
										}
									});
									return false;
								}
							});
						});

					</script>
					<?php
						$scriptBut = ob_get_contents();
						ob_end_clean();
						$priceTableHtmlPrices .= $scriptBut;
					}

					$link = itw_app_link('appExt=payPerRentals&products_id=' . $_GET['products_id'], 'build_reservation', 'default');

					$return = array(
						'form_action'   => $link,
						'purchase_type' => $this,
						'allowQty'      => false,
						'header'        => $this->getTitle(),
						'content'       => $priceTableHtmlPrices,
						'button'        => $button
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
							if (Session::exists('isppr_shipping_method')){

								if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_UPS_RESERVATION') == 'False'){
									if (Session::exists('isppr_shipping_cost')){
										$ship_cost = (float)Session::get('isppr_shipping_cost');
									}
								}
								else {
									if (isset($_POST['rental_shipping'])){
										$isR = true;
										$isRV = $_POST['rental_shipping'];
									}
									$_POST['rental_shipping'] = 'upsreservation_' . Session::get('isppr_shipping_method');
								}
							}
							else {
								//here i should check for use_ship
								if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_SHIP') == 'True'){
									$payPerRentalButton
										->disable()
										->addClass('no_shipping');
								}
							}
							$thePrice = 0;
							$rInfo = '';
							$price = $this->getReservationPrice($start_date, $end_date, $rInfo, '', (sysConfig::get('EXTENSION_PAY_PER_RENTALS_INSURE_ALL_PRODUCTS_AUTO') == 'True'));
							$thePrice += $price['price'];
							if (Session::exists('isppr_event_multiple_dates')){
								$thePrice = 0;
								$datesArr = Session::get('isppr_event_multiple_dates');

								foreach($datesArr as $iDate){
									$price = $this->getReservationPrice($iDate, $iDate, $rInfo, '', (sysConfig::get('EXTENSION_PAY_PER_RENTALS_INSURE_ALL_PRODUCTS_AUTO') == 'True'));
									$thePrice += $price['price'];
								}
							}

							$pricing = $currencies->format($qtyVal * $thePrice + $ship_cost);
							if (!$isR){
								unset($_POST['rental_shipping']);
							}
							else {
								$_POST['rental_shipping'] = $isRV;
							}
							$pageForm = htmlBase::newElement('div');

							if (isset($start_date)){
								$htmlStartDate = htmlBase::newElement('input')
									->setType('hidden')
									->setName('start_date')
									->setValue($start_date);
							}

							if (isset($days_before)){
								$htmlDaysBefore = htmlBase::newElement('input')
									->setType('hidden')
									->setName('days_before')
									->setValue($days_before);
							}

							if (isset($days_after)){
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
							if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_SHOW_QTY_LISTING') == 'False'){
								$htmlRentalQty->setType('hidden');
							}
							else {
								$htmlRentalQty->attr('size', '3');
							}
							$htmlRentalQty
								->setName('rental_qty')
								->setValue($qtyVal);
							if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_INSURE_ALL_PRODUCTS_AUTO') == 'True'){
								$htmlHasInsurance = htmlBase::newElement('input')
									->setType('hidden')
									->setName('hasInsurance')
									->setValue('1');
								$pageForm->append($htmlHasInsurance);
							}
							$htmlProductsId = htmlBase::newElement('input')
								->setType('hidden')
								->setName('products_id')
								->setValue($_GET['products_id']);
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
							if (isset($htmlDaysBefore)){
								$pageForm->append($htmlDaysBefore);
							}

							if (isset($htmlDaysAfter)){
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

							if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_EVENTS') == 'True'){
								$pageForm
									->append($htmlEventDate)
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
								}
								else {
									$htmlShippingDays->setValue("upsreservation_" . Session::get('isppr_shipping_method'));
								}
								$pageForm->append($htmlShippingDays);
							}

							$priceHolder = htmlBase::newElement('span')
								->css(array(
								'font-size'   => '1.3em',
								'font-weight' => 'bold'
							))
								->html($pricing);

							$perHolder = htmlBase::newElement('span')
								->css(array(
								'white-space' => 'nowrap',
								'font-size'   => '1.1em',
								'font-weight' => 'bold'
							))
								->html('Price per selected period');
							if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_SHOW_PRICE_SELECTED_PERIOD_PRODUCT_INFO') == 'True'){
								$priceTable->addBodyRow(array(
									'columns' => array(
										array(
											'addCls' => 'main',
											'align'  => 'right',
											'text'   => $priceHolder->draw()
										),
										array(
											'addCls' => 'main',
											'align'  => 'left',
											'text'   => $perHolder->draw()
										)
									)
								));
								$pageForm->append($priceTable);
							}
							$priceTableHtml = $pageForm->draw();
							$script = '';
							if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_PRODUCT_INFO_DATES') == 'True'){
								ob_start();
								?>
							<script type="text/javascript">
								function nobeforeDays(date) {
									today = new Date();
									if (today.getTime() <= date.getTime() - (1000 * 60 * 60 * 24 * <?php echo $datePadding;?> -(24 - date.getHours()) * 1000 * 60 * 60)){
										return [true, ''];
									}
									else {
										return [false, ''];
									}
								}
								function makeDatePicker(pickerID) {
									var minRentalDays = <?php
										if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_GLOBAL_MIN_RENTAL_DAYS') == 'True'){
											echo (int)sysConfig::get('EXTENSION_PAY_PER_RENTALS_MIN_RENTAL_DAYS');
											$minDays = (int)sysConfig::get('EXTENSION_PAY_PER_RENTALS_MIN_RENTAL_DAYS');
										}
										else {
											$minDays = 0;
											echo '0';
										}
										if (Session::exists('button_text')){
											$butText = Session::get('button_text');
										}
										else {
											$butText = '';
										}
										?>;
									var selectedDateId = null;
									var startSelectedDate;

									var dates = $(pickerID + ' .dstart,' + pickerID + ' .dend').datepicker({
										dateFormat    : '<?php echo getJsDateFormat(); ?>',
										changeMonth   : true,
										beforeShowDay : nobeforeDays,
										onSelect      : function (selectedDate) {

											var option = this.id == "dstart" ? "minDate" : "maxDate";
											if ($(this).hasClass('dstart')){
												myid = "dstart";
												option = "minDate";
											}
											else {
												myid = "dend";
												option = "maxDate";
											}
											var instance = $(this).data("datepicker");
											var date = $.datepicker.parseDate(instance.settings.dateFormat || $.datepicker._defaults.dateFormat, selectedDate, instance.settings);

											var dateC = new Date('<?php echo (Session::exists('isppr_curDate') ? Session::get('isppr_curDate') : '01-01-2011');?>');
											if (date.getTime() == dateC.getTime()){
												if (myid == "dstart"){
													$(this).closest('form').find('.hstart').html('<?php echo (Session::exists('isppr_selectOptionscurdays') ? Session::get('isppr_selectOptionscurdays') : '1');?>');
												}
												else {
													$(this).closest('form').find('.hend').html('<?php echo (Session::exists('isppr_selectOptionscurdaye') ? Session::get('isppr_selectOptionscurdaye') : '1');?>');
												}
											}
											else {
												if (myid == "dstart"){
													$(this).closest('form').find('.hstart').html('<?php echo (Session::exists('isppr_selectOptionsnormaldays') ? Session::get('isppr_selectOptionsnormaldays') : '1');?>');
												}
												else {
													$(this).closest('form').find('.hend').html('<?php echo (Session::exists('isppr_selectOptionsnormaldaye') ? Session::get('isppr_selectOptionsnormaldaye') : '1');?>');
												}
											}

											if (myid == "dstart"){
												var days = "0";
												if ($(this).closest('form').find('select.pickupz option:selected').attr('days')){
													days = $(this).closest('form').find('select.pickupz option:selected').attr('days');
												}
												//startSelectedDate = new Date(selectedDate);
												dateFut = new Date(date.setDate(date.getDate() + parseInt(days)));
												dates.not(this).datepicker("option", option, dateFut);
											}
											f = true;
											if (myid == "dend"){
												datest = new Date(selectedDate);
												if ($(this).closest('form').find('.dstart').val() != ''){
													startSelectedDate = new Date($(this).closest('form').find('.dstart').val());
													if (datest.getTime() - startSelectedDate.getTime() < minRentalDays * 24 * 60 * 60 * 1000){
														alert('<?php echo sprintf(sysLanguage::get('EXTENSION_PAY_PER_RENTALS_ERROR_MIN_DAYS'), $minDays);?>');
														$(this).val('');
														f = false;
													}
												}
												else {
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
								$(document).ready(function () {
									$('.no_dates_selected').each(function () {
										$(this).click(function () {
											<?php
											if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_PRODUCT_INFO_DATES') == 'True'){
												?>
												$('<div id="dialog-mesage" title="Choose Dates"><input class="tField" name="tField" ><div class="destBD"><span class="start_text">Start: </span><input class="picker dstart" name="dstart" ></div><div class="destBD"><span class="end_text">End: </span><input class="picker dend" name="dend" ></div><?php echo sysConfig::get('EXTENSION_PAY_PER_RENTALS_INFOBOX_CONTENT');?></div>').dialog({
													modal    : false,
													autoOpen : true,
													open     : function (e, ui) {
														makeDatePicker('#dialog-mesage');
														$(this).find('.tField').hide();
													},
													buttons  : {
														Submit : function () {

															$('.dstart').val($(this).find('.dstart').val());
															$('.dend').val($(this).find('.dend').val());
															$('.rentbbut').trigger('click');
															$(this).dialog("close");
														}
													}
												});
												<?php
											}
											else {
												?>
												alert('No dates selected');
												<?php } ?>
											return false;
										})
									});
									$('.no_inventory').each(function () {
										$(this).click(function () {

											$('<div id="dialog-mesage" title="No Inventory"><span style="color:red;font-size:18px;"><?php echo sysLanguage::get('EXTENSION_PAY_PER_RENTALS_ERROR_NO_INVENTORY_FOR_SELECTED_DATES');?></span></div>').dialog({
												modal   : true,
												buttons : {
													Ok : function () {
														$(this).dialog("close");
													}
												}
											});

											return false;
										})
									});
								});
							</script>
							<?php
								$script = ob_get_contents();
								ob_end_clean();
							}
							$return = array(
								'form_action'   => itw_app_link('appExt=payPerRentals&products_id=' . $_GET['products_id'], 'build_reservation', 'default'),
								'purchase_type' => $this->getCode(),
								'allowQty'      => false,
								'header'        => $this->getTitle(),
								'content'       => $priceTableHtmlPrices . $priceTableHtml . $script,
								'button'        => $payPerRentalButton
							);
						}
					}
					else {
						$payPerRentalButton = htmlBase::newElement('button')
							->setType('submit')
							->setText(sysLanguage::get('TEXT_BUTTON_RESERVE'));

						if ($this->hasInventory() === false && Session::exists('isppr_selected') && Session::get('isppr_selected') == true){
							$payPerRentalButton->addClass('no_inventory');
							$payPerRentalButton->setText(sysLanguage::get('TEXT_BUTTON_RESERVE_OUT_OF_STOCK'));
						}
						else {
							$payPerRentalButton->addClass('no_dates_selected');
						}
						$script = '';
						if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_PRODUCT_INFO_DATES') == 'True'){
							ob_start();
							?>
						<script type="text/javascript">
							function nobeforeDays(date) {
								today = new Date();
								if (today.getTime() <= date.getTime() - (1000 * 60 * 60 * 24 * <?php echo $datePadding;?> -(24 - date.getHours()) * 1000 * 60 * 60)){
									return [true, ''];
								}
								else {
									return [false, ''];
								}
							}
							function makeDatePicker(pickerID) {
								var minRentalDays = <?php
									if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_GLOBAL_MIN_RENTAL_DAYS') == 'True'){
										echo (int)sysConfig::get('EXTENSION_PAY_PER_RENTALS_MIN_RENTAL_DAYS');
										$minDays = (int)sysConfig::get('EXTENSION_PAY_PER_RENTALS_MIN_RENTAL_DAYS');
									}
									else {
										$minDays = 0;
										echo '0';
									}
									if (Session::exists('button_text')){
										$butText = Session::get('button_text');
									}
									else {
										$butText = '';
									}
									?>;
								var selectedDateId = null;
								var startSelectedDate;

								var dates = $(pickerID + ' .dstart,' + pickerID + ' .dend').datepicker({
									dateFormat    : '<?php echo getJsDateFormat(); ?>',
									changeMonth   : true,
									beforeShowDay : nobeforeDays,
									onSelect      : function (selectedDate) {

										var option = this.id == "dstart" ? "minDate" : "maxDate";
										if ($(this).hasClass('dstart')){
											myid = "dstart";
											option = "minDate";
										}
										else {
											myid = "dend";
											option = "maxDate";
										}
										var instance = $(this).data("datepicker");
										var date = $.datepicker.parseDate(instance.settings.dateFormat || $.datepicker._defaults.dateFormat, selectedDate, instance.settings);

										var dateC = new Date('<?php echo (Session::exists('isppr_curDate') ? Session::get('isppr_curDate') : '01-01-2011');?>');
										if (date.getTime() == dateC.getTime()){
											if (myid == "dstart"){
												$(this).closest('form').find('.hstart').html('<?php echo (Session::exists('isppr_selectOptionscurdays') ? Session::get('isppr_selectOptionscurdays') : '1');?>');
											}
											else {
												$(this).closest('form').find('.hend').html('<?php echo (Session::exists('isppr_selectOptionscurdaye') ? Session::get('isppr_selectOptionscurdaye') : '1');?>');
											}
										}
										else {
											if (myid == "dstart"){
												$(this).closest('form').find('.hstart').html('<?php echo (Session::exists('isppr_selectOptionsnormaldays') ? Session::get('isppr_selectOptionsnormaldays') : '1');?>');
											}
											else {
												$(this).closest('form').find('.hend').html('<?php echo (Session::exists('isppr_selectOptionsnormaldaye') ? Session::get('isppr_selectOptionsnormaldaye') : '1');?>');
											}
										}

										if (myid == "dstart"){
											var days = "0";
											if ($(this).closest('form').find('select.pickupz option:selected').attr('days')){
												days = $(this).closest('form').find('select.pickupz option:selected').attr('days');
											}
											//startSelectedDate = new Date(selectedDate);
											dateFut = new Date(date.setDate(date.getDate() + parseInt(days)));
											dates.not(this).datepicker("option", option, dateFut);
										}
										f = true;
										if (myid == "dend"){
											datest = new Date(selectedDate);
											if ($(this).closest('form').find('.dstart').val() != ''){
												startSelectedDate = new Date($(this).closest('form').find('.dstart').val());
												if (datest.getTime() - startSelectedDate.getTime() < minRentalDays * 24 * 60 * 60 * 1000){
													alert('<?php echo sprintf(sysLanguage::get('EXTENSION_PAY_PER_RENTALS_ERROR_MIN_DAYS'), $minDays);?>');
													$(this).val('');
													f = false;
												}
											}
											else {
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
							$(document).ready(function () {
								$('.no_dates_selected').each(function () {
									$(this).click(function () {
										<?php
										if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_PRODUCT_INFO_DATES') == 'True'){
											?>
											$('<div id="dialog-mesage" title="Choose Dates"><input class="tField" name="tField" ><div class="destBD"><span class="start_text">Start: </span><input class="picker dstart" name="dstart" ></div><div class="destBD"><span class="end_text">End: </span><input class="picker dend" name="dend" ></div><?php echo sysConfig::get('EXTENSION_PAY_PER_RENTALS_INFOBOX_CONTENT');?></div>').dialog({
												modal    : false,
												autoOpen : true,
												open     : function (e, ui) {
													makeDatePicker('#dialog-mesage');
													$(this).find('.tField').hide();
												},
												buttons  : {
													Submit : function () {

														$('.dstart').val($(this).find('.dstart').val());
														$('.dend').val($(this).find('.dend').val());
														$('.rentbbut').trigger('click');
														$(this).dialog("close");
													}
												}
											});
											<?php
										}
										else {
											?>
											alert('No dates selected');
											<?php }?>
										return false;
									})
								});
								$('.no_inventory').each(function () {
									$(this).click(function () {

										$('<div id="dialog-mesage" title="No Inventory"><span style="color:red;font-size:18px;"><?php echo sysLanguage::get('EXTENSION_PAY_PER_RENTALS_ERROR_NO_INVENTORY_FOR_SELECTED_DATES');?></span></div>').dialog({
											modal   : true,
											buttons : {
												Ok : function () {
													$(this).dialog("close");
												}
											}
										});

										return false;
									})
								});
							});
						</script>
						<?php
							$script = ob_get_contents();
							ob_end_clean();
						}
						$return = array(
							'form_action'   => '#',
							'purchase_type' => $this,
							'allowQty'      => false,
							'header'        => $this->getTitle(),
							'content'       => $priceTableHtmlPrices . $script,
							'button'        => $payPerRentalButton
						);
					}
				}
				//}
				/*else{
									ob_start();
									require(sysConfig::getDirFsCatalog() . 'extensions/payPerRentals/catalog/base_app/build_reservation/pages/default.php');
										echo '<script type="text/javascript" src="'.sysConfig::getDirWsCatalog() . 'extensions/payPerRentals/catalog/base_app/build_reservation/javascript/default.js'.'"></script>';
									$pageHtml = ob_get_contents();
									ob_end_clean();
									$return = array(
												'form_action'   => '',
												'purchase_type' => $this,
												'allowQty'      => false,
												'header'        => $this->getTitle(),
												'content'       => $pageHtml,
												'button'        => ''
									);
									//echo $pageHtml;
							} */
				break;
		}
		return $return;
	}

	public function getDepositAmount()
	{
		return $this->pprInfo['deposit_amount'];
	}

	public function getPriceSemester($semName)
	{
		$QPeriodsNames = Doctrine_Query::create()
			->from('PayPerRentalPeriods')
			->where('period_name=?', $semName)
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
		if (count($QPeriodsNames) > 0){
			$QPricePeriod = Doctrine_Query::create()
				->from('ProductsPayPerPeriods')
				->where('period_id=?', $QPeriodsNames[0]['period_id'])
				->andWhere('products_id=?', $this->getProductId())
				->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
			return $QPricePeriod[0]['price'];
		}
		else {
			return 0;
		}
	}

	public function getReservePrice($type)
	{
		if (isset($this->pprInfo)){
			return $this->pprInfo['price_' . $type];
		}
		return;
	}

	public function displayReservePrice($price)
	{
		global $currencies;
		EventManager::notify('ReservationPriceBeforeSetup', &$price);
		return $currencies->display_price($price, $this->getTaxRate());
	}

	public function hasMaxDays()
	{
		if (isset($this->pprInfo)){
			return $this->pprInfo['max_days'] > 0;
		}
		return false;
	}

	public function hasMaxMonths()
	{
		if (isset($this->pprInfo)){
			return $this->pprInfo['max_months'] > 0;
		}
		return false;
	}

	public function getMaxDays()
	{
		return $this->pprInfo['max_days'];
	}

	public function getMaxMonths()
	{
		return $this->pprInfo['max_months'];
	}

	public function getPricingTable()
	{
		global $currencies;
		$table = '';
		$table .= '<table cellpadding="0" cellspacing="0" border="0">';

		foreach(PurchaseType_reservation_utilities::getRentalPricing($this->getPayPerRentalId()) as $iPrices){
			$table .= '<tr>' .
				'<td class="main">' . $iPrices['PricePayPerRentalPerProductsDescription'][0]['price_per_rental_per_products_name'] . ': </td>' .
				'<td class="main">' . $this->displayReservePrice($iPrices['price']) . '</td>' .

				'</tr>';
		}

		$table .= '</table>';
		return $table;
	}

	public function buildSemesters($semDates)
	{

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
						'checked'   => 1,
						'separator' => '<br />',
						'name'      => 'cal_or_semester',
						'data'      => array(
							array(
								'label'         => sysLanguage::get('TEXT_USE_CALENDAR'),
								'labelPosition' => 'before',
								'addCls'        => 'iscal',
								'value'         => '1'
							),
							array(
								'label'         => sysLanguage::get('TEXT_USE_SEMESTER'),
								'labelPosition' => 'before',
								'addCls'        => 'issem',
								'value'         => '0'
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
								'name'  => 'start_date',
								'value' => $sDate['start_date']
							),
							array(
								'name'  => 'end_date',
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

	public function buildShippingTable()
	{
		global $userAccount, $ShoppingCart, $App;

		if ($this->getShipping() === false){
			return;
		}

		if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_UPS_RESERVATION') == 'False'){
			$Module = OrderShippingModules::getModule($this->shipModuleCode);
			$dontShow = '';
			$selectedMethod = '';

			$weight = 0;
			if ($Module && $Module->getType() == 'Order' && $App->getEnv() == 'catalog'){
				if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_SHOW_SHIPPING_ON_CALENDAR_IF_ORDER') == 'False'){
					$dontShow = 'none';
				}
				foreach($ShoppingCart->getProducts() as $cartProduct){
					if ($cartProduct->hasInfo('ReservationInfo') === true){
						$reservationInfo1 = $cartProduct->getInfo('ReservationInfo');
						if (isset($reservationInfo1['shipping']) && isset($reservationInfo1['shipping']['module']) && $reservationInfo1['shipping']['module'] == 'zonereservation'){
							$selectedMethod = $reservationInfo1['shipping']['id'];
							$dontShow = '';
							break;
						}
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
			if ($quotes && sizeof($quotes[0]['methods']) > 0 && ($Module->getType() == 'Product' || sysConfig::get('EXTENSION_PAY_PER_RENTALS_SHOW_SHIPPING_ON_CALENDAR_IF_ORDER') == 'True')){
				$table .= $this->parseQuotes($quotes);
			}
			$table .= '</div>';
		}
		elseif (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_UPS_RESERVATION') == 'True' && sysConfig::get('EXTENSION_PAY_PER_RENTALS_CHECK_GOOGLE_ZONES_BEFORE') == 'False') {
			$table = '<div class="shippingUPS"><table cellpadding="0" cellspacing="0" border="0">';

			$table .= '<tr id="shipMethods">' .
				'<td class="main">' . '</td>' .
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
				$shippingAddress = $Editor->AddressManager
					->getAddress('delivery')
					->toArray();
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

			$getQuotes
				->append($checkAddressBox)
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

	public function parseQuotes($quotes)
	{
		global $currencies, $userAccount, $App;
		$table = '';
		if ($this->getShipping() !== false){
			$table = '<table cellpadding="0" cellspacing="0" border="0" align="center">';

			$newMethods = array();

			foreach($quotes[0]['methods'] as $mInfo){
				if (!in_array($mInfo['id'], $this->getShippingArray()) && sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_ZIPCODES_SHIPPING') == 'False'){
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
					'<td class="main" colspan="3"><b>' . sysLanguage::get('PPR_SHIPPING_SELECT') . '</b>&nbsp;' . '</td>' .
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
									cache    : false,
									dataType : 'json',
									url      : js_app_link('appExt=payPerRentals&app=build_reservation&appPage=default&rType=ajax&action=checkAddress'),
									data     : $('*', $('#googleAddress')).serialize(),
									type     : 'post',
									success  : function (data) {
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
									cache    : true,
									url      : js_app_link('appExt=payPerRentals&app=build_reservation&appPage=default&rType=ajax&action=getCountryZones'),
									data     : 'cID=' + $(this).val() + '&zName=' + $('#stateColCheck input').val(),
									dataType : 'html',
									success  : function (data) {
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

				$changeAddress
					->append($checkAddressBox)
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

	public function getHiddenFields()
	{
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

	public function overBookingAllowed()
	{
		return ($this->getOverbooking() == '1');
	}

	public function getProductsBarcodes()
	{
		return $this->_invItems;
	}

	public function getBookedDaysArray(DateTime $StartDate, $qty, &$reservationsArr, &$bookedDates, $usableBarcodes = array())
	{
		$reservationsArr = ReservationUtilities::getMyReservations(
			$this->getProductId(),
			$StartDate,
			$this->overBookingAllowed(),
			$usableBarcodes
		);

		if (empty($reservationsArr) === true){
			return array();
		}

		$bookedDates = array();
		foreach($reservationsArr as $rInfo){
			if (isset($rInfo['start']) && isset($rInfo['end'])){
				$startTime = $rInfo['start']->getTimestamp();
				$endTime = $rInfo['end']->getTimestamp();
				while($startTime <= $endTime){
					$dateFormated = date(sysLanguage::getDateFormat('short'), $startTime);
					if ($this->getTrackMethod() == 'barcode'){
						foreach($rInfo['barcode'] as $barcodeId){
							$bookedDates[$dateFormated]['barcode'][] = $barcodeId;
						}
					}
					else {
						if (isset($bookedDates[$dateFormated]['qty'])){
							$bookedDates[$dateFormated]['qty'] = $bookedDates[$dateFormated]['qty'] + 1;
						}
						else {
							$bookedDates[$dateFormated]['qty'] = 1;
						}
					}

					$startTime += 60 * 60 * 24;
				}
			}
		}

		$bookingsArr = array();
		$prodBarcodes = array();
		foreach($this->getProductsBarcodes() as $iBarcode){
			if (sizeof($usableBarcodes) == 0 || in_array($iBarcode['id'], $usableBarcodes)){
				$prodBarcodes[] = $iBarcode['id'];
			}
		}
		//print_r($prodBarcodes);
		//echo '------------'.$qty;
		//print_r($bookedDates);

		if (sizeof($prodBarcodes) < $qty && sizeof($reservationsArr) > 0){
			return false;
		}
		else {
			$TotalBarcodes = sizeof($prodBarcodes);
			foreach($bookedDates as $dateFormated => $iBook){
				if ($this->getTrackMethod() == 'barcode'){
					//$myqty = 0;
					//foreach($iBook['barcode'] as $barcode){
					//if (in_array($barcode, $prodBarcodes)){
					//$myqty++;
					//}
					//}
					if (($TotalBarcodes - sizeof($iBook['barcode'])) < $qty){
						$bookingsArr[] = $dateFormated;
					}
				}
				else {
					if ($prodBarcodes['available'] - $iBook['qty'] < $qty){
						$bookingsArr[] = $dateFormated;
					}
				}
			}
		}
		return $bookingsArr;
	}

	public function getBookedTimeDaysArray(DateTime $StartDate, $qty, $minTime, &$reservationsArr, &$bookedDates)
	{
		/*$reservationsArr = ReservationUtilities::getMyReservations(
			$this->getProductId(),
			$StartDate,
			$this->overBookingAllowed()
		);*/
		$bookedTimes = array();
		//print_r($bookedDates);
		//print_r($reservationsArr);

		foreach($reservationsArr as $iReservation){
			if (isset($iReservation['start_time']) && isset($iReservation['end_time'])){
				$startTime = strtotime($iReservation['start_date'] . ' ' . $iReservation['start_time']);
				$endTime = strtotime($iReservation['start_date'] . ' ' . $iReservation['end_time']);
				while($startTime <= $endTime){
					$dateFormated = date('Y-n-j H:i', $startTime);
					if ($this->getTrackMethod() == 'barcode'){
						$bookedTimes[$dateFormated]['barcode'][] = $iReservation['barcode'];
						if (isset($bookedDates[$iReservation['start_date']]['barcode'])){
							foreach($bookedDates[$iReservation['start_date']]['barcode'] as $iBarc){
								$bookedTimes[$dateFormated]['barcode'][] = $iBarc;
							}
						}
						//check if all the barcodes are already or make a new function to make checks by qty... (this function can return also the free barcode?)
					}
					else {
						if (isset($bookedTimes[$dateFormated]['qty'])){
							$bookedTimes[$dateFormated]['qty'] = $bookedTimes[$dateFormated]['qty'] + 1;
						}
						else {
							$bookedTimes[$dateFormated]['qty'] = 1;
						}
						if (isset($bookedDates[$iReservation['start_date']]['qty'])){
							$bookedTimes[$dateFormated]['qty'] = $bookedTimes[$dateFormated]['qty'] + count($bookedDates[$iReservation['start_date']]['qty']);
						}
						//check if there is still qty available.
					}

					$startTime += $minTime * 60;
				}
			}
		}
		$bookingsArr = array();
		$prodBarcodes = array();
		foreach($this->getProductsBarcodes() as $iBarcode){
			$prodBarcodes[] = $iBarcode['id'];
		}

		foreach($bookedTimes as $dateFormated => $iBook){
			if ($this->getTrackMethod() == 'barcode'){
				$myqty = 0;
				foreach($iBook['barcode'] as $barcode){
					if (in_array($barcode, $prodBarcodes)){
						$myqty++;
					}
				}
				if (count($prodBarcodes) - $myqty < $qty){
					$bookingsArr[] = $dateFormated;
				}
			}
			else {
				if ($prodBarcodes['available'] - $iBook['qty'] < $qty){
					$bookingsArr[] = $dateFormated;
				}
			}
		}

		return $bookingsArr;
	}

	public function getReservations($start, $end)
	{
		$booked = ReservationUtilities::getReservations(
			$this->getProductId(),
			$start,
			$end,
			$this->overBookingAllowed()
		);

		return $booked;
	}

	public function dateIsBooked($date, $bookedDays, $invItems, $qty = 1)
	{
		if ($invItems === false){
			return true;
		}
		$totalAvail = 0;
		foreach($invItems as $item){
			if ($this->getTrackMethod() == 'barcode'){
				if (!isset($bookedDays['barcode'][$date]) || !in_array($item['id'], $bookedDays['barcode'][$date])){
					$totalAvail++;
				}
			}
			elseif ($this->getTrackMethod() == 'quantity') {
				$realAvail = ($item['available'] + $item['reserved']) /* - $Qcheck[0]['total']*/
				;
				if (!isset($bookedDays['quantity'][$date]) || !isset($bookedDays['quantity'][$date][$item['id']])){
					$totalAvail += $realAvail;
				}
				elseif ($realAvail > $qty) {
					$totalAvail += $realAvail;
				}
			}

			if ($totalAvail >= $qty){
				break;
			}
		}
		if ($totalAvail >= $qty){
			return false;
		}
		else {
			if ($this->overBookingAllowed() === true){
				return false;
			}
			else {
				return true;
			}
		}
	}

	public function findBestPrice($dateArray)
	{
		global $currencies, $appExtension, $Editor;
		$dateArray['start_date'] = $dateArray['start'];
		$dateArray['end_date'] = $dateArray['end'];
		$this->addDays($dateArray['start'], $dateArray['end']);
		$price = 0;
		if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_FULL_DAYS') == 'True'){
			if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_MORE_HOURS_ONE_DAY') == 'True'){
				$StartHour = $dateArray['end_date']->format('h');
				$StartMinute = $dateArray['start_date']->format('i');
				$EndHour = $dateArray['end_date']->format('h');
				$EndMinute = $dateArray['end_date']->format('i');
				if (
					(isset($_POST['start_time']) && isset($_POST['end_time']) && $_POST['end_time'] > $_POST['start_time']) ||
					($EndHour > $StartHour || ($EndHour == $StartHour && $EndMinute > $StartMinute))
				){
					$dateArray['end_date'] = $dateArray['end']->modify('+1 Day');
				}
			}
			$dateArray['start_date'] = $dateArray['start']->setTime(0, 0, 0);
			$dateArray['end_date'] = $dateArray['end']->setTime(0, 0, 0);
		}

		$PricingInfo = PurchaseType_reservation_utilities::getPricingPeriodInfo(
			$this->getPayPerRentalId(),
			$dateArray['start_date'],
			$dateArray['end_date']
		);

		$Prices = array();
		foreach($PricingInfo as $PriceInfo){
			$TypeId = $PriceInfo['Type']['pay_per_rental_types_id'];

			$Prices[] = $PriceInfo;

			if ($this->hasDiscounts()){
				$Discounted = array();
				foreach($Prices as $Price){
					$Discounted[] = PurchaseType_reservation_utilities::discountPrice(
						$Price['price'],
						$PriceInfo,
						$this->getDiscounts(),
						$dateArray
					);
				}

				foreach($Discounted as $Price){
					$Prices[] = array_merge($PriceInfo, array(
						'price' => $Price
					));
				}
			}
		}

		usort($Prices, function ($a, $b)
		{
			return ($a['price'] < $b['price'] ? 1 : -1);
		});
		$Lowest = PurchaseType_reservation_utilities::getLowestPrice(
			$Prices,
			$dateArray['start_date'],
			$dateArray['end_date']
		);
		$Price = $Lowest['price'];

		$return['price'] = round($Price, 2);
		$return['totalPrice'] = round($Price, 2);
		if (sysconfig::get('EXTENSION_PAY_PER_RENTALS_SHORT_PRICE') == 'False'){
			$NumberOfMinutes = ((($dateArray['end_date']->diff($dateArray['start_date'])->days + 1) * SesDateTime::TIME_DAY) / SesDateTime::TIME_MINUTE);
			$return['message'] = sysLanguage::get('PPR_PRICE_BASED_ON') .
				($NumberOfMinutes / $Lowest['Type']['minutes']) .
				'X' .
				$Lowest['number_of'] . ' ' . $Lowest['Type']['pay_per_rental_types_name'] .
				'@' .
				$Lowest['price'] .
				'/' .
				$Lowest['Type']['pay_per_rental_types_name'];
		}
		else {
			$return['message'] = '';
		}
		return $return;
	}

	public function addDays(DateTime &$sdate, DateTime &$edate)
	{
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
					$edate = $edate->modify('+1 Day');
					//$edate = date('Y-m-d H:i:s', strtotime('+1 days', strtotime($edate)));
					break;
				case 'None':
					$sdate = $sdate->modify('+1 Day');
					//$sdate = date('Y-m-d H:i:s', strtotime('+1 days', strtotime($sdate)));
					//$edate = date('Y-m-d H:i:s', strtotime('-1 days', strtotime($edate)));
					break;
			}
		}
		if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_CALCULATE_DISABLED_DAYS') == 'False'){
			$startTime = $sdate->getTimestamp();
			$endTime = $edate->getTimestamp();
			$disabledDays = array_filter(sysConfig::explode('EXTENSION_PAY_PER_RENTALS_DISABLED_DAYS', ','));
			while($startTime <= $endTime){
				$dayOfWeek = date('D', $startTime);
				if (in_array($dayOfWeek, $disabledDays)){
					$sdate = date('Y-m-d H:i:s', strtotime('+1 days', strtotime($sdate)));
				}
				$startTime += 60 * 60 * 24;
			}
		}
	}

	public function getReservationPrice(DateTime $start, DateTime $end, &$rInfo = '', $semName = '', $includeInsurance = false, $onlyShow = true)
	{
		global $currencies, $ShoppingCart, $App;
		$productPricing = array();

		$dateArray = array(
			'start' => $start,
			'end'   => $end
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
			$returnPrice['totalPrice'] = $this->getPriceSemester($semName);
			$returnPrice['message'] = sysLanguage::get('PPR_PRICE_BASED_ON_SEMESTER') . $semName . ' ';
		}

		if ($rInfo != '' && isset($rInfo['shipping']) && isset($rInfo['shipping']['cost'])){
			$productPricing['shipping'] = $rInfo['shipping']['cost'];
		}
		elseif (isset($_POST['rental_shipping']) && $_POST['rental_shipping'] != '' && $_POST['rental_shipping'] != 'undefined') {
			$shippingMethod = explode('_', $_POST['rental_shipping']);
			$Module = OrderShippingModules::getModule($shippingMethod[0]);
			$totalPrice = 0;
			$weight = 0;
			if ($Module->getType() == 'Order' && $App->getEnv() == 'catalog'){

				foreach($ShoppingCart->getProducts() as $cartProduct){
					if ($cartProduct->hasInfo('ReservationInfo') === true){
						$reservationInfo1 = $cartProduct->getInfo('ReservationInfo');
						if (isset($reservationInfo1['shipping']) && isset($reservationInfo1['shipping']['module']) && $reservationInfo1['shipping']['module'] == 'zonereservation'){
							$cost = 0;
							if (isset($reservationInfo1['shipping']['cost'])){
								$cost = $reservationInfo1['shipping']['cost'];
							}
							$totalPrice += $cartProduct->getFinalPrice(true) * $cartProduct->getQuantity() - $cost * $cartProduct->getQuantity();
							break;
						}
						$weight += $cartProduct->getWeight();
					}
				}
			}

			$product = new product($this->getProductId());
			if (isset($_POST['rental_qty'])){
				$total_weight = (int)$_POST['rental_qty'] * $product->getWeight();
			}
			else {
				$total_weight = $product->getWeight();
			}

			if (is_array($returnPrice)){
				$totalPrice += $returnPrice['price'];
			}

			$quote = $Module->quote($shippingMethod[1], $total_weight + $weight, $totalPrice);

			if ($quote['methods'][0]['cost'] >= 0){
				$productPricing['shipping'] = (float)$quote['methods'][0]['cost'];
			}
		}

		if (is_array($returnPrice)){

			if (isset($productPricing['shipping']) && sysConfig::get('EXTENSION_PAY_PER_RENTALS_SHOW_SHIPPING') == 'True'){
				if ($onlyShow){
					$returnPrice['price'] += $productPricing['shipping'];
				}
				$returnPrice['totalPrice'] += $productPricing['shipping'];
				$returnPrice['message'] .= ' + ' . $currencies->format($productPricing['shipping']) . ' ' . sysLanguage::get('EXTENSION_PAY_PER_RENTALS_CALENDAR_SHIPPING');
			}
			if ($this->getDepositAmount() > 0){
				if ($onlyShow){
					$returnPrice['price'] += $this->getDepositAmount();
				}
				$returnPrice['totalPrice'] += $this->getDepositAmount();
				$returnPrice['message'] .= ' + ' . $currencies->format($this->getDepositAmount()) . ' ' . sysLanguage::get('EXTENSION_PAY_PER_RENTALS_CALENDAR_DEPOSIT');
			}

			if (isset($rInfo['insurance'])){
				if ($onlyShow){
					$returnPrice['price'] += (float)$rInfo['insurance'];
				}
				$returnPrice['totalPrice'] += (float)$rInfo['insurance'];
			}
			elseif ($includeInsurance) {
				$payPerRentals = Doctrine_Query::create()
					->select('insurance_cost')
					->from('ProductsPayPerRental')
					->where('products_id = ?', $this->getProductId())
					->fetchOne();
				$rInfo['insurance'] = $payPerRentals->insurance_cost;
				$returnPrice['price'] += (float)$rInfo['insurance'];
				$returnPrice['totalPrice'] += (float)$rInfo['insurance'];
				$returnPrice['message'] .= ' + ' . $currencies->format($rInfo['insurance']) . ' ' . sysLanguage::get('EXTENSION_PAY_PER_RENTALS_CALENDAR_INSURANCE');
			}

			EventManager::notify('PurchaseTypeAfterSetup', &$returnPrice);
		}
		return $returnPrice;
	}

	public function figureProductPricing(&$pID_string, $externalResInfo = false)
	{
		global $ShoppingCart;

		if ($externalResInfo === true){
			$rInfo = $ShoppingCart->reservationInfo;
		}
		elseif (is_array($pID_string)) {
			$rInfo =& $pID_string;
		}

		$pricing = $this->getReservationPrice($rInfo['start_date'], $rInfo['end_date'], &$rInfo, (isset($_POST['semester_name']) ? $_POST['semester_name'] : ''), (isset($_POST['hasInsurance']) ? true : false));

		return $pricing;
	}

	public function formatDateArr($format, $date)
	{
		return date($format, mktime($date['hour'], $date['minute'], $date['second'], $date['month'], $date['day'], $date['year']));
	}

	public function showProductListing($col, $options = array())
	{
		global $currencies, $appExtension;
		$return = false;
		if ($col == 'productsPriceReservation'){
			$options = array_merge(array(
				'showBuyButton' => true
			), $options);
			$tableRow = array();
			if ($appExtension->isEnabled('payPerRentals') === true){

				if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_DATE_SELECTION') == 'Using calendar after browsing products and clicking Reserve'){
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
					}
					else {
						$payPerRentalButton = $payPerRentalButton->draw();
					}
					$i = 1;
					foreach(PurchaseType_reservation_utilities::getRentalPricing($this->getPayPerRentalId()) as $iPrices){
						$tableRow[$i] = '<tr>
                                    <td class="main">' . $iPrices['Description'][0]['price_per_rental_per_products_name'] . ':</td>
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
						$price = $this->getReservationPrice($start_date, $end_date, $rInfo, '', (sysConfig::get('EXTENSION_PAY_PER_RENTALS_INSURE_ALL_PRODUCTS_AUTO') == 'True'));
						$thePrice += $price['price'];
						if (Session::exists('isppr_event_multiple_dates')){
							$thePrice = 0;
							$datesArr = Session::get('isppr_event_multiple_dates');

							foreach($datesArr as $iDate){
								$price = $this->getReservationPrice($iDate, $iDate, $rInfo, '', (sysConfig::get('EXTENSION_PAY_PER_RENTALS_INSURE_ALL_PRODUCTS_AUTO') == 'True'));
								$thePrice += $price['price'];
							}
						}
						$i2 = 1;
						if (Session::exists('noInvDates')){
							$myNoInvDates = Session::get('noInvDates');
							if (isset($myNoInvDates[$this->getData('products_id')]) && is_array($myNoInvDates[$this->getData('products_id')]) && count($myNoInvDates[$this->getData('products_id')]) > 0){
								$tableRow[$i2] = '<tr>
										<td class="main" colspan="2">' . '<b>Item not available:</b>' . '</td>
									  </tr>';
								$i2++;
								foreach($myNoInvDates[$this->getData('products_id')] as $iDate){
									$tableRow[$i2] = '<tr>
										<td class="main" colspan="2" style="color:red">' . strftime(sysLanguage::getDateFormat('long'), $iDate) . '</td>
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
						if (isset($days_before)){
							$htmlDaysBefore = htmlBase::newElement('input')
								->setType('hidden')
								->setName('days_before')
								->setValue($days_before);
						}

						if (isset($days_after)){
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
						if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_SHOW_QTY_LISTING') == 'False'){
							$htmlRentalQty->setType('hidden');
						}
						else {
							$htmlRentalQty->attr('size', '3');
						}
						$htmlRentalQty
							->setName('rental_qty')
							->setValue($qtyVal);

						if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_INSURE_ALL_PRODUCTS_AUTO') == 'True'){
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
						if (isset($htmlDaysBefore)){
							$pageForm->append($htmlDaysBefore);
						}

						if (isset($htmlDaysAfter)){
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
							$pageForm
								->append($htmlEventDate)
								->append($htmlEventName);
							if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_GATES') == 'True'){
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
								if (Session::exists('isppr_shipping_cost')){
									$ship_cost = (float)Session::get('isppr_shipping_cost');
								}
							}
							else {
								$htmlShippingDays->setValue("upsreservation_" . Session::get('isppr_shipping_method'));
								if (isset($_POST['rental_shipping'])){
									$isR = true;
									$isRV = $_POST['rental_shipping'];
								}
								$_POST['rental_shipping'] = 'upsreservation_' . Session::get('isppr_shipping_method');
							}
							$pageForm->append($htmlShippingDays);
						}

						if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_SHOW_RATES_PPR_BEFORE') == 'True'){
							foreach($this->getRentalPricing() as $iPrices){
								$tableRow[$i2] = '<tr>
									<td class="main" colspan="2">' . $iPrices['PricePayPerRentalPerProductsDescription'][0]['price_per_rental_per_products_name'] . ': ' . $this->displayReservePrice($iPrices['price']) . '</td>
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
						   <td class="main" colspan="2" style="font-size:.8em;" align="center">' . $extraContent . $pageForm->draw() . '</td>
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
							if (Session::exists('isppr_shipping_method')){
								if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_UPS_RESERVATION') == 'False'){
									if (Session::exists('isppr_shipping_cost')){
										$ship_cost = (float)Session::get('isppr_shipping_cost');
									}
								}
								else {
									if (isset($_POST['rental_shipping'])){
										$isR = true;
										$isRV = $_POST['rental_shipping'];
									}
									$_POST['rental_shipping'] = 'upsreservation_' . Session::get('isppr_shipping_method');
								}
							}

							$thePrice = 0;
							$price = $this->getReservationPrice($start_date, $end_date);
							$thePrice += $price['price'];
							if (Session::exists('isppr_event_multiple_dates')){
								$thePrice = 0;
								$datesArr = Session::get('isppr_event_multiple_dates');

								foreach($datesArr as $iDate){
									$price = $this->getReservationPrice($iDate, $iDate);
									$thePrice += $price['price'];
								}
							}

							$pricing = $currencies->format($qtyVal * $thePrice - $qtyVal * $depositAmount + $ship_cost);
							if (!$isR){
								unset($_POST['rental_shipping']);
							}
							else {
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
					  	 <td class="main" colspan="2" style="font-size:.8em;" align="center">' . $extraContent . $payPerRentalButton->draw() . '</td>
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

						if (Session::exists('isppr_selected') == false || Session::get('isppr_selected') == false){
							$payPerRentalButton
								->setName('no_dates_selected');
						}
						else {
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

	public function getOrderedProductBarcode(array $pInfo)
	{
		return $pInfo['OrdersProductsReservation'][0]['ProductsInventoryBarcodes']['barcode'];
		$barcode = array();
		foreach($pInfo['OrdersProductsReservation'] as $res){
			$barcode[] = $res['ProductsInventoryBarcodes']['barcode'];
		}
		return $barcode;
	}

	public function displayOrderedProductBarcode(array $pInfo)
	{
		$barcode = '';
		foreach($pInfo['OrdersProductsReservation'] as $res){
			$barcode .= $res['ProductsInventoryBarcodes']['barcode'] . '<br/>';
		}
		return $barcode;
	}

	/*
	 * @TODO: Figure out something better
	 */
	public function getPrice($priceTime)
	{
		global $currencies;

		$pprId = $this->getPayPerRentalId();
		foreach(PurchaseType_reservation_utilities::getRentalPricing($pprId) as $priceInfo){
			$price = $priceInfo['price'];
			//$partName = $priceInfo['Description'][0]['price_per_rental_per_products_name'];
			$partName = $priceInfo['Type']['minutes'];
			if (!isset($prices[$partName])){
				$prices[$partName] = 0;
			}
			$prices[$partName] += $price;
		}

		return (isset($prices[$priceTime]) ? $prices[$priceTime] : 0);
	}

	public function getExportTableColumns()
	{
		return array(
			'status',
			'inventory_controller',
			'inventory_track_method',
			'tax_class_id'
		);
	}

	public function processProductImport(&$Product, $CurrentRow)
	{
		parent::processProductImport($Product, $CurrentRow);
		$colBasename = 'v_' . $Product->products_type . '_' . $this->getCode();

		$PayPerRental =& $Product->ProductsPayPerRental;
		$PayPerRental->overbooking = ($CurrentRow->getColumnValue($colBasename . '_shipping', 'No') == 'No' ? 0 : 1);

		/*$Product->products_auth_method = (
		isset($item['v_' . $colNameAdd . '_auth_method'])
			? $item['v_' . $colNameAdd . '_auth_method']
			: 'auth'
		);

		$Product->products_auth_charge = (
		isset($item['v_' . $colNameAdd . '_auth_charge'])
			? $item['v_' . $colNameAdd . '_auth_charge']
			: '0.0000'
		);*/

		$PayPerRental->deposit_amount = (float)$CurrentRow->getColumnValue($colBasename . '_deposit_amount', 0);
		$PayPerRental->insurance = (float)$CurrentRow->getColumnValue($colBasename . '_insurance', 0);
		$PayPerRental->shipping = $CurrentRow->getColumnValue($colBasename . '_shipping');
		//$PayPerRental->save();

		$QPayPerRentalTypes = Doctrine_Query::create()
			->from('PayPerRentalTypes')
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
		$htypes = array();
		foreach($QPayPerRentalTypes as $iType){
			$htypes[$iType['pay_per_rental_types_id']] = $iType['pay_per_rental_types_name'];
		}
		unset($QPayPerRentalTypes);

		/*import hidden dates*/
		$HiddenDates = $Product->PayPerRentalHiddenDates;
		$HiddenDates->delete();
		$j = 0;
		while(true){
			$HiddenStartDate = $CurrentRow->getColumnValue($colBasename . '_hidden_start_date_' . $j);
			if ($HiddenStartDate !== false){
				if ($HiddenStartDate !== null){
					$HiddenEndDate = $CurrentRow->getColumnValue($colBasename . '_hidden_end_date_' . $j);
					$PayPerRentalHiddenDates = new PayPerRentalHiddenDates();
					$PayPerRentalHiddenDates->hidden_start_date = date('Y-m-d', strtotime($HiddenStartDate));
					$PayPerRentalHiddenDates->hidden_end_date = date('Y-m-d', strtotime($HiddenEndDate));

					$HiddenDates->add($PayPerRentalHiddenDates);
					unset($HiddenEndDate);
					unset($HiddenStartDate);
				}
			}
			else {
				unset($HiddenStartDate);
				break;
			}
			$j++;
		}
		/*end import hidden dates*/
		$i = 0;
		$Periods = Doctrine_Core::getTable('PayPerRentalPeriods');
		$PeriodPrices = $Product->ProductsPayPerPeriods;
		while(true){
			$PayPerRentalPeriod = $CurrentRow->getColumnValue($colBasename . '_period_' . $i);
			if ($PayPerRentalPeriod !== false){
				if ($PayPerRentalPeriod !== null){
					$Period = $Periods->findOneByPeriodName($PayPerRentalPeriod);
					if (!$Period){
						$Period = $Periods->getRecord();
						$Period->period_name = $PayPerRentalPeriod;
						$Period->save();
					}

					$PeriodPrices[$Period->period_id]->period_id = $Period->period_id;
					$PeriodPrices[$Period->period_id]->price = $CurrentRow->getColumnValue($colBasename . '_period_price_' . $i, 0);
					$Period->free(true);
				}
			}
			else {
				break;
			}
			$i++;
		}

		$j = 0;
		$PayPerRental->PricePerRentalPerProducts->delete();
		while(true){
			$PayPerRentalTimePeriodNumOf = $CurrentRow->getColumnValue($colBasename . '_time_period_number_of_' . $j);
			if ($PayPerRentalTimePeriodNumOf !== false){
				if ($PayPerRentalTimePeriodNumOf !== null){
					$PricePerProduct = new PricePerRentalPerProducts();
					$Description = $PricePerProduct->PricePayPerRentalPerProductsDescription;

					foreach(sysLanguage::getLanguages() as $lInfo){
						$langId = $lInfo['id'];
						$Description[$langId]->language_id = $langId;
						$Description[$langId]->price_per_rental_per_products_name = $CurrentRow->getColumnValue($colBasename . '_time_period_desc_' . $langId . '_' . $j);
					}

					$type = '';
					$PayPerRentalTimePeriodTypeName = $CurrentRow->getColumnValue($colBasename . '_time_period_type_name_' . $j);
					foreach($htypes as $itypeID => $itypeName){
						if ($itypeName == $PayPerRentalTimePeriodTypeName){
							$type = $itypeID;
							break;
						}
					}

					$PricePerProduct->price = $CurrentRow->getColumnValue($colBasename . '_time_period_price_' . $j);
					$PricePerProduct->number_of = $CurrentRow->getColumnValue($colBasename . '_time_period_number_of_' . $j);
					$PricePerProduct->pay_per_rental_types_id = $type;

					$PayPerRental->PricePerRentalPerProducts->add($PricePerProduct);
				}
			}
			else {
				break;
			}
			$j++;
		}
	}

	public function addExportQueryConditions($ProductType, &$QfileLayout)
	{
		parent::addExportQueryConditions($ProductType, $QfileLayout);

		$colNameAdd = $ProductType . '_' . $this->getCode();
		$QfileLayout
			->leftJoin('p.ProductsPayPerRental ppr')
			->addSelect('ppr.shipping as v_' . $colNameAdd . '_shipping')
			->addSelect('ppr.max_days as v_' . $colNameAdd . '_max_days')
			->addSelect('ppr.max_months as v_' . $colNameAdd . '_max_months')
			->addSelect('ppr.overbooking as v_' . $colNameAdd . '_overbooking')
			->addSelect('ppr.insurance as v_' . $colNameAdd . '_insurance')
			->addSelect('ppr.deposit_amount as v_' . $colNameAdd . '_deposit_amount');
	}

	public function addExportHeaderColumns($ProductType, &$HeaderRow)
	{
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
			if ($iMax['hiddenmax'] > $maxVal){
				$maxVal = $iMax['hiddenmax'];
			}
		}

		for($j = 0; $j < $maxVal; $j++){
			$HeaderRow->addColumn('v_' . $colNameAdd . '_hidden_start_date_' . $j);
			$HeaderRow->addColumn('v_' . $colNameAdd . '_hidden_end_date_' . $j);
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
			if ($iMax['pprmax'] > $maxVal){
				$maxVal = $iMax['pprmax'];
			}
		}

		for($j = 0; $j < $maxVal; $j++){
			$HeaderRow->addColumn('v_' . $colNameAdd . '_time_period_number_of_' . $j);
			$HeaderRow->addColumn('v_' . $colNameAdd . '_time_period_type_name_' . $j);
			$HeaderRow->addColumn('v_' . $colNameAdd . '_time_period_price_' . $j);
			foreach(sysLanguage::getLanguages() as $lInfo){
				$HeaderRow->addColumn('v_' . $colNameAdd . '_time_period_desc_' . $lInfo['id'] . '_' . $j);
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
			$HeaderRow->addColumn('v_' . $colNameAdd . '_period_' . $i);
			$HeaderRow->addColumn('v_' . $colNameAdd . '_period_price_' . $i);
			$i++;
		}
	}

	public function addExportRowColumns($ProductType, &$CurrentRow, $Product)
	{
		parent::addExportRowColumns($ProductType, $CurrentRow, $Product);

		$PayPerRental = $Product->ProductsPayPerRental;

		$colNameAdd = $ProductType . '_' . $this->getCode();
		if ($PayPerRental->overbooking == '0'){
			$CurrentRow->addColumn('No', 'v_' . $colNameAdd . '_overbooking');
		}
		else {
			$CurrentRow->addColumn('Yes', 'v_' . $colNameAdd . '_overbooking');
		}

		/*export hidden dates*/
		$HiddenDates = $Product->PayPerRentalHiddenDates;
		if ($HiddenDates && $HiddenDates->count() > 0){
			$j = 0;
			foreach($HiddenDates as $Date){
				$CurrentRow->addColumn($Date->hidden_start_date, 'v_' . $colNameAdd . '_hidden_start_date_' . $j);
				$CurrentRow->addColumn($Date->hidden_end_date, 'v_' . $colNameAdd . '_hidden_end_date_' . $j);
				$j++;
			}
		}
		/*end export hidden dates*/

		$Prices = $PayPerRental->PricePerRentalPerProducts;
		if ($Prices && $Prices->count() > 0){
			$j = 0;
			foreach($Prices as $Price){
				$CurrentRow->addColumn($Price->number_of, 'v_' . $colNameAdd . '_time_period_number_of_' . $j);
				$CurrentRow->addColumn($Price->Type->pay_per_rental_types_name, 'v_' . $colNameAdd . '_time_period_type_name_' . $j);
				$CurrentRow->addColumn($Price->price, 'v_' . $colNameAdd . '_time_period_price_' . $j);

				foreach(sysLanguage::getLanguages() as $lInfo){
					foreach($Price->Description as $PriceDescription){
						if ($lInfo['id'] == $PriceDescription->language_id){
							$CurrentRow->addColumn(
								$PriceDescription->price_per_rental_per_products_name,
								'v_' . $colNameAdd . '_time_period_desc_' . $lInfo['id'] . '_' . $j
							);
						}
					}
				}
				$j++;
			}
		}

		$i = 0;
		$Periods = $Product->ProductsPayPerPeriods;
		foreach($Periods as $Period){
			$CurrentRow->addColumn(
				$Period->PayPerRentalPeriods->period_name,
				'v_' . $colNameAdd . '_period_' . $i
			);
			$CurrentRow->addColumn(
				$Period->price,
				'v_' . $colNameAdd . '_period_price_' . $i
			);
			$i++;
		}
	}

	public function productImportAppendLog(&$Product, &$productLogArr)
	{
		$productLogArr = array_merge($productLogArr, array(
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