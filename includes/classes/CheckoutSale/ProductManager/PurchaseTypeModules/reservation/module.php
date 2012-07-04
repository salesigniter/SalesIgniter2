<?php
class CheckoutSalePurchaseTypeReservation extends PurchaseType_reservation
{

	protected $ReservationInfo = array();

	public function hasEnoughInventory(OrderProduct $OrderProduct, $Qty = null){
		if ($this->overBookingAllowed() === true){
			return true;
		}

		if ($Qty === null){
			$Qty = $OrderProduct->getQuantity();
		}
		$return = true;
		$excludedBarcodes = array();
		for($count = 0; $count < $Qty; $count++){
			$AvailableBarcode = $this->getAvailableBarcode($this->ReservationInfo, $excludedBarcodes);
			if ($AvailableBarcode > -1){
				$excludedBarcodes[] = $AvailableBarcode;
			}else{
				$return = false;
				break;
			}
		}
		return $return;
	}

	public function onAddFromCart(CheckoutSaleProduct $SaleProduct, ShoppingCartProduct $CartProduct)
	{
		$this->ReservationInfo = $CartProduct->getInfo('ReservationInfo');
	}

	public function onUpdateFromCart(CheckoutSaleProduct $SaleProduct, ShoppingCartProduct $CartProduct)
	{
		$this->ReservationInfo = $CartProduct->getInfo('ReservationInfo');
	}

	public function onSaveSale(&$SaleProduct, $AssignInventory = false)
	{
		global $appExtension, $_excludedBarcodes, $_excludedQuantities;

		PurchaseType_reservation_utilities::onSaveSale(
			$this->ReservationInfo,
			$this,
			$OrderProduct,
			$SaleProduct,
			$AssignInventory
		);
	}

	public function prepareJsonSave(CheckoutSaleProductTypeStandard $ProductType)
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