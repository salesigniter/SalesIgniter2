<?php
if ($success === true){
	$NewOrder->OrdersToStores->stores_id = $Editor->getData('store_id');
	if (!isset($_GET['oID'])){
		$NewOrder->Customers->CustomersToStores->stores_id = $Editor->getData('store_id');
		$NewOrder->save();
	}
}
?>