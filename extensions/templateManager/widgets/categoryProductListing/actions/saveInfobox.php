<?php
if (isset($_POST['category_id'])){
	$WidgetProperties['category_id'] = $_POST['category_id'];
}
if (isset($_POST['max_products'])){
	$WidgetProperties['max_products'] = $_POST['max_products'];
}
if (isset($_POST['when_max_products'])){
	$WidgetProperties['when_max_products'] = $_POST['when_max_products'];
}
?>