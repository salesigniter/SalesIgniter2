<?php
if (Session::exists('OverrideApproved') === false || Session::get('OverrideApproved') == 'false'){
	$response = array(
		'success' => false
	);

	if (Session::exists('OverrideApproved') === true){
		Session::remove('OverrideApproved');
	}
}
else {
	$LateFees = $appExtension->getExtension('lateFees');

	$Fee = Doctrine_Core::getTable('LateFees')->find((int)$_GET['fee_id']);
	$Fee->fee_status = $LateFees->voidStatusId();
	$Fee->date_paid = date(DATE_RSS);
	$Fee->save();

	$QnewTotal = Doctrine_Query::create()
		->select('SUM(fee_amount) as total')
		->from('LateFees')
		->where('customers_id = ?', $Fee->customers_id)
		->andWhere('fee_status = ?', $LateFees->openStatusId())
		->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

	$response = array(
		'success' => true,
		'feesTotal' => $currencies->format($QnewTotal[0]['total'])
	);

	Session::remove('OverrideApproved');
}

EventManager::attachActionResponse($response, 'json');