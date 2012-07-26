<?php
$Coupons = Doctrine_Core::getTable('Coupons');
if (isset($_GET['coupon_id'])){
	$Coupon = $Coupons->find((int)$_GET['coupon_id']);
}
else {
	$Coupon = $Coupons->create();
}

$Coupon->coupon_type = 'F';
$Coupon->coupon_amount = (float)$_POST['coupon_amount'];
if (substr($_POST['coupon_amount'], -1) == '%'){
	$Coupon->coupon_type = 'P';
}

if (isset($_POST['coupon_free_ship'])){
	$Coupon->coupon_type = 'S';
	$Coupon->coupon_amount = 0;
}

$Coupon->coupon_active = $_POST['coupon_active'];
$Coupon->coupon_code = (empty($_POST['coupon_code']) === true ? tep_rand(111111, 999999): $_POST['coupon_code']);
$Coupon->uses_per_coupon = (int)$_POST['uses_per_coupon'];
$Coupon->uses_per_user = (int)$_POST['uses_per_user'];
//$Coupon->number_days_membership = $_POST['number_days_membership'];
$Coupon->coupon_minimum_order = (float)$_POST['coupon_minimum_order'];
$Coupon->coupon_products = '';
$Coupon->coupon_products_use = '';
if (isset($_POST['coupon_products'])){
	$Coupon->coupon_products_use = $_POST['coupon_products_use'];
	$Coupon->coupon_products = implode(',', $_POST['coupon_products']);
}
/*if (isset($_POST['restrict_to_purchase_type'])){
	$Coupon->restrict_to_purchase_type = implode(',', $_POST['restrict_to_purchase_type']);
}
else {
	$Coupon->restrict_to_purchase_type = '';
}*/
//$Coupon->restrict_to_products = $_POST['restrict_to_products'];
//$Coupon->restrict_to_categories = $_POST['restrict_to_categories'];
//$Coupon->restrict_to_customers = $_POST['restrict_to_customers'];
$Coupon->coupon_start_date = $_POST['coupon_start_date'];
$Coupon->coupon_expire_date = $_POST['coupon_expire_date'];

foreach(sysLanguage::getLanguages() as $lInfo){
	$Coupon->CouponsDescription[$lInfo['id']]->language_id = $lInfo['id'];
	$Coupon->CouponsDescription[$lInfo['id']]->coupon_name = $_POST['coupon_name'][$lInfo['id']];
	$Coupon->CouponsDescription[$lInfo['id']]->coupon_description = $_POST['coupon_description'][$lInfo['id']];
}

EventManager::notify('CouponEditBeforeSave', $Coupon);

$Coupon->save();

EventManager::attachActionResponse(array(
	'success' => true,
	'cID'     => $Coupon->coupon_id
), 'json');
?>