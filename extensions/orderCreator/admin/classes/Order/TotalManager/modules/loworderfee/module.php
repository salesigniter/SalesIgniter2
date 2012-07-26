<?php
class OrderCreatorTotalModuleLoworderfee extends OrderTotalLoworderfee
{

	protected $_editable = false;

	public function isEditable(){
		return $this->_editable;
	}

	/**
	 * @param Order $Sale
	 */
	public function updateSale(Order &$Sale){
		$this->setValue(0);
		if ($this->allowFees == 'True'){
			$DeliveryAddress = $Sale->AddressManager->getAddress('delivery');
			$pass = true;
			if ($DeliveryAddress){
				switch($this->feesDestination){
					case 'National':
						if ($DeliveryAddress->getCountryId() == sysConfig::get('STORE_COUNTRY')) {
							$pass = true;
						}
						break;
					case 'International':
						if ($DeliveryAddress->getCountryId() != sysConfig::get('STORE_COUNTRY')) {
							$pass = true;
						}
						break;
					case 'Both':
						$pass = true;
						break;
				}
			}

			if ($pass === true){
				$TotalModule = $Sale->TotalManager->get('total');
				$TotalValue = $TotalModule->getValue();
				$ShippingValue = $Sale->TotalManager->getTotalValue('shipping');

				if (($TotalValue - $ShippingValue) < $this->lowOrderAmount){
					$this->setText(sysCurrency::format(
						$this->lowOrderFee,
						true,
						$Sale->getCurrency(),
						$Sale->getCurrencyValue()
					));
					$this->setValue($this->lowOrderFee);

					$TotalModule->addToValue($this->getValue());
				}
			}
		}
	}

	public function loadSessionData($ModuleJson){
		$this->setDisplayOrder($ModuleJson['display_order']);
		$this->setValue($ModuleJson['value']);
	}
}
