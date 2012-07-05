<?php
//echo '<pre>';print_r($_POST);itwExit();
$Editor->InfoManager->setInfo('tracking', array(
	'ups'   => $_POST['ups_tracking_number'],
	'usps'  => $_POST['usps_tracking_number'],
	'fedex' => $_POST['fedex_tracking_number'],
	'dhl'   => $_POST['dhl_tracking_number']
));
$Editor->InfoManager->setInfo('ip_address', $_SERVER['REMOTE_ADDR']);
$Editor->InfoManager->setInfo('admin_id', Session::get('login_id'));
$Editor->InfoManager->setInfo('admin_comments', $_POST['comments']);

if (isset($_POST['convertTo'])){
	AccountsReceivable::convertSale($Editor, $_POST['convertTo']);
}
elseif (isset($_POST['duplicate'])){
	AccountsReceivable::duplicateSale($Editor);
}
elseif (isset($_POST['save'])) {
	$SaleId = AccountsReceivable::saveSale($Editor);
}
elseif (isset($_POST['saveAs'])) {
	AccountsReceivable::saveSale($Editor, $_POST['saveAs']);
}

EventManager::attachActionResponse(
	itw_app_link('appExt=orderCreator&sale_module=' . $Editor->getSaleModule()->getCode() . '&sale_id=' . $SaleId, 'default', 'new'),
	'redirect'
);
