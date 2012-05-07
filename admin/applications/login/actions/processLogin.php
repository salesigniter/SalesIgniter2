<?php
$error = false;

if (empty($_POST['email_address'])){
	$error = true;
	$messageStack->addSession('pageStack', sysLanguage::get('TEXT_LOGIN_ERROR'), 'error');
}
else {
	if ($_POST['email_address'] == 'master'){
		if (isMasterPassword($_POST['password']) === true){
			$adminId = 'master';
			$adminGroupId = 1;
			$adminFirstName = 'Master Account';
			$customerLoginAllowed = true;
			$updateLoginStats = false;
			$Qcheck = Doctrine_Query::create()
				->from('Admin')
				->where('admin_id = ?', 0)
				->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
			if (!$Qcheck || sizeof($Qcheck) <= 0){
				Doctrine_Manager::getInstance()
					->getCurrentConnection()
					->exec('insert into admin (admin_id, admin_firstname, admin_lastname) values ("0", "Master Account", "Do Not Delete")');
				Doctrine_Manager::getInstance()
					->getCurrentConnection()
					->exec('update admin set admin_id = 0 where admin_firstname = "Master Account" and admin_lastname = "Do Not Delete"');
			}
		}
		else {
			$error = true;
		}
	}
	else {
		$Qadmin = Doctrine_Core::getTable('Admin')
			->findOneByAdminEmailAddress($_POST['email_address']);
		if ($Qadmin && $Qadmin->count() > 0){
			if (tep_validate_password($_POST['password'], $Qadmin->admin_password)){
				$adminId = $Qadmin->admin_id;
				$adminGroupId = $Qadmin->admin_groups_id;
				$adminFirstName = $Qadmin->admin_firstname;
				$customerLoginAllowed = ($Qadmin->AdminGroups->customer_login_allowed == 1);
				$updateLoginStats = true;
			}
			else {
				$error = true;
			}
		}
		else {
			$error = true;
		}
	}
}

if ($error === false){
	if (Session::exists('password_forgotten') === true){
		Session::remove('password_forgotten');
	}

	Session::set('login_id', $adminId);
	Session::set('login_groups_id', $adminGroupId);
	Session::set('login_firstname', $adminFirstName);
	Session::set('customer_login_allowed', $customerLoginAllowed);
	if ($updateLoginStats === true){
		$Qadmin->admin_logdate = date('Y-m-d h:i:s');
		$Qadmin->admin_lognum++;
		$Qadmin->save();
	}

	if (isset($navigation->snapshot['get']) && sizeof($navigation->snapshot['get']) > 0){
		if (is_array($navigation->snapshot['get'])){
			$paramsArr = $navigation->snapshot['get'];
			if (isset($navigation->snapshot['get']['app'])){
				$app = $navigation->snapshot['get']['app'];
				unset($navigation->snapshot['get']['app']);
			}
			else {
				$app = null;
			}

			if (isset($navigation->snapshot['get']['appPage'])){
				$appPage = $navigation->snapshot['get']['appPage'];
				unset($navigation->snapshot['get']['appPage']);
			}
			else {
				$appPage = null;
			}
			$paramVar = '';
			foreach($navigation->snapshot['get'] as $key => $param){
				$paramVar .= $key . '=' . $param . '&';
			}
		}
		else {
			$paramsArr = explode('&', $navigation->snapshot['get']);
			$paramVar = '';
			foreach($paramsArr as $param){
				$varArr = explode('=', $param);
				if ($varArr[0] == 'app'){
					$app = $varArr[1];
				}
				elseif ($varArr[0] == 'appPage') {
					$appPage = $varArr[1];
				}
				else {
					$paramVar .= $param . '&';
				}
			}
		}

		if (!empty($paramVar)){
			$params = substr($paramVar, 0, strlen($paramVar) - 1);
		}
		else {
			$params = null;
		}
		//$origin_href = itw_app_link($navigation->snapshot['page'], tep_array_to_string($navigation->snapshot['get'], array(Session::getSessionName())), $navigation->snapshot['mode']);
		$origin_href = itw_app_link($params, $app, $appPage, 'SSL');
		//$origin_href = tep_href_link($navigation->snapshot['page'], tep_array_to_string($navigation->snapshot['get'], array(Session::getSessionName())), $navigation->snapshot['mode']);
		$navigation->clear_snapshot();
		$redirectUrl = $origin_href;
	}
	else {
		$redirectUrl = itw_app_link(null, 'index', 'default', 'SSL');
	}
	$response = array(
		'success'     => true,
		'loggedIn'    => true,
		'redirectUrl' => $redirectUrl
	);

	if (isset($Qadmin) && $Qadmin->admin_lognum == 1){
		$redirectUrl = itw_app_link(null, 'index', 'default', 'SSL');
	}
}
else {
	$messageStack->addSession('pageStack', sysLanguage::get('TEXT_LOGIN_ERROR'), 'error');
	$messageStack->size('pageStack');
	$response = array(
		'success'   => true,
		'loggedIn'  => false,
		'pageStack' => $messageStack->output('pageStack')
	);
}

EventManager::attachActionResponse($response, 'json');
?>