<?php
$Coupon = Doctrine_Core::getTable('Coupons')->find((int)$_GET['coupon_id']);
$success = false;
if ($Coupon){
	$Coupon->delete();
	$success = true;
}

EventManager::attachActionResponse(array(
	'success' => $success
), 'json');
