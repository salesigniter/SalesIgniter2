<?php
class PurchaseType_reservation_utilities
{

	private static $EventsCache = array();

	private static $GatesCache = array();

	private static $RentalTypesCache = array();

	public static $RentalPricingCache = array();

	private static $ProductPeriodCache = array();

	public static function getRentalPeriods()
	{
		$Qperiods = Doctrine_Query::create()
			->from('PayPerRentalPeriods p')
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
		return $Qperiods;
	}

	public static function getProductPeriods($productId, $periodId = null)
	{
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

	public static function getEvents($eventName = false)
	{
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

	public static function getGates($gateName = false)
	{
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

	public static function getRentalTypes()
	{
		if (empty(self::$RentalTypesCache)){
			$Query = Doctrine_Query::create()
				->from('PayPerRentalTypes')
				->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

			self::$RentalTypesCache = ($Query && sizeof($Query) > 0 ? $Query : false);
		}
		return self::$RentalTypesCache;
	}

	public static function getRentalPricing($PayPerRentalId)
	{
		if (!isset(self::$RentalPricingCache[$PayPerRentalId])){
			$QPricePerRentalProducts = Doctrine_Query::create()
				->from('PricePerRentalPerProducts p')
				->leftJoin('p.Description d')
				->leftJoin('p.Type t')
				->where('p.pay_per_rental_id = ?', $PayPerRentalId)
				->andWhere('d.language_id = ?', Session::get('languages_id'))
				->orderBy('p.price_per_rental_per_products_id')
				->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

			self::$RentalPricingCache[$PayPerRentalId] = $QPricePerRentalProducts;
		}
		return self::$RentalPricingCache[$PayPerRentalId];
	}

	/**
	 * Filter through a price array to determine which is the lowest for the reservation
	 *
	 * @static
	 * @param array    $Prices
	 * @param DateTime $StartDate
	 * @param DateTime $EndDate
	 * @return mixed
	 */
	public static function getLowestPrice($Prices, DateTime $StartDate, DateTime $EndDate){
		$NumberOfMinutes = $EndDate->diff($StartDate)->i;

		$BestPrice = $Prices[0];
		foreach($Prices as $PriceInfo){
			//echo $PriceInfo['price'] . ' / ' . ($PriceInfo['Type']['minutes'] * $PriceInfo['number_of']) . "\n";
			$PricePerMinute = ($PriceInfo['price'] / ($PriceInfo['Type']['minutes'] * $PriceInfo['number_of']));
			$TotalPrice = $PricePerMinute * $NumberOfMinutes;
			if ($TotalPrice < $BestPrice['price']){
				$BestPrice = $PriceInfo;
			}
		}
		return $BestPrice;
	}

	public static function getPricingPeriodInfo($PayPerRentalId, DateTime $StartDate, DateTime $EndDate)
	{
		$NumberOfMinutes = $EndDate->diff($StartDate)->i;

		$return = array();
		foreach(self::getRentalPricing($PayPerRentalId) as $PricingInfo){
			$CheckMinutes = $PricingInfo['number_of'] * $PricingInfo['Type']['minutes'];
			if ($CheckMinutes >= $NumberOfMinutes){
				$return['current'] = $PricingInfo;
				break;
			}
			$return['previous'] = $PricingInfo;
		}
		return $return;
	}

	public function discountPrice($price, $PricingInfo, $Discounts, $ReservationInfo)
	{
		global $Editor, $appExtension;
		$checkStoreId = 0;
		if ($appExtension->isEnabled('multiStore')){
			if (isset($ReservationInfo['store_id'])){
				$checkStoreId = $ReservationInfo['store_id'];
			}
			elseif (isset($Editor) && $Editor->hasData('store_id')) {
				$checkStoreId = $Editor->getData('store_id');
			}
			elseif (Session::exists('current_store_id')) {
				$checkStoreId = Session::exists('current_store_id');
			}
		}

		$discount = false;
		if (isset($Discounts[$checkStoreId])){
			$NumberOfMinutes = $ReservationInfo['end_date']->diff($ReservationInfo['start_date'])->i;
			foreach($Discounts[$checkStoreId] as $dInfo){
				if ($dInfo['ppr_type'] == $PricingInfo['pay_per_rental_types_id']){
					$checkFrom = $dInfo['discount_from'] * $PricingInfo['Type']['minutes'];
					$checkTo = $dInfo['discount_to'] * $PricingInfo['Type']['minutes'];
					if ($NumberOfMinutes >= $checkFrom && $NumberOfMinutes <= $checkTo){
						if ($dInfo['discount_type'] == 'percent'){
							$discount = ($price * ($dInfo['discount_amount'] / 100));
						}
						else {
							$discount = $dInfo['discount_amount'];
						}
					}
				}
			}

			if ($discount !== false){
				$price -= $discount;
			}
		}
		return $price;
	}

	public static function remove_item_by_value($array, $val = '', $preserve_keys = true)
	{
		if (empty($array) || !is_array($array)) {
			return false;
		}
		if (!in_array($val, $array)) {
			return $array;
		}

		foreach($array as $key => $value){
			if ($value == $val) {
				unset($array[$key]);
			}
		}

		return ($preserve_keys === true) ? $array : array_values($array);
	}
}
