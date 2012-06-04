<?php
class PurchaseType_reservation_utilities
{

	private static $EventsCache = array();

	private static $GatesCache = array();

	private static $RentalTypesCache = array();

	public static $RentalPricingCache = array();

	private static $ProductPeriodCache = array();

	public static function getRentalPeriods(){
		$Qperiods = Doctrine_Query::create()
			->from('PayPerRentalPeriods p')
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
		return $Qperiods;
	}

	public static function getProductPeriods($productId, $periodId = null){
		if (!isset(self::$ProductPeriodCache[$productId][$periodId])){
			$ResultSet = Doctrine_Query::create()
				->from('ProductsPayPerPeriods p')
				->where('p.products_id = ?', $productId);

			if (is_null($periodId) === false){
				$ResultSet->andWhere('p.period_id = ?', $periodId);
			}

			self::$ProductPeriodCache[$productId][$periodId] = $ResultSet->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
		}
		return self::$ProductPeriodCache[$productId][$periodId];
	}

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
				->orderBy('pprp.price_per_rental_per_products_id')
				->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

			self::$RentalPricingCache[$pprId] = $QPricePerRentalProducts;
		}
		return self::$RentalPricingCache[$pprId];
	}

	public static function remove_item_by_value($array, $val = '', $preserve_keys = true) {
		if (empty($array) || !is_array($array)) return false;
		if (!in_array($val, $array)) return $array;

		foreach($array as $key => $value) {
			if ($value == $val) unset($array[$key]);
		}

		return ($preserve_keys === true) ? $array : array_values($array);
	}
}
