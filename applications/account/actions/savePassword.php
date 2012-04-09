<?php
	$password_current = tep_db_prepare_input($_POST['password_current']);
	$password_new = tep_db_prepare_input($_POST['password_new']);
	$password_confirmation = tep_db_prepare_input($_POST['password_confirmation']);

	$error = false;

	if (strlen($password_current) < ENTRY_PASSWORD_MIN_LENGTH) {
		$error = true;

		$messageStack->add('pageStack', ENTRY_PASSWORD_CURRENT_ERROR, 'error');
	} elseif (strlen($password_new) < ENTRY_PASSWORD_MIN_LENGTH) {
		$error = true;

		$messageStack->add('pageStack', ENTRY_PASSWORD_NEW_ERROR, 'error');
	} elseif ($password_new != $password_confirmation) {
		$error = true;

		$messageStack->add('pageStack', ENTRY_PASSWORD_NEW_ERROR_NOT_MATCHING, 'error');
	}

	if ($error == false) {
		$Check = Doctrine_Manager::getInstance()
			->getCurrentConnection()
			->fetchAssoc("select customers_password from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$userAccount->getCustomerId() . "'");

		if (tep_validate_password($password_current, $Check[0]['customers_password'])) {
			Doctrine_Manager::getInstance()
				->getCurrentConnection()
				->exec("update " . TABLE_CUSTOMERS . " set customers_password = '" . tep_encrypt_password($password_new) . "' where customers_id = '" . (int)$userAccount->getCustomerId() . "'");

			Doctrine_Manager::getInstance()
				->getCurrentConnection()
				->exec("update " . TABLE_CUSTOMERS_INFO . " set customers_info_date_account_last_modified = now() where customers_info_id = '" . (int)$userAccount->getCustomerId() . "'");

			$messageStack->addSession('pageStack', sysLanguage::get('SUCCESS_PASSWORD_UPDATED'), 'success');

			EventManager::attachActionResponse(itw_app_link(null, 'account', 'default', 'SSL'));
		} else {
			$error = true;

			$messageStack->add('pageStack', sysLanguage::get('ERROR_CURRENT_PASSWORD_NOT_MATCHING'), 'error');
		}
	}
?>