<?php
$addressArray['address_type'] = 'customer';
$OrderCustomerAddress = new OrderCreatorAddress($addressArray);

$addressArray['address_type'] = 'billing';
$OrderBillingAddress = new OrderCreatorAddress($addressArray);

$addressArray['address_type'] = 'delivery';
$OrderDeliveryAddress = new OrderCreatorAddress($addressArray);

$addressArray['address_type'] = 'pickup';
$OrderPickupAddress = new OrderCreatorAddress($addressArray);

$Editor->AddressManager->addAddressObj($OrderCustomerAddress);
$Editor->AddressManager->addAddressObj($OrderBillingAddress);
$Editor->AddressManager->addAddressObj($OrderDeliveryAddress);
$Editor->AddressManager->addAddressObj($OrderPickupAddress);

$Editor->AddressManager->updateFromPost();

/**
 * Reset all products tax rates depending on the addresses set above
 */
$TaxAddress = $Editor->AddressManager->getAddress('billing');
if ($TaxAddress === false){
	$TaxAddress = $Editor->AddressManager->getAddress('delivery');
	if ($TaxAddress === false){
		$TaxAddress = $Editor->AddressManager->getAddress('customer');
	}
}

foreach($Editor->ProductManager->getContents() as $OrderProduct){
	$OrderProduct->setTaxRate(tep_get_tax_rate(
		$OrderProduct->getTaxClassId(),
		($TaxAddress !== false ? $TaxAddress->getCountryId() : sysConfig::get('STORE_COUNTRY')),
		($TaxAddress !== false ? $TaxAddress->getZoneId() : sysConfig::get('STORE_ZONE'))
	));
}

/**
 * Update the totals in case a tax rate has changed
 */
$Editor->TotalManager->updateSale($Editor);

$Editor->getSaleModule()->saveProgress($Editor);

EventManager::notify('OrderCreatorSaveCustomerInfoResponse');

EventManager::attachActionResponse('', 'html');
?>