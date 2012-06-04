<?php
/*
$Customers = Doctrine_Core::getTable('Customers');
if (isset($_GET['customer_id'])){
	$Customer = $Customers->find((int) $_GET['customer_id']);

	$AddressBook = $Customer->AddressBook->getTable();
	$Address = $AddressBook->find($Customer->customers_default_address_id);
}else{
	$Customer = $Customers->create();

	$CustomersInfo = $Customer->CustomersInfo;
	$CustomersInfo->customers_info_number_of_logons = 0;

	$AddressBook = $Customer->AddressBook;
	$Address = $AddressBook[0];
}

$error = false;
$Customer->customers_firstname = $_POST['customers_firstname'];
$Customer->customers_lastname = $_POST['customers_lastname'];
$Customer->customers_email_address = $_POST['customers_email_address'];
$Customer->customers_number = (!empty($_POST['customers_number']) ? $_POST['customers_number'] : tep_create_random_value(8));
$Customer->customers_account_frozen = (isset($_POST['customers_account_frozen']) ? 1 : 0);

if (isset($_POST['customers_password'])){
	$Customer->customers_password = $_POST['customers_password'];
}

if (isset($_POST['customers_city_birth'])){
	$Customer->customers_city_birth = $_POST['customers_city_birth'];
}

if (isset($_POST['customers_gender'])){
	$Customer->customers_gender = $_POST['customers_gender'];
}

if (isset($_POST['customers_newsletter'])){
	$Customers->customers_newsletter = $_POST['customers_newsletter'];
}

if (isset($_POST['customers_telephone'])){
	$Customers->customers_telephone = $_POST['customers_telephone'];
}

if (isset($_POST['customers_fax'])){
	$Customers->customers_fax = $_POST['customers_fax'];
}

if (isset($_POST['customers_dob'])){
	$Customers->customers_dob = $_POST['customers_dob'];
}

$Address->entry_firstname = $_POST['customers_firstname'];
$Address->entry_lastname = $_POST['customers_lastname'];
$Address->entry_street_address = $_POST['entry_street_address'];
$Address->entry_postcode = $_POST['entry_postcode'];
$Address->entry_city = $_POST['entry_city'];
$Address->entry_country_id = $_POST['country'];
if (isset($_POST['entry_zone_id'])){
	$Address->entry_zone_id = $_POST['entry_zone_id'];
}
elseif ($_POST['entry_state']){
	$Address->entry_state = $_POST['entry_state'];
}

if (isset($_POST['entry_suburb'])){
	$Address->entry_suburb = $_POST['entry_suburb'];
}

if (isset($_POST['entry_company'])){
	$Address->entry_company = $_POST['entry_company'];
}

if (isset($_POST['entry_cif'])){
	$Address->entry_cif = $_POST['entry_cif'];
}

if (isset($_POST['entry_vat'])){
	$Address->entry_vat = $_POST['entry_vat'];
}

echo '<pre>';print_r($Customer->toArray());
if ($Customer->isValid(true) === false){
	$CustomerErrorStack = $Customer->getErrorStack();

	foreach($CustomerErrorStack->toArray() as $FieldName => $ErrorTypes){
		foreach($ErrorTypes as $ErrorType){
			$messageStack->add('pageStack', sysLanguage::get('TEXT_' . strtoupper($FieldName) . '_ERROR_' . strtoupper($ErrorType)), 'error');
		}
	}
	$noExit = true;
}else{
	echo '<pre>';print_r($Customer->toArray());

	if (!isset($_GET['customer_id'])){
		EventManager::notify('AdminNewCustomerAccountBeforeSave', $Customer, $Address);
		$Customer->save();

		$Customer->customers_default_address_id = $Address->address_book_id;
		$Customer->customers_delivery_address_id = $Address->address_book_id;
		$Customer->save();

		if (isset($_POST['email_new_customer'])){
			$firstName = $Customer->customers_firstname;
			$lastName = $Customer->customers_lastname;
			$emailAddress = $Customer->customers_email_address;
			$fullName = $firstName . ' ' . $lastName;

			$emailEvent = new emailEvent('create_account');

			$emailEvent->setVars(array(
				'email_address' => $emailAddress,
				'password'      => (isset($_POST['customers_password']) ? $_POST['customers_password'] : $Customer->customers_password),
				'firstname'     => $firstName,
				'lastname'      => $lastName,
				'full_name'     => $fullName
			));

			$emailEvent->sendEmail(array(
				'email' => $emailAddress,
				'name'  => $fullName
			));

			EventManager::notify('AdminNewCustomerAccountSendEmail', $Customer);
		}
	}else{
		EventManager::notify('AdminEditCustomerAccountBeforeSave', $Customer, $Address);
		$Customer->save();
	}

	EventManager::attachActionResponse(itw_app_link(tep_get_all_get_params(array('customer_id', 'action')) . 'customer_id=' . $Customer->customers_id, null, 'default'), 'redirect');
}
//echo '<pre>';print_r($CustomerErrorStack);print_r($AddressErrorStack);itwExit();
*/

