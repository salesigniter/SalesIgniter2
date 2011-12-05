<?php
class PurchaseType_reservation_utilities
{

	private static $EventsCache = array();

	private static $GatesCache = array();

	private static $RentalTypesCache = array();

	private static $RentalPricingCache = array();

	public static function getEvents($eventName = false) {
		if (!isset(self::$EventsCache[$eventName])){
			$Qevents = Doctrine_Query::create()
				->from('PayPerRentalEvents')
				->orderBy('events_date');

			if ($eventName !== false){
				$Qevents->where('event_name = ?', $eventName);
				$Result = $Qevents->fetchOne();
			}
			else {
				$Result = $Qevents->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
			}
			self::$EventsCache[$eventName] = ($Result && sizeof($Result) > 0 ? $Result : false);
		}

		return self::$EventsCache[$eventName];
	}

	public static function getGates($gateName = false) {
		if (!isset(self::$GatesCache[$eventName])){
			$Qgates = Doctrine_Query::create()
				->from('PayPerRentalGates');

			if ($gateName !== false){
				$Qgates->where('gate_name = ?', $gateName);
				$Result = $Qgates->fetchOne();
			}
			else {
				$Result = $Qgates->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
			}
			self::$GatesCache[$gateName] = ($Result && sizeof($Result) > 0 ? $Result : false);
		}

		return self::$GatesCache[$gateName];
	}

	public static function getRentalTypes() {
		if (empty(self::$RentalTypesCache)){
			$Query = Doctrine_Query::create()
				->from('PayPerRentalTypes')
				->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

			self::$RentalTypesCache = ($Query && sizeof($Query) > 0 ? $Query : false);
		}
		return self::$RentalTypesCache;
	}

	public static function getRentalPricing($pprId) {
		if (!isset(self::$RentalPricingCache[$pprId])){
			$QPricePerRentalProducts = Doctrine_Query::create()
				->from('PricePerRentalPerProducts pprp')
				->leftJoin('pprp.PricePayPerRentalPerProductsDescription pprpd')
				->where('pprp.pay_per_rental_id =?', $pprId)
				->andWhere('pprpd.language_id=?', Session::get('languages_id'))
				->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

			self::$RentalPricingCache[$pprId] = $QPricePerRentalProducts;
		}
		return self::$RentalPricingCache[$pprId];
	}

	public static function parse_reservation_info($pID_string, $resInfo, $showEdit = true) {
		global $currencies;
		$return = '';
		$return .= '<br /><small><b><i><u>' . sysLanguage::get('TEXT_INFO_RESERVATION_INFO') . '</u></i></b></small>';

		$startTime = $resInfo['start_date']->getTimestamp();
		$endTime = $resInfo['end_date']->getTimestamp();

		//$return .= '<br /><small><i> - ' . sysLanguage::get('TEXT_INFO_START_DATE') . ' ' . strftime(sysLanguage::getDateFormat('long'), $startTime) . '</i></small>' .
		//	'<br /><small><i> - ' . sysLanguage::get('TEXT_INFO_END_DATE') . ' ' . strftime(sysLanguage::getDateFormat('long'), $endTime) . '</i></small>';
		if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_EVENTS') == 'False'){
			if ($resInfo['semester_name'] == ''){
				if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_ALLOW_HOURLY') == 'True'){
					$DateFormat = 'getDateTimeFormat';
				}
				else {
					$DateFormat = 'getDateFormat';
				}

				$return .= '<br /><small><i> - ' . sysLanguage::get('TEXT_INFO_START_DATE') . ' ' . $resInfo['start_date']->format(sysLanguage::$DateFormat('long')) . '</i></small>' .
					'<br /><small><i> - ' . sysLanguage::get('TEXT_INFO_END_DATE') . ' ' . $resInfo['end_date']->format(sysLanguage::$DateFormat('long')) . '</i></small>';
			}
			else {
				$return .= '<br /><small><i> - ' . sysLanguage::get('TEXT_INFO_SEMESTER') . ' ' . $resInfo['semester_name'] . '</i></small>';
			}
		}
		else {
			$return .= '<br /><small><i> - Event Date: ' . $resInfo['start_date']->format(sysLanguage::getDateTimeFormat('long')) . '</i></small>' .
				'<br /><small><i> - Event Name: ' . $resInfo['event_name'] . '</i></small>';
			if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_GATES') == 'True'){
				$return .= '<br /><small><i> - Event Gate: ' . $resInfo['event_gate'] . '</i></small>';
			}
		}

		if (isset($resInfo['shipping']) && $resInfo['shipping'] !== false && isset($resInfo['shipping']['title']) && !empty($resInfo['shipping']['title']) && isset($resInfo['shipping']['cost']) && !empty($resInfo['shipping']['cost'])){
			$return .= '<br /><small><i> - ' . sysLanguage::get('TEXT_INFO_SHIPPING_METHOD') . ' ' . $resInfo['shipping']['title'] . ' (' . $currencies->format($resInfo['shipping']['cost']) . ')</i></small>';
		}

		if (isset($resInfo['deposit_amount']) && $resInfo['deposit_amount'] > 0){
			$return .= '<br /><small><i> - ' . sysLanguage::get('TEXT_INFO_DEPOSIT_AMOUNT') . ' ' . $currencies->format($resInfo['deposit_amount']) . '</i></small>';
		}
		if (isset($resInfo['insurance']) && $resInfo['insurance'] > 0){
			$return .= '<br /><small><i> - ' . sysLanguage::get('TEXT_INFO_INSURANCE') . ' ' . $currencies->format($resInfo['insurance']) . '</i></small>';
		}
		//$return .= '<br />';
		EventManager::notify('ParseReservationInfo', &$return, &$resInfo);

		return $return;
	}
}
