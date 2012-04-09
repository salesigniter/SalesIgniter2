<?php
/*
	Rentals Products Extension Version 1

	I.T. Web Experts, Sales Igniter E-Commerce System v2
	http://www.itwebexperts.com

	Copyright (c) 2011 I.T. Web Experts

	This script and it's source is not redistributable
*/

class rentalProducts_admin_customers_edit extends Extension_rentalProducts {

	public function __construct(){
		parent::__construct();
	}
	
	public function load(){
		if ($this->isEnabled() === false) return;
		
		EventManager::attachEvents(array(
			'AdminCustomerEditBuildTabs',
			'AdminNewCustomerAccountBeforeSave',
			'AdminEditCustomerAccountBeforeSave',
			'AdminNewCustomerAccountSendEmail'
		), null, $this);
	}
	
	public function AdminCustomerEditBuildTabs($Customer, &$tabsObj){
		global $currencies, $cID;
		$pageTabsDir = sysConfig::getDirFsCatalog() . 'extensions/rentalProducts/admin/ext_app/customers/page_tabs/';
		ob_start();
		include($pageTabsDir . 'history.php');
		$tab1 = ob_get_contents();
		ob_end_clean();
	
		ob_start();
		include($pageTabsDir . 'pending.php');
		$tab2 = ob_get_contents();
		ob_end_clean();
	
		ob_start();
		include($pageTabsDir . 'out.php');
		$tab3 = ob_get_contents();
		ob_end_clean();

		$tabsObj->addTabHeader('rentalProductsTab1', array('text' => sysLanguage::get('TAB_RENTAL_PRODUCTS_HISTORY')))
		->addTabPage('rentalProductsTab1', array('text' => $tab1))
		->addTabHeader('rentalProductsTab2', array('text' => sysLanguage::get('TAB_RENTAL_PRODUCTS_PENDING')))
		->addTabPage('rentalProductsTab2', array('text' => $tab2))
		->addTabHeader('rentalProductsTab3', array('text' => sysLanguage::get('TAB_RENTAL_PRODUCTS_OUT')))
		->addTabPage('rentalProductsTab3', array('text' => $tab3));
	}

	public function AdminNewCustomerAccountSendEmail(Customers $Customer){
		$Membership = $Customer->CustomersMembership;
		$planInfo = $Membership->Membership;

		$emailEvent = new emailEvent('membership_activated_admin', $Customer->language_id);
		$emailEvent->setVars(array(
			'customerFirstName' => $Customer->customers_firstname,
			'customerLastName' => $Customer->customers_lastname,
			'currentPlanPackageName' => $planInfo->MembershipPlanDescription[0]['name'],
			'currentPlanMembershipDays' => $planInfo->membership_days,
			'currentPlanNumberOfTitles' => $planInfo->no_of_titles,
			'currentPlanFreeTrial' => $planInfo->free_trial,
			'currentPlanPrice' => $planInfo->price
		));

		$emailEvent->sendEmail(array(
			'email' => $Customer->customers_email_address,
			'name'  => $Customer->customers_firstname . ' ' . $Customer->customers_lastname
		));
	}

	public function AdminNewCustomerAccountBeforeSave(Customers $Customer, AddressBook $DefaultAddress){
		if (isset($_POST['make_member'])){
			$Membership = $Customer->CustomersMembership;
			$Membership->activate = $_POST['activate'];

			$Membership->plan_id = $_POST['planid'];
			$planInfo = $Membership->Membership;
			$CustomersMembership->plan_name = $planInfo->package_name;
			$CustomersMembership->plan_price = $planInfo->price;
			$CustomersMembership->plan_tax_class_id = $planInfo->rent_tax_class_id;
			$CustomersMembership->ismember = 'M';
			if ($planInfo->free_trial == 1){
			}

			$Membership->payment_method = $_POST['payment_method'];

			if (isset($_POST['cc_number'])){
				$Membership->card_num = $_POST['cc_number'];
				$Membership->exp_date = $_POST['cc_expires_month'] . $_POST['cc_expires_year'];
				$Membership->card_cvv = $_POST['cc_cvv'];
			}

			if ($_POST['activate'] == 'Y'){
				$next_bill_date = mktime(0,0,0,
					$_POST['next_billing_month'],
					$_POST['next_billing_day'],
					$_POST['next_billing_year']
				);
				$Membership->next_bill_date = date($next_bill_date, DATE_TIMESTAMP);
			}
		}
	}

