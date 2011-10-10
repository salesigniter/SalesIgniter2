<?php
$CartProduct = $ShoppingCart->getProduct($_POST['id']);
if ($CartProduct){
	$CartProduct->updateFromPost();
}

 	ob_start();
	require(sysConfig::getDirFsCatalog() . 'applications/checkout/pages/cart.php');
	$pageHtml = ob_get_contents();
	ob_end_clean();

	EventManager::attachActionResponse(array(
		'success' => true,
		'pageHtml' => $pageHtml
	), 'json');

?>