<?php
class OrderPaymentCustom4 extends StandardPaymentModule
{

	public function __construct() {
		global $order;
		/*
		 * Default title and description for modules that are not yet installed
		 */
		$this->setTitle('Custom Payment #4');
		$this->setDescription('Custom Payment #4');

		$this->init('custom4');

		if (is_object($order) && $this->isEnabled() == true){
			if ($order->content_type == 'virtual'){
				$this->enabled = false;
			}
		}
	}

	public function sendPaymentRequest($requestData) {
		return $this->onResponse(array(
				'saleId' => $requestData['saleId'],
				'amount' => $requestData['amount'],
				'message' => 'Awaiting Payment',
				'success' => /*2*/
				1
			));
	}

	public function processPayment(Order $Order) {
		return $this->sendPaymentRequest(array(
			'saleId' => $Order->getSaleId(),
			'amount'  => $Order->TotalManager->getTotalValue('total')
		));
	}

	public function processPaymentCron($saleId) {
		global $order;
		$order->info['payment_method'] = $this->getTitle();

		$this->processPayment();
		return true;
	}

	private function onResponse($logData) {
		$this->onSuccess($logData);
		return true;
	}

	private function onSuccess($logData) {
		$this->logPayment($logData);
	}

	private function onFail($info) {
	}
}

?>