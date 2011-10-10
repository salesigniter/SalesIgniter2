<?php
	$Product->addon_products = '';
	if (isset($_POST['addon_products'])){
		$Product->addon_products = implode(',', $_POST['addon_products']);
	}
	
	$Product->save();
?>