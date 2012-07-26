<?php
class OrderCreatorTotalModulePaymentfee extends OrderTotalPaymentfee
{

	protected $_editable = false;

	public function isEditable(){
		return $this->_editable;
	}

	/**
	 * @param Order $Sale
	 */
	public function updateSale(Order &$Sale){
		$PaymentModule = $Sale->PaymentManager->getPaymentModule();
		if ($this->allowPaymentFee == 'True' && $PaymentModule !== false){
			$paymentFee = explode(',', $this->getConfigData('MODULE_ORDER_TOTAL_PAYMENTFEE_VALUE'));
			$val = '0';
			foreach($paymentFee as $sPayment){
				$method_value = explode('-', $sPayment);
				if ($method_value[0] == $PaymentModule->getCode()){
					$val = $method_value[1];
					break;
				}
			}

			$TotalModule = $Sale->TotalManager->get('total');

			$TotalValue = $TotalModule->getValue();
			if (substr($val, -1) == '%'){
				$val = (float)substr($val, 0, strlen($val) - 1);
				$fee = $TotalValue * $val / 100;
			}
			else {
				$fee = (float)$val;
			}

			$this->setValue($fee);

			$TotalModule->addToValue($fee);
		}
	}

	public function loadSessionData($ModuleJson){
		$this->setDisplayOrder($ModuleJson['display_order']);
		$this->setValue($ModuleJson['value']);
	}
}
