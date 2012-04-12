<?php
$appContent = $App->getAppContentFile();

if (Session::exists('payment_rental') === true){
	$payment_rental = false;
	Session::remove('payment_rental');
}

$breadcrumb->add(sysLanguage::get('NAVBAR_TITLE'), itw_app_link(null, 'shoppingCart', 'default'));
$productsId = (isset($_POST['products_id']) ? $_POST['products_id'] : (isset($_GET['products_id']) ? $_GET['products_id'] : null));
?>