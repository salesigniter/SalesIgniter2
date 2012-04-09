<?php
	$membership =& $userAccount->plugins['membership'];
	$planInfo = $membership->getPlanInfo($membership->getPlanId());
	$adminEditLink = itw_admin_app_link('cID=' . $userAccount->getCustomerId(),'customers','edit',SSL);

	$emailEvent = new emailEvent('membership_cancel_request');
	$emailEvent->setVars(array(
		'customerID' => $userAccount->getCustomerId(),
		'full_name' => $userAccount->getFullName(),
		'emailAddress' => $userAccount->getEmailAddress(),
		'paymentMethod' => $membership->getPaymentMethod(),
		'subscriptionDate' => $membership->getMembershipDate(),
		'planID' => $membership->getPlanId(),
		'packageName' =>  $membership->getPlanName(),
		'numberOfRentals' => $membership->getRentalsAllowed(),
		'freeTrialPeriod' => $planInfo['free_trial'],
		'adminEditLink' => $adminEditLink,
		'price' => $currencies->format($membership->getMembershipPrice(), true),
		'membershipIsDays' => false,
		'membershipIsMonths' => false
	));

	if ($membership->getMembershipDays() > 0){
		$emailEvent->setVar('membershipIsDays', true);
		$emailEvent->setVar('membershipPeriod', $membership->getMembershipDays());
	}else{
		$emailEvent->setVar('membershipIsMonths', true);
		$emailEvent->setVar('membershipPeriod', $membership->getMembershipMonths());
	}

	$emailEvent->sendEmail(array(
		'email' => STORE_OWNER_EMAIL_ADDRESS,
		'name' => STORE_OWNER
	));

	$messageStack->add_session('pageStack', sysLanguage::get('CANCELLATION_EMAIL_SENT'), 'success');
	EventManager::attachActionResponse(itw_app_link(null, 'account', 'default', 'SSL'), 'redirect');
?>