$hasError = false;
$userAccount = new rentalStoreUser((isset($_GET['customer_id']) ? $_GET['customer_id'] : false));
$userAccount->loadPlugins();
$addressBook =& $userAccount->plugins['addressBook'];
$membership =& $userAccount->plugins['membership'];

$accountValidation = array(
	'entry_firstname'      => $_POST['customers_firstname'],
	'entry_lastname'       => $_POST['customers_lastname'],
	'entry_street_address' => $_POST['entry_street_address'],
	'entry_postcode'       => $_POST['entry_postcode'],
	'entry_city'           => $_POST['entry_city'],
	'entry_country_id'     => $_POST['country'],
	'entry_state'          => (isset($_POST['entry_state']) ? $_POST['entry_state'] : 'none'),
	'email_address'        => $_POST['customers_email_address']
);

if (array_key_exists('entry_suburb', $_POST)) {
	$accountValidation['entry_suburb'] = $_POST['entry_suburb'];
}
if (array_key_exists('entry_company', $_POST)) {
	$accountValidation['entry_company'] = $_POST['entry_company'];
}

if (array_key_exists('entry_cif', $_POST)) {
	$accountValidation['entry_cif'] = $_POST['entry_cif'];
}

if (array_key_exists('customers_password', $_POST) && !empty($_POST['customers_password'])){
	$accountValidation['password'] = $_POST['customers_password'];
	$accountValidation['confirmation'] = $_POST['customers_password'];
}

if (array_key_exists('entry_vat', $_POST)) {
	$accountValidation['entry_vat'] = $_POST['entry_vat'];
}
if (array_key_exists('customers_city_birth', $_POST)) {
	$accountValidation['city_birth'] = $_POST['customers_city_birth'];
}

if (array_key_exists('customers_gender', $_POST)) {
	$accountValidation['entry_gender'] = $_POST['customers_gender'];
}
if (array_key_exists('customers_newsletter', $_POST)) {
	$accountValidation['newsletter'] = $_POST['customers_newsletter'];
}
if (array_key_exists('customers_telephone', $_POST)) {
	$accountValidation['telephone'] = $_POST['customers_telephone'];
}
if (array_key_exists('customers_notes', $_POST)) {
	$accountValidation['notes'] = $_POST['customers_notes'];
}
if (array_key_exists('customers_fax', $_POST)) {
	$accountValidation['fax'] = $_POST['customers_fax'];
}
if (array_key_exists('customers_dob', $_POST)) {
	$accountValidation['dob'] = $_POST['customers_dob'];
}

