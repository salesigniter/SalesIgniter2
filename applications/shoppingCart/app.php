<?php
/**
 * Sales Igniter E-Commerce System
 * Version: {ses_version}
 *
 * I.T. Web Experts
 * http://www.itwebexperts.com
 *
 * Copyright (c) {ses_copyright} I.T. Web Experts
 *
 * This script and its source are not distributable without the written consent of I.T. Web Experts
 */

if (Session::exists('payment_rental') === true){
	$payment_rental = false;
	Session::remove('payment_rental');
}

$breadcrumb->add(sysLanguage::get('NAVBAR_TITLE'), itw_app_link(null, 'shoppingCart', 'default'));
$productsId = (isset($_POST['products_id']) ? $_POST['products_id'] : (isset($_GET['products_id']) ? $_GET['products_id'] : null));
