<?php
$appPage = $App->getAppPage();
$appContent = $App->getAppContentFile();
if ($appPage == 'success' && $userAccount->isLoggedIn()){
}
else {
	$App->addJavascriptFile('ext/jQuery/external/pass_strength/jQuery.pstrength.js');

	require('includes/classes/http_client.php');
	include('includes/functions/crypt.php');
	$navigation->set_snapshot();
	if (sysConfig::get('ONEPAGE_LOGIN_REQUIRED') == 'true'){
		if ($userAccount->isLoggedIn() === false){
			if (!isset($_GET['checkoutType']) || (isset($_GET['checkoutType']) && $_GET['checkoutType'] == 'default')){
				$navigation->set_snapshot(array('mode' => 'SSL', 'page' => 'application.php', 'get' => 'app=checkout&appPage=default'));
				tep_redirect(itw_app_link(null, 'account', 'login', 'SSL'));
			}
		}
	}

	if (isset($_GET['rType'])){
		header('content-type: text/html; charset=' . sysLanguage::getCharset());
	}

	if ($userAccount->isFrozen() === true){
		$messageStack->addSession('pageStack', 'Your account is frozen and cannot place new orders, please contact the administrator if you feel this is an error.', 'warning');
		tep_redirect(itw_app_link(null, 'account', 'default', 'SSL'));
	}

	if ($userAccount->isLoggedIn() && $userAccount->plugins['membership']->isRentalMember() && $userAccount->plugins['membership']->isActivated() && isset($_GET['checkoutType']) && $_GET['checkoutType'] == 'rental' && !isset($_GET['isUpgrade'])){
		tep_redirect(itw_app_link(null, 'account', 'default', 'SSL'));
	}

	if ($ShoppingCart->countContents() == 0){
		tep_redirect(itw_app_link(null, 'shoppingCart', 'default'));
	}

	EventManager::notify('CheckoutBeforeExecute');

	$isPostPage = (isset($_POST) && !empty($_POST));

	if ($isPostPage === false && $action != 'remotePaymentProcess'){
		if ($ShoppingCart->hasId() === false){
			$ShoppingCart->setId();
		}
		Session::set('cartID', $ShoppingCart->getId());
	}

	OrderPaymentModules::loadModules();
	OrderShippingModules::loadModules();
	OrderTotalModules::loadModules();

	$runInit = false;
	if (!isset($_GET['action'])){
		if (Session::exists('CheckoutSale')){
			$runInit = true;
		}
		else {
			$CheckoutSale = new CheckoutSale('order');
			$CheckoutSale->importUserAccount($userAccount);

			Session::set('CheckoutSale', $CheckoutSale);
		}
	}
	else {
		$runInit = true;
	}

	$CheckoutSale =& Session::getReference('CheckoutSale');
	if ($runInit === true){
		//echo __FILE__ . '::' . __LINE__ . '<br>';
		$CheckoutSale->init();
	}
	$CheckoutSale->importShoppingCart($ShoppingCart);
}
?>