<?php
//$_POST['purchase_type'] = $_POST['type'];
$ShoppingCart->remove($_POST['pID']);

    ob_start();
	require(sysConfig::getDirFsCatalog() . 'applications/checkout/pages/cart.php');
	$pageHtml = ob_get_contents();
	ob_end_clean();

 	if ($ShoppingCart->countContents() == 0){
		 $empty = true;
	 }else{
		 $empty = false;
	 }

	EventManager::attachActionResponse(array(
		'success' => true,
		'empty'	=> $empty,
		'pageHtml' => $pageHtml
	), 'json');
?>