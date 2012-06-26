<?php
//echo '<pre>';print_r($_POST);itwExit();
if (isset($_GET['sale_id']) === false){
	$createAccount = false;
	if (isset($_POST['customers_id'])){
		$Editor->setCustomerId($_POST['customers_id']);
	}
	elseif ((isset($_POST['account_password']) && !empty($_POST['account_password'])) || sysConfig::get('EXTENSION_ORDER_CREATOR_AUTOGENERATE_PASSWORD') == 'True') {
		if (isset($_POST['isType']) && $_POST['isType'] == 'walkin'){
			if (isset($_POST['email']) && !empty($_POST['email'])){
				$Editor->setEmailAddress($_POST['email']);
			}
			else {
				$Editor->addErrorMessage('Email address not set');
			}
			$Editor->setTelephone($_POST['telephone']);
		}
		else {
			if (isset($_POST['room_number']) && !empty($_POST['room_number'])){
				if (isset($_POST['email']) && !empty($_POST['email'])){
					$Editor->setEmailAddress($_POST['email']);
				}
				else {
					$Editor->setEmailAddress('roomnumber' . '_' . $Editor->getData('store_id') . '_' . $_POST['room_number']);
				}

				if (isset($_POST['telephone']) && !empty($_POST['telephone'])){
					$Editor->setTelephone($_POST['telephone']);
				}

				$Editor->infoManager->setInfo('customers_room_number', $_POST['room_number']);
			}
			else {
				$Editor->addErrorMessage('You need to have a room number setup');
			}
		}
		//$Editor->createCustomerAccount($NewOrder->Customers);
	}
	else {
		$Editor->addErrorMessage('You need to setup a password');
	}
}

if (sysConfig::get('EXTENSION_ORDER_CREATOR_NEEDS_LICENSE_PASSPORT') == 'True' && $_POST['isType'] == 'walkin'){
	$hasData = false;
	if (isset($_POST['drivers_license']) && !empty($_POST['drivers_license'])){
		$Editor->setData('customers_drivers_license', $_POST['drivers_license']);
		$hasData = true;
	}
	if (isset($_POST['passport']) && !empty($_POST['passport'])){
		$Editor->setData('customers_passport', $_POST['passport']);
		$hasData = true;
	}
	if ($hasData === false){
		$Editor->addErrorMessage('You need to have a drivers license or passport setup');
	}
}

$Editor->setData('usps_track_num', $_POST['usps_track_num']);
$Editor->setData('usps_track_num2', $_POST['usps_track_num2']);
$Editor->setData('ups_track_num', $_POST['ups_track_num']);
$Editor->setData('ups_track_num2', $_POST['ups_track_num2']);
$Editor->setData('fedex_track_num', $_POST['fedex_track_num']);
$Editor->setData('fedex_track_num2', $_POST['fedex_track_num2']);
$Editor->setData('dhl_track_num', $_POST['dhl_track_num']);
$Editor->setData('dhl_track_num2', $_POST['dhl_track_num2']);
$Editor->setData('ip_address', $_SERVER['REMOTE_ADDR']);
$Editor->setData('admin_id', Session::get('login_id'));

//$Editor->AddressManager->updateFromPost();
//$Editor->ProductManager->updateFromPost();
//$Editor->TotalManager->updateFromPost();

//EventManager::notify('OrderSaveBeforeSave', $NewOrder);
//echo '<pre>';print_r($_POST);itwExit();

if (isset($_POST['convertTo'])){
	AccountsReceivable::convertSale($Editor, $_POST['convertTo']);
}
elseif (isset($_POST['save'])) {
	$SaleId = AccountsReceivable::saveSale($Editor);
}
elseif (isset($_POST['saveAs'])) {
	AccountsReceivable::saveSale($Editor, $_POST['saveAs']);
}

EventManager::attachActionResponse(itw_app_link('appExt=orderCreator&sale_module=' . $Editor->getSaleModule()->getCode() . '&sale_id=' . $SaleId, 'default', 'new'), 'redirect');