	public function AdminEditCustomerAccountBeforeSave(Customers $Customer, AddressBook $DefaultAddress){
		if (isset($_POST['planid']) || isset($_POST['activate']) || isset($_POST['make_member'])){
			$Membership = $Customer->CustomersMembership;
			if (isset($_POST['activate'])){
				$Membership->activate = $_POST['activate'];
			}

			if (isset($_POST['planid'])){
				$Membership->plan_id = $_POST['planid'];
			}

			if (isset($_POST['payment_method'])){
				$Membership->payment_method = $_POST['payment_method'];
			}

			if (isset($_POST['cc_number'])){
				$Membership->card_num = $_POST['cc_number'];
				$Membership->exp_date = $_POST['cc_expires_month'] . $_POST['cc_expires_year'];
				$Membership->card_cvv = $_POST['cc_cvv'];
			}

			$planInfo = $Membership->Membership;

			if (isset($_POST['next_billing_month']) && isset($_POST['next_billing_day']) && isset($_POST['next_billing_year'])){
				$next_bill_date = mktime(0,0,0,
					$_POST['next_billing_month'],
					$_POST['next_billing_day'],
					$_POST['next_billing_year']
				);
				$Membership->next_bill_date = date($next_bill_date, DATE_TIMESTAMP);
			}

			// Send email based on certian conditions - BEGIN


			$emailEventName = false;
			if ($_POST['activate'] == 'Y'){
				if (array_key_exists('make_member', $_POST)){
					$emailEventName = 'membership_activated_admin';
				}elseif (tep_not_null($_POST['prev_acti_status']) && $_POST['prev_acti_status'] == 'N'){
					$emailEventName = 'membership_activated_admin';
				}elseif ($_POST['prev_plan_id'] != "" && $_POST['planid'] != $_POST['prev_plan_id']){
					$emailEventName = 'membership_upgraded_admin';
				}
			}elseif ($_POST['prev_acti_status'] == 'N' && $_POST['prev_acti_status'] == 'Y'){
				$emailEventName = 'membership_canceled_admin';
			}

			if ($emailEventName !== false){
				$emailEvent = new emailEvent($emailEventName, $userAccount->getLanguageId());
				$currentPlan = Doctrine_Core::getTable('Membership')->findOneByPlanId((int)$_POST['planid'])->toArray();

				$emailEvent->setVars(array(
					'customerFirstName' => $userAccount->getFirstName(),
					'customerLastName' => $userAccount->getLastName(),
					'currentPlanPackageName' => $currentPlan['MembershipPlanDescription'][0]['name'],
					'currentPlanMembershipDays' => $currentPlan['membership_days'],
					'currentPlanNumberOfTitles' => $currentPlan['no_of_titles'],
					'currentPlanFreeTrial' => $currentPlan['free_trial'],
					'currentPlanPrice' => $currentPlan['price']
				));

				if (isset($_POST['prev_plan_id']) && !empty($_POST['prev_plan_id']) && $_POST['planid'] != $_POST['prev_plan_id']){
					$previousPlan = Doctrine_Core::getTable('Membership')->findOneByPlanId((int)$_POST['prev_plan_id'])->toArray();

					$emailEvent->setVars(array(
						'previousPlanPackageName' => $previousPlan['MembershipPlanDescription'][0]['name'],
						'previousPlanMembershipDays' => $previousPlan['membership_days'],
						'previousPlanNumberOfTitles' => $previousPlan['no_of_titles'],
						'previousPlanFreeTrial' => $previousPlan['free_trial'],
						'previousPlanPrice' => $previousPlan['price']
					));
				}
				$emailEvent->sendEmail(array(
					'email' => $userAccount->getEmailAddress(),
					'name'  => $userAccount->getFullName()
				));
			}
			// Send email based on certian conditions - END
		}
	}
}
?>