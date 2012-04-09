<?php
$PaymentModule = OrderPaymentModules::getModule('gateway2checkout', true);
$PaymentModule->refundOrder(array(
	'sale_id'    => (isset($_POST['sale_id']) ? $_POST['sale_id'] : null),
	'invoice_id' => (isset($_POST['invoice_id']) ? $_POST['invoice_id'] : null),
	'amount'     => (isset($_POST['amount']) ? $_POST['amount'] : 0),
	'category'   => $_POST['category'],
	'comment'    => $_POST['comment']
));

EventManager::attachActionResponse(array(
	'success'     => true,
	'apiResponse' => $PaymentModule->getApiResponse()
), 'json');