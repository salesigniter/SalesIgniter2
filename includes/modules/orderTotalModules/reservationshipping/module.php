<?php
class OrderTotalReservationshipping extends OrderTotalModuleBase {

	public function __construct() {
		/*
		 * Default title and description for modules that are not yet installed
		 */
		$this->setTitle('Reservation Shipping');
		$this->setDescription('Reservation Shipping');
		
		$this->init('reservationshipping');

		if ($this->isInstalled() === true){
			$this->showReservationShipping = $this->getConfigData('STATUS');
			$this->allowReservationShipping = $this->getConfigData('MODULE_ORDER_TOTAL_RESERVATION_SHIPPING_ENABLE');
		}

	}

	public function process(array &$outputData) {
		global $order, $appExtension, $userAccount, $onePageCheckout;

		if ($this->allowReservationShipping == 'True' && isset($onePageCheckout->onePage['info']['reservationshipping']['id'])) {
			$order->info['total'] += $onePageCheckout->onePage['info']['reservationshipping']['cost'];

			if($onePageCheckout->onePage['info']['reservationshipping']['cost'] > 0 && ($this->showReservationShipping == 'True') ){
				$outputData['title'] = $this->getTitle() .'('.$onePageCheckout->onePage['info']['reservationshipping']['title'].')'. ':';
				$outputData['text']  = $this->formatAmount($onePageCheckout->onePage['info']['reservationshipping']['cost']);
				$outputData['value'] = $onePageCheckout->onePage['info']['reservationshipping']['cost'];
			}
		}
	}
}
?>