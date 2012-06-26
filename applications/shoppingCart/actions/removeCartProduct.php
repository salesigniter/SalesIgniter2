<?php
$success = $ShoppingCart->remove($productsId);

if (SesRequestInfo::isAjax() === true){
	EventManager::attachActionResponse(array(
		'success' => $success
	), 'json');
}
else {
	if ($success === true){
		if (Session::get('layoutType') == 'smartphone'){
			tep_redirect(itw_app_link(null, 'mobile', 'shoppingCart'));
		}else{
			tep_redirect(itw_app_link(null, 'shoppingCart', 'default'));
		}
	}
	else {
		$messageStack->add('pageStack', 'There was an error removing the product from your shopping cart.', 'error');
	}
}