$hasError = $userAccount->validate($accountValidation);
if ($hasError === false){
	$userAccount->setFirstName($accountValidation['entry_firstname']);
	$userAccount->setLastName($accountValidation['entry_lastname']);
	$userAccount->setEmailAddress($accountValidation['email_address']);
	$userAccount->setPassword($accountValidation['password']);
	$userAccount->setTelephoneNumber($accountValidation['telephone']);
	//$userAccount->setNotes($accountValidation['notes']);
	$userAccount->setFaxNumber($accountValidation['fax']);
	$userAccount->setNewsLetter($accountValidation['newsletter']);
	if (isset($accountValidation['entry_gender'])){
		$userAccount->setGender($accountValidation['entry_gender']);
	}
	if (isset($accountValidation['dob'])){
		$userAccount->setDateOfBirth($accountValidation['dob']);
	}
	$userAccount->setMemberNumber((!empty($_POST['customers_number']) ? $_POST['customers_number'] : tep_create_random_value(8)));
	$userAccount->setAccountFrozen((isset($_POST['customers_account_frozen'])));

	if (isset($accountValidation['city_birth'])){
		$userAccount->setCityBirth($accountValidation['city_birth']);
	}

	if (isset($_POST['customers_password']) && !empty($_POST['customers_password'])){
		$userAccount->setPassword($_POST['customers_password']);
	}

	if (isset($_GET['customer_id'])){
		$userAccount->updateCustomerAccount();
		$addressBook->updateAddress((int)$_POST['default_address_id'], $accountValidation);
	}
	else {
		$userAccount->createNewAccount();
		$addressBook->insertAddress($accountValidation, true);
	}

	if (array_key_exists('planid', $_POST) || array_key_exists('activate', $_POST) || array_key_exists('make_member', $_POST)){
		if (array_key_exists('activate', $_POST)){
			$membership->setActivationStatus($_POST['activate']);
		}
		if (isset($_POST['planid'])){
			$membership->setPlanId($_POST['planid']);
		}
		if (isset($_POST['payment_method'])){
			$membership->setPaymentMethod($_POST['payment_method']);
		}

		if (array_key_exists('cc_number', $_POST)){
			$membership->setCreditCardNumber($_POST['cc_number']);
			$membership->setCreditCardExpirationDate($_POST['cc_expires_month'] . $_POST['cc_expires_year']);
			$membership->setCreditCardCvvNumber($_POST['cc_cvv']);
		}

		if (isset($_POST['planid'])){
			$planInfo = $membership->getPlanInfo($_POST['planid']);
		}

		if (isset($_POST['member']) && $_POST['member'] == 'Y'){
			if (isset($_POST['next_billing_month']) && isset($_POST['next_billing_day']) && isset($_POST['next_billing_year'])){
				$next_bill_date = mktime(0, 0, 0,
					$_POST['next_billing_month'],
					$_POST['next_billing_day'],
					$_POST['next_billing_year']
				);
				$membership->setNextBillDate($next_bill_date);
			}
		}
		if (array_key_exists('make_member', $_POST)){
			$membership->createNewMembership();
		}
		else {
			$membership->updateMembership();
		}

		// Send email based on certian conditions - BEGIN

		$emailEventName = false;
		if ($_POST['activate'] == 'Y'){
			if (array_key_exists('make_member', $_POST)){
				$emailEventName = 'membership_activated_admin';
			}
			elseif (tep_not_null($_POST['prev_acti_status']) && $_POST['prev_acti_status'] == 'N') {
				$emailEventName = 'membership_activated_admin';
			}
			elseif ($_POST['prev_plan_id'] != "" && $_POST['planid'] != $_POST['prev_plan_id']) {
				$emailEventName = 'membership_upgraded_admin';
			}
		}
		elseif ($_POST['prev_acti_status'] == 'N' && $_POST['prev_acti_status'] == 'Y') {
			$emailEventName = 'membership_canceled_admin';
		}

		if ($emailEventName !== false){
			$emailEvent = new emailEvent($emailEventName, $userAccount->getLanguageId());
			$currentPlan = Doctrine_Core::getTable('Membership')->findOneByPlanId((int)$_POST['planid'])->toArray();

			$emailEvent->setVars(array(
				'customerFirstName'         => $userAccount->getFirstName(),
				'customerLastName'          => $userAccount->getLastName(),
				'currentPlanPackageName'    => $currentPlan['MembershipPlanDescription'][0]['name'],
				'currentPlanMembershipDays' => $currentPlan['membership_days'],
				'currentPlanNumberOfTitles' => $currentPlan['no_of_titles'],
				'currentPlanFreeTrial'      => $currentPlan['free_trial'],
				'currentPlanPrice'          => $currentPlan['price']
			));

			if (isset($_POST['prev_plan_id']) && !empty($_POST['prev_plan_id']) && $_POST['planid'] != $_POST['prev_plan_id']){
				$previousPlan = Doctrine_Core::getTable('Membership')->findOneByPlanId((int)$_POST['prev_plan_id'])
					->toArray();

				$emailEvent->setVars(array(
					'previousPlanPackageName'    => $previousPlan['MembershipPlanDescription'][0]['name'],
					'previousPlanMembershipDays' => $previousPlan['membership_days'],
					'previousPlanNumberOfTitles' => $previousPlan['no_of_titles'],
					'previousPlanFreeTrial'      => $previousPlan['free_trial'],
					'previousPlanPrice'          => $previousPlan['price']
				));
			}
			if (isset($_POST['sendEmail'])){
				$emailEvent->sendEmail(array(
					'email' => $userAccount->getEmailAddress(),
					'name'  => $userAccount->getFullName()
				));
			}
		}
		// Send email based on certian conditions - END
	}

	EventManager::attachActionResponse(itw_app_link(tep_get_all_get_params(array('customer_id', 'action')) . 'customer_id=' . $userAccount->getCustomerId(), null, 'default'), 'redirect');
}
elseif ($error == true) {
	$cInfo = new objectInfo($_POST);
	$noExit = true;
}
?>