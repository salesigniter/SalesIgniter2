<?php

if(isset($_POST['pickupRequest'])){

		$RentedProductToPR = Doctrine_Core::getTable('CustomersToPickupRequests')->findOneByCustomersId($userAccount->getCustomerId());
		if(!$RentedProductToPR){
			$RentedProductToPR = new CustomersToPickupRequests;
		}
		$RentedProductToPR->customers_id = $userAccount->getCustomerId();
		$RentedProductToPR->pickup_requests_id = $_POST['pickupRequest'];
		$RentedProductToPR->save();

}

EventManager::attachActionResponse(itw_app_link(tep_get_all_get_params(array('action'))), 'redirect');
?>