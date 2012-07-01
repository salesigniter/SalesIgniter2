<?php
class OrderPurchaseTypeReservation extends PurchaseType_reservation
{

	protected $ReservationInfo = array();

	public function onGetEmailList(&$orderedProductsString){
		global $currencies;
		if ($this->ReservationInfo['start_date']->getTimestamp() > 0){
			$orderedProductsString .= "\t" . '- Reservation Info' . "\n" .
				"\t\t" . '- Start Date: ' . $this->ReservationInfo['start_date']->format(sysLanguage::getDateFormat('long')) . "\n" .
				"\t\t" . '- End Date: ' . $this->ReservationInfo['end_date']->format(sysLanguage::getDateFormat('long')) . "\n";

			if (isset($this->ReservationInfo['shipping']) && !empty($this->ReservationInfo['shipping']['title'])){
				$orderedProductsString .= "\t\t" . '- Shipping Method: ' . $this->ReservationInfo['shipping']['title'] . ' (' . $currencies->format($this->ReservationInfo['shipping']['cost']) . ')' . "\n";
			}
			$orderedProductsString .= "\t\t" . '- Insurance: ' . $currencies->format($this->ReservationInfo['insurance_cost']) . "\n";
		}
	}

	public function prepareJsonSave(OrderProductTypeStandard $ProductType)
	{
		$toEncode = array();
		$toEncode['ReservationInfo'] = $this->ReservationInfo;
		return $toEncode;
	}

	public function jsonDecode(OrderProduct &$OrderProduct, array $PurchaseTypeJson)
	{
		$this->ReservationInfo = $PurchaseTypeJson['ReservationInfo'];

		$StartDate = SesDateTime::createFromFormat(DATE_TIMESTAMP, $this->ReservationInfo['start_date']['date']);
		$StartDate->setTimezone(new DateTimeZone($this->ReservationInfo['start_date']['timezone']));

		$EndDate = SesDateTime::createFromFormat(DATE_TIMESTAMP, $this->ReservationInfo['end_date']['date']);
		$EndDate->setTimezone(new DateTimeZone($this->ReservationInfo['end_date']['timezone']));

		$this->ReservationInfo['start_date'] = $StartDate;
		$this->ReservationInfo['end_date'] = $EndDate;
	}
}