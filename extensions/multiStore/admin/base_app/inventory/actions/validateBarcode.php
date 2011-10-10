<?php
$Qcheck = mysql_query('select count(*) as total from products_inventory_barcodes where barcode = "' . addslashes(strip_tags($_GET['code'])) . '"');
$check = mysql_fetch_assoc($Qcheck);
if ($check['total'] <= 0){
	$isValid = false;
}else{
	$isValid = true;
}

EventManager::attachActionResponse(array(
		'success' => true,
		'isValid' => $isValid
	), 'json');