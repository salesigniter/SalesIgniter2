<?php
class lateFees_admin_rentalProducts_rental_report_default extends Extension_lateFees
{

	public function __construct() {
		parent::__construct('lateFees');
	}

	public function load() {
		global $appExtension;
		if ($this->enabled === false){
			return;
		}

		EventManager::attachEvents(array(
				'PurchaseTypeRentalOnReturn'
			), null, $this);
	}

	public function PurchaseTypeRentalOnReturn(&$Rental, PurchaseType_Rental $PurchaseType, &$statusMsg) {
		global $currencies;
		if ($PurchaseType->getConfigData('LATE_FEES_ENABLED') == 'False') {
			return;
		}

		$TimeReturned = strtotime($Rental->date_returned);
		$TimeEnd = strtotime($Rental->end_date);

		if ($TimeReturned > $TimeEnd){
			$ProductId = $Rental->OrdersProducts->products_id;
			$PurchaseType->loadData($ProductId);

			$feeAmount = $PurchaseType->getData('late_fee');
			if ($feeAmount > 0){
				if ($PurchaseType->getData('late_fee_calculation') == 'percent'){
					$fee = $Rental->OrdersProducts->final_price * ($feeAmount / 100);
				}
				else {
					$fee = $feeAmount;
				}

				if ($PurchaseType->getConfigData('CALCULATION_METHOD') == 'Recurring'){
					$feeTotal = $fee * ceil(($TimeReturned - $TimeEnd) / (60 * 60 * 24));
				}
				else {
					$feeTotal = $fee;
				}

				$LateFee = new LateFees;
				$LateFee->fee_status = 0;
				$LateFee->customers_id = $Rental->OrdersProducts->Orders->customers_id;
				$LateFee->orders_products_id = $Rental->orders_products_id;
				$LateFee->fee_amount = $feeTotal;
				$LateFee->save();

				$statusMsg .= '<br>Late Fee Assessed: ' . $currencies->format($feeTotal);
			}
		}
	}
}