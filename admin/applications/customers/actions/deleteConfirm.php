<?php
$toDelete = explode(',', $_GET['customer_id']);
$Customers = Doctrine::getTable('Customers');
foreach($toDelete as $customerId){
	$Customer = $Customers->find($customerId);
	if ($Customer){
		$isAllowed = true;
		$errorMessages = array();
		EventManager::notify('AdminDeleteCustomerCheckAllowed', &$isAllowed, &$errorMessages, $Customer);
		EventManager::notify('CustomersBeforeDelete', $Customer);
		if ($isAllowed === true){
			$Customer->delete();
			$response = array(
				'success' => true
			);
		}
		else {
			$errorMsg = 'Customer account could not be deleted.' . "\n\n" .
				'The following errors were reported:' . "\n";
			foreach($errorMessages as $k => $v){
				$errorMsg .= $k + 1 . ': ' . $v . "\n";
			}

			$response = array(
				'success'      => false,
				'errorMessage' => $errorMsg
			);
		}
	}
}

EventManager::attachActionResponse($response, 'json');
