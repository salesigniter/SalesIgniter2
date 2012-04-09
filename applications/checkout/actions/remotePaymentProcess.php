<?php
// load the after_process function from the payment modules
$foundModule = false;
foreach(OrderPaymentModules::getModules() as $PaymentModule){
	if ($PaymentModule->ownsProcessPage() === true){
		$foundModule = true;
		break;
	}
}
if ($foundModule === true){
	$PaymentModule->afterProcessPayment(null);
	$PaymentModule->afterOrderProcess();
}

EventManager::attachActionResponse(
	itw_app_link((isset($_GET['checkoutType']) ? 'checkoutType=' . $_GET['checkoutType'] : null), 'checkout', 'success', 'SSL'),
	'redirect'
);
?>