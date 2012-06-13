<?php
	$jsonData = array();

$QcustomerName = Doctrine_Query::create()
	->from('Customers c')
	->where('(' .
		'c.customers_firstname LIKE "' . $_GET['term'] . '%"' .
		' OR ' .
		'c.customers_lastname LIKE "' . $_GET['term'] . '%"' .
		' OR ' .
		'c.customers_email_address LIKE "' . $_GET['term'] . '%"' .
		' OR ' .
		'c.customers_number LIKE "' . $_GET['term'] . '%"' .
		' OR ' .
		'c.customers_telephone LIKE "' . $_GET['term'] . '%"' .
		') AND TRUE');

EventManager::notify('OrderCreatorFindCustomerQueryBeforeExecute', $QcustomerName);

$Result = $QcustomerName->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
if ($Result){
	$jsonData[] = array(
		'value' => 'no-select',
		'label' => '<span style="display:inline-block;width:150px;font-weight:bold;">Member Number</span>' .
			'<span style="display:inline-block;width:150px;font-weight:bold;">Telephone Number</span>' .
			'<span style="display:inline-block;width:150px;font-weight:bold;">First Name</span>' .
			'<span style="display:inline-block;width:150px;font-weight:bold;">Last Name</span>' .
			'<span style="display:inline-block;width:250px;font-weight:bold;">Email Address</span>'
	);
	foreach($Result as $cInfo){
		$msg = '';
		if ($cInfo['customers_account_frozen'] == '1'){
			$value = 'disabled';
			$msg = 'This customers account is frozen.';
		}/*else{
			$Qcheck = Doctrine_Query::create()
				->select('count(*) as total')
				->from('LateFees')
				->where('customers_id = ?', $cInfo['customers_id'])
				->andwhere('fee_status = ?', '0')
				->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
			if ($Qcheck[0]['total'] > 0){
				$value = 'disabled';
				$msg = 'This customer cannot place new orders until all late fees are paid.';
			}else{
				$value = $cInfo['customers_id'];
			}
		}*/
		$value = $cInfo['customers_id'];
		$jsonData[] = array(
			'value' => $value,
			'reason' => $msg,
			'label' => '<span class="' . ($value == 'disabled' ? 'ui-state-disabled' : '') . '" style="display:inline-block;width:150px;">' . $cInfo['customers_number'] . '</span>' .
				'<span class="' . ($value == 'disabled' ? 'ui-state-disabled' : '') . '" style="display:inline-block;width:150px;">' . $cInfo['customers_telephone'] . '</span>' .
				'<span class="' . ($value == 'disabled' ? 'ui-state-disabled' : '') . '" style="display:inline-block;width:150px;">' . $cInfo['customers_firstname'] . '</span>' .
				'<span class="' . ($value == 'disabled' ? 'ui-state-disabled' : '') . '" style="display:inline-block;width:150px;">' . $cInfo['customers_lastname'] . '</span>' .
				'<span class="' . ($value == 'disabled' ? 'ui-state-disabled' : '') . '" style="display:inline-block;width:250px;">' . $cInfo['customers_email_address'] . '</span>'
		);
	}
}

EventManager::attachActionResponse($jsonData, 'json');
?>