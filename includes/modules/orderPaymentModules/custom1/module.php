<?php
class OrderPaymentCustom1 extends StandardPaymentModule
{

	public function __construct() {
		global $order;
		/*
		 * Default title and description for modules that are not yet installed
		 */
		$this->setTitle('Custom Payment #1');
		$this->setDescription('Custom Payment #1');

		$this->init('custom1');

		if (is_object($order) && $this->isEnabled() == true){
			if ($order->content_type == 'virtual'){
				$this->enabled = false;
			}
		}
	}

	public function sendPaymentRequest($requestData) {
		return $this->onResponse(array(
				'orderID'   => $requestData['orderID'],
				'amount'    => $requestData['amount'],
				'message'   => (isset($requestData['message']) ? $requestData['message'] : 'Awaiting Payment'),
				'success'   => 1,
				'is_refund' => (isset($requestData['is_refund']) ? $requestData['is_refund'] : 0)
			));
	}

	public function processPayment($orderID = null, $amount = null) {
		global $order;

		return $this->sendPaymentRequest(array(
				'orderID' => $order->newOrder['orderID'],
				'amount' => $order->info['total']
			));
	}

	public function refundPayment($requestData){
		return $this->sendPaymentRequest(array(
			'orderID' => $requestData['orderID'],
			'amount'  => -$requestData['amount'],
			'message' => 'Refund Issued In Amount Of: -' . $requestData['amount'],
			'is_refund' => 1
		));
	}

	public function processPaymentCron($orderID) {
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