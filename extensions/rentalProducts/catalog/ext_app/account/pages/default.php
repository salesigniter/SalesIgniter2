<?php
class rentalProducts_catalog_account_default extends Extension_rentalProducts
{

	public function __construct(){
		parent::__construct();
	}

	public function load(){
		if ($this->isEnabled() === false) return;

		EventManager::attachEvents(array(
			'AccountDefaultAddLinksBlock',
		), null, $this);
	}

	public function AccountDefaultAddLinksBlock(&$pageContents){
		global $userAccount;

		$listIcon = '<span class="ui-icon ui-icon-carat-1-e" style="display:inline-block;"></span>';

		if ($userAccount->isRentalMember()){
			$links = array();
			if ($userAccount->membershipIsActivated()){
				$membership =& $userAccount->plugins['membership'];
				$Qcheck = Doctrine_Query::create()
					->from('MembershipUpdate mu')
					->leftJoin('mu.Membership m')
					->leftJoin('m.MembershipPlanDescription md')
					->where('mu.customers_id = ?', $userAccount->getCustomerId())
					->andWhere('md.language_id = ?', Session::get('languages_id'))
					->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
				if ($Qcheck){
					$ex= sprintf(sysLanguage::get('MY_ACCOUNT_MEMBERSHIP_UPGRADE_EX'), $Qcheck[0]['Membership']['MembershipPlanDescription'][0]['package_name'], tep_date_short($Qcheck[0]['upgrade_date']));
				}else{
					$ex='';
				}

				if($membership->isPastDue()){
					$beforeText .= $errorMsg = sprintf(sysLanguage::get('RENTAL_CUSTOMER_IS_PAST_DUE'), itw_app_link('edit='.$membership->getRentalAddressId(),'account','billing_address_book','SSL'));
				}

				$cancel_mesg = '';
				if ($membership->membershipInfo['canceled']) $cancel_mesg = '<br><span style="color:red">' . sysLanguage::get('TEXT_INFO_PACKAGE_CANCELED') . '</span>';

				$links[] = htmlBase::newElement('a')->html(sysLanguage::get('MY_ACCOUNT_MEMBERSHIP_BILLING_INFO'))
					->setHref(itw_app_link(null, 'account', 'membership_info', 'SSL'));

				$links[] = htmlBase::newElement('a')->html(sysLanguage::get('MY_ACCOUNT_MEMBERSHIP_BILLING_INFO_EDIT'))
					->setHref(itw_app_link('edit=' . $membership->getRentalAddressId(), 'account', 'billing_address_book', 'SSL'));

				$links[] = htmlBase::newElement('a')->html(sysLanguage::get('MY_ACCOUNT_MEMBERSHIP_UPGRADE'))
					->setHref(itw_app_link(null, 'account', 'membership_upgrade', 'SSL'));

				$links[] = htmlBase::newElement('a')->html(sysLanguage::get('MY_ACCOUNT_MEMBERSHIP_CANCEL'))
					->setHref(itw_app_link(null, 'account', 'membership_cancel', 'SSL'));
			} elseif($userAccount->needsRetry()) {
				Session::set('account_action', 'renew');

				$links[] = htmlBase::newElement('a')->html(sysLanguage::get('MY_ACCOUNT_MEMBERSHIP_BILLING_INFO_EDIT'))
					->setHref(itw_app_link('edit=' . $membership->getRentalAddressId(), 'account', 'billing_address_book', 'SSL'));

			}elseif($userAccount->needsRenewal()){
				$links[] = htmlBase::newElement('a')->html(sysLanguage::get('TEXT_CURRENT_RENEW_ACCOUNT_MEMBERSHIP'))
					->setHref(itw_app_link('checkoutType=rental','checkout','default','SSL'));
			}

			$links[] = htmlBase::newElement('a')->html(sysLanguage::get('MY_ACCOUNT_RENTAL_QUEUE'))
				->setHref(itw_app_link('appExt=rentalProducts', 'rentalQueue', 'default', 'SSL'));

			$links[] = htmlBase::newElement('a')->html(sysLanguage::get('MY_ACCOUNT_RENTED_PRODUCTS'))
				->setHref(itw_app_link(null, 'account', 'rented_products', 'SSL'));

			$links[] = htmlBase::newElement('a')->html(sysLanguage::get('MY_ACCOUNT_RENTAL_ISSUES'))
				->setHref(itw_app_link(null, 'account', 'rental_issues', 'SSL'));

			$rentalLinkList = htmlBase::newElement('list')
				->addClass('accountPageLinks');
			foreach($links as $link){
				$rentalLinkList->addItem('', $listIcon . $link->draw());
			}

			$pageContents .= '<div class="main" style="margin-top:1em;">' .
				'<b>' . sysLanguage::get('RENTALS_TITLE') . '</b>' .
				'</div>' .
				'<div class="ui-widget ui-widget-content ui-corner-all" style="padding:1em;">' .
				$rentalLinkList->draw() .
				'</div>';
		}
	}
}
