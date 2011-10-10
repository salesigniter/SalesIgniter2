<?php
$Invoice = new StoresFeesInvoices();
$Invoice->stores_id = $_POST['store_id'];
$Invoice->paid = 0;
$Invoice->date_added = time();

$Invoice->fee_royalty = $_POST['royalty_billed'];
$Invoice->fee_royalty_discount = 0;
if (isset($_POST['royalty_billed_discount'])){
	$Invoice->fee_royalty_discount = $_POST['royalty_owed'] - $_POST['royalty_billed'];
}

$Invoice->fee_management = $_POST['management_billed'];
$Invoice->fee_management_discount = 0;
if (isset($_POST['management_billed_discount'])){
	$Invoice->fee_management_discount = $_POST['management_owed'] - $_POST['management_billed'];
}

$Invoice->fee_marketing = $_POST['marketing_billed'];
$Invoice->fee_marketing_discount = 0;
if (isset($_POST['marketing_billed_discount'])){
	$Invoice->fee_marketing_discount = $_POST['marketing_owed'] - $_POST['marketing_billed'];
}

$Invoice->fee_labor = $_POST['labor_billed'];
$Invoice->fee_labor_discount = 0;
if (isset($_POST['labor_billed_discount'])){
	$Invoice->fee_labor_discount = $_POST['labor_owed'] - $_POST['labor_billed'];
}

$Invoice->fee_parts = $_POST['parts_billed'];
$Invoice->fee_parts_discount = 0;
if (isset($_POST['parts_billed_discount'])){
	$Invoice->fee_parts_discount = $_POST['parts_owed'] - $_POST['parts_billed'];
}

$Invoice->save();

EventManager::attachActionResponse(array(
		'success' => true
	), 'json');