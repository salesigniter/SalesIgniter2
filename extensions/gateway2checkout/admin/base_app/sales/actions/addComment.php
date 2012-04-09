<?php
$PaymentModule = OrderPaymentModules::getModule('gateway2checkout', true);
$PaymentModule->commentOrder(array(
	'sale_id'      => $_POST['sale_id'],
	'sale_comment' => $_POST['comment'],
	'cc_vendor'    => $_POST['notify_vendor'],
	'cc_customer'  => $_POST['notify_customer']
));

$Comment = '';
if ($_POST['notify_vendor'] == '1' || $_POST['notify_customer'] == '1'){
	$sentTo = array();
	if ($_POST['notify_vendor'] == '1'){
		$sentTo[] = 'Vendor';
	}
	if ($_POST['notify_customer'] == '1'){
		$sentTo[] = 'Customer';
	}
	$Comment .= '[* Sent: ' . implode('/', $sentTo) . ' *] ';
}
$Comment .= $_POST['comment'];

EventManager::attachActionResponse(array(
	'success'     => true,
	'apiResponse' => $PaymentModule->getApiResponse(),
	'newComment'  => array(
		'Date'    => date(sysLanguage::getDateTimeFormat()),
		'Who'     => $PaymentModule->getConfigData('USERNAME'),
		'Ip'      => $_SERVER['REMOTE_ADDR'],
		'Comment' => $Comment
	)
), 'json');