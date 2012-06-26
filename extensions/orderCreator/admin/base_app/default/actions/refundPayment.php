<?php
	$success = $Editor->PaymentManager->refundPayment($_POST['payment_module'], $_POST['payment_history_id'], $_POST['amount']);
	
	$html = '';
	if ($success === true){
		$Qhistory = Doctrine_Query::create()
		->from('OrdersPaymentsHistory')
		->where('orders_id = ?', $Editor->getSaleId())
		->orderBy('payment_history_id DESC')
		->limit(1)
		->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

		$paymentHistory = $Qhistory[0];
		
		if (array_key_exists('card_details', $paymentHistory) && is_null($paymentHistory['card_details']) === false){
			$cardInfo = unserialize(cc_decrypt($paymentHistory['card_details']));
			if (empty($cardInfo['cardNumber'])){
				unset($cardInfo);
			}
		}
		
		if ($paymentHistory['success'] == 0){
			$iconClass = 'ui-icon-closethick';
		}elseif ($paymentHistory['success'] == 1){
			$iconClass = 'ui-icon-check';
		}elseif ($paymentHistory['success'] == 2){
			$iconClass = 'ui-icon-alert';
		}
			
		$html = '<tr class="gridBodyRow" data-can_refund="false" data-can_void="false">' .
			'<td class="gridBodyRowColumn">' .
				tep_date_short($paymentHistory['date_added']) . 
			'</td>' . 
			'<td class="gridBodyRowColumn">' .
				$paymentHistory['payment_method'] . 
			'</td>' . 
			'<td class="gridBodyRowColumn">' .
				stripslashes($paymentHistory['gateway_message']) . 
			'</td>' . 
			'<td class="gridBodyRowColumn centerAlign">' .
				'<span class="ui-icon ' . $iconClass . '">' . 
			'</td>' . 
			'<td class="gridBodyRowColumn">' .
				$currencies->format($paymentHistory['payment_amount']) . 
			'</td>' . 
			'<td class="gridBodyRowColumn">' .
				(isset($cardInfo) && is_array($cardInfo) ? $cardInfo['cardNumber'] : '') . 
			'</td>' . 
			'<td class="gridBodyRowColumn">' .
				(isset($cardInfo) && is_array($cardInfo) ? $cardInfo['cardExpMonth'] . ' / ' . $cardInfo['cardExpYear'] : '') . 
			'</td>' . 
			'<td class="gridBodyRowColumnLast">' .
				(isset($cardInfo) && is_array($cardInfo) && isset($cardInfo['cardCvvNumber']) ? $cardInfo['cardCvvNumber'] : 'N/A') . 
			'</td>' . 
		'</tr>';
	}
	
	EventManager::attachActionResponse(array(
		'success' => $success,
		'tableRow' => $html
	), 'json');
?>