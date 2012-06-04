<?php
class PurchaseType_reservation_getters extends PurchaseType_reservation_setters
{

	public function getPayPerRentalId() { return $this->pprInfo['pay_per_rental_id']; }

	public function getPriceDaily() { return $this->pprInfo['price_daily']; }

	public function getPriceWeekly() { return $this->pprInfo['price_weekly']; }

	public function getPriceMonthly() { return $this->pprInfo['price_monthly']; }

	public function getPriceSixMonth() { return $this->pprInfo['price_six_month']; }

	public function getPriceYear() { return $this->pprInfo['price_year']; }

	public function getPriceThreeYear() { return $this->pprInfo['price_three_year']; }

	public function getQuantity() { return $this->pprInfo['quantity']; }

	public function getComboProducts() { return $this->pprInfo['combo_products']; }

	public function getComboPrice() { return $this->pprInfo['combo_price']; }



	public function getShippingArray() { return explode(',', $this->pprInfo['shipping']); }

	public function getOverbooking() { return $this->pprInfo['overbooking']; }


	public function getInsurance() { return $this->pprInfo['insurance']; }


	public function getMinPeriod() { return $this->pprInfo['min_period']; }

	public function getMaxPeriod() { return $this->pprInfo['max_period']; }

	public function getMinType() { return $this->pprInfo['min_type']; }

	public function getMaxType() { return $this->pprInfo['max_type']; }

	public function getShipModuleCode() { return $this->shipModuleCode; }

	public function getMaintenance(){ return $this->pprInfo['maintenance']; }









}
