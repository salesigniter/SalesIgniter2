<?php
	$onePageCheckout->setPaymentMethod($_POST['payment_method']);
	OrderTotalModules::process();

	EventManager::attachActionResponse(array(
		'success' => true,
		'orderTotalRows' => OrderTotalModules::output()
	), 'json');
?>