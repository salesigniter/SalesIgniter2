<?php
class PurchaseType_reservation_setters extends MI_Importable {
	public function setPayPerRentalId($val) { $this->pprInfo['pay_per_rental_id'] = $val; }

	public function setPriceDaily($val) { $this->pprInfo['price_daily'] = $val; }

	public function setPriceWeekly($val) { $this->pprInfo['price_weekly'] = $val; }

	public function setPriceMonthly($val) { $this->pprInfo['price_monthly'] = $val; }

	public function setPriceSixMonth($val) { $this->pprInfo['price_six_month'] = $val; }

	public function setPriceYear($val) { $this->pprInfo['price_year'] = $val; }

	public function setPriceThreeYear($val) { $this->pprInfo['price_three_year'] = $val; }

	public function setQuantity($val) { $this->pprInfo['quantity'] = $val; }

	public function setComboProducts($val) { $this->pprInfo['combo_products'] = $val; }

	public function setComboPrice($val) { $this->pprInfo['combo_price'] = $val; }

	public function setMaxDays($val) { $this->pprInfo['max_days'] = $val; }

	public function setMaxMonths($val) { $this->pprInfo['max_months'] = $val; }

	public function setShipping($val) { $this->pprInfo['shipping'] = $val; }

	public function setOverbooking($val) { $this->pprInfo['overbooking'] = $val; }

	public function setDepositAmount($val) { $this->pprInfo['deposit_amount'] = $val; }

	public function setInsurance($val) { $this->pprInfo['insurance'] = $val; }

	public function setMinRentalDays($val) { $this->pprInfo['min_rental_days'] = $val; }

	public function setMinPeriod($val) { $this->pprInfo['min_period'] = $val; }

	public function setMaxPeriod($val) { $this->pprInfo['max_period'] = $val; }

	public function setMinType($val) { $this->pprInfo['min_type'] = $val; }

	public function setMaxType($val) { $this->pprInfo['max_type'] = $val; }
}
