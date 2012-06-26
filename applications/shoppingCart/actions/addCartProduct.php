<?php
$success = $ShoppingCart->add($productsId);
if (SesRequestInfo::isAjax() === true){
	echo json_encode(array(
		'success' => $success
	));
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
		$messageStack->add('pageStack', 'There was an error adding the product to your shopping cart.', 'error');
	}
}
?>