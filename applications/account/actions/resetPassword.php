<?php
if (isset($_GET['rType']) && $_GET['rType'] == 'ajax'){
	$success = false;
	if ($userAccount->processPasswordForgotten($_POST['email_address']) === true){
		$success = true;
	}

	$message = '';
	if ($messageStack->size('pageStack') > 0){
		$message = $messageStack->output('pageStack');
	}
	EventManager::attachActionResponse(array(
		'success' => $success,
		'messageStack' => $message
	), 'json');
}else{
	$link = itw_app_link(null, 'account', 'password_forgotten', 'SSL');
	if ($userAccount->processPasswordForgotten($_POST['email_address']) === true){
		$link = itw_app_link(null, 'account', 'login', 'SSL');
	}
	EventManager::attachActionResponse($link, 'redirect');
}
?>