/*
if($Editor->hasErrors()){
	$success = false;
}else{
	$success = true;
	if (!isset($_GET['oID'])){
		$NewOrder->bill_attempts = 1;
	}
	if(!isset($_POST['estimateOrder'])){
		if (isset($_POST['paymentQueue'])){
			foreach($_POST['paymentQueue'] as $k => $data){
				$success = $Editor->PaymentManager->processPayment($data, $NewOrder);
				$Editor->addErrorMessage($success['error_message']);
			}
			$NewOrder->save();
		}
		//$NewOrder->payment_module = $_POST['payment_method'];
	}else{
		$success = true;
	}
}
*/
$success = true;
if ($success === true){
	/*$StatusHistory = new OrdersStatusHistory();
	if(!isset($_POST['estimateOrder'])){
		$StatusHistory->orders_status_id = $_POST['status'];
	}else{
		$StatusHistory->orders_status_id = sysConfig::get('ORDERS_STATUS_ESTIMATE_ID');
	}
	$StatusHistory->customer_notified = (int) (isset($_POST['notify']));
	$StatusHistory->comments = $_POST['comments'];
		
	$NewOrder->OrdersStatusHistory->add($StatusHistory);

	$NewOrder->save();
	if (!isset($_GET['oID'])){
		$NewOrder->Customers->customers_default_address_id = $NewOrder->Customers->AddressBook[0]->address_book_id;
		$NewOrder->save();
		if(!isset($_POST['estimateOrder'])){
			$Editor->sendNewOrderEmail($NewOrder);
		}else{
			$Editor->sendNewEstimateEmail($NewOrder);
		}
	}
	if(!isset($_POST['estimateOrder'])){
		$startDate = strtotime(date('Y-m-d'));
		$endDate = strtotime(date('Y-m-d'));
		$hasRes = false;
		foreach($NewOrder->OrdersProducts as $orderp){
			if ($orderp->purchase_type == 'reservation'){
				foreach($orderp->OrdersProductsReservation as $ores){
					if(strtotime($ores['start_date']) < $startDate){
						$startDate = strtotime($ores['start_date']);
					}
					$hasRes = true;
					if(strtotime($ores['end_date']) > $endDate){
						$endDate = strtotime($ores['end_date']);
					}
				}
			}
		}

		$startDate = date('Y-m-d H:i:s', $startDate);
		$endDate = date('Y-m-d H:i:s', $endDate);
		if(sysConfig::get('EXTENSION_ORDER_CREATOR_MESSAGE_ON_SAVE') == 'True'){
			$messageStack->addSession('pageStack','Order successfully saved.<a style="font-size:14px;color:red" target="_blank" href="'.itw_catalog_app_link('appExt=pdfPrinter&oID=' . $NewOrder->orders_id, 'generate_pdf', 'default').'">Print Invoice</a><br/>'.(($hasRes)?'<a style="font-size:14px;color:red" href="'.itw_app_link('appExt=payPerRentals&start_date='.$startDate.'&end_date='.$endDate.'&highlightOID='.$NewOrder->orders_id, 'send', 'default').'">Checkout Reservation</a>':''), 'success');
		}

		EventManager::attachActionResponse(itw_app_link('oID=' . $NewOrder->orders_id, 'orders', 'details'), 'redirect');
	}else{
		EventManager::attachActionResponse(itw_app_link('oID=' . $NewOrder->orders_id.'&isEstimate=1', 'orders', 'details'), 'redirect');
	}*/
}
else {
	if (isset($_POST['estimateOrder'])){
		$est = '&isEstimate=1';
	}
	else {
		$est = '';
	}
	if (isset($_GET['oID'])){
		EventManager::attachActionResponse(itw_app_link('appExt=orderCreator&isType=' . $_POST['isType'] . '&error=true&oID=' . $_GET['oID'] . $est, 'default', 'new'), 'redirect');
	}
	else {
		EventManager::attachActionResponse(itw_app_link('appExt=orderCreator&isType=' . $_POST['isType'] . '&error=true' . $est, 'default', 'new'), 'redirect');
	}
}
