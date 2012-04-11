<?php
$paymentHistoryTable = htmlBase::newElement('newGrid')
	->addClass('paymentsTable');

$paymentHistoryTable->addButtons(array(
	htmlBase::newElement('button')->addClass('paymentVoidButton')->usePreset('cancel')
		->setTooltip('Void This Payment')->setText('Void')->disable(),
	htmlBase::newElement('button')->addClass('paymentRefundButton')->usePreset('cancel')
		->setTooltip('Refund This Payment')->setText('Refund')->disable()
));

$paymentHistoryTable->addHeaderRow(array(
	'columns' => array(
		array('text' => 'Date Added'),
		array('text' => 'Payment Method'),
		array('text' => 'Message'),
		array('text' => 'Status'),
		array('text' => 'Amount Paid'),
		array('text' => 'Card Number'),
		array('text' => 'Exp Date'),
		array('text' => 'CVV Code')
	)
));

$PaymentMethodDrop = htmlBase::newElement('selectbox')
	->setName('payment_method')
	->addOption('', sysLanguage::get('TEXT_PLEASE_SELECT'));

foreach(OrderPaymentModules::getModules() as $Module){
	if ($Module->hasFormUrl() === false){
		$PaymentMethodDrop->addOption($Module->getCode(), $Module->getTitle());
	}
}

$paymentHistoryTable->addBeforeButtonBar('<fieldset>
			<legend>New Payment</legend>
			' . $PaymentMethodDrop->draw() . '
			<div id="paymentFields"></div>
			<div id="paymentQueue" style="display:none;">
				<div><button class="addPaymentQueueButton" type="button">Add To Payment Process Queue</button> - Will Process On Save/Update</div>
				<table cellpadding="2" cellspacing="0" width="100%">
					<thead>
						<tr>
							<th align="center">Payment Process Queue</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>
								<table class="processQueue" cellpadding="2" cellspacing="0" width="100%">
									<thead>
										<tr>
											<th>Payment Method</th>
											<th>Payment Amount</th>
											<th>Card Type</th>
											<th>Card Number</th>
											<th>Card Expiration</th>
											<th>Card Cvv</th>
											<th>Comments</th>
										</tr>
									</thead>
									<tbody></tbody>
								</table>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</fieldset>');

foreach($Editor->PaymentManager->getHistory() as $paymentHistory){
	$cardInfo = false;

	if (array_key_exists('card_details', $paymentHistory) && is_null($paymentHistory['card_details']) === false){
		$cardInfo = unserialize(cc_decrypt($paymentHistory['card_details']));
		if (empty($cardInfo['cardNumber'])){
			$cardInfo = false;
		}
	}

	if ($paymentHistory['success'] == 0){
		$iconClass = 'ui-icon-closethick';
	}
	elseif ($paymentHistory['success'] == 1) {
		$iconClass = 'ui-icon-check';
	}
	elseif ($paymentHistory['success'] == 2) {
		$iconClass = 'ui-icon-alert';
	}

	$paymentHistoryTable->addBodyRow(array(
		'rowAttr' => array(
			'data-can_refund'         => ($paymentHistory['is_refund'] == 0 ? 'true' : 'false'),
			'data-can_void'           => 'false',
			'data-payment_history_id' => $paymentHistory['payment_history_id'],
			'data-payment_module'     => $paymentHistory['payment_module']
		),
		'columns' => array(
			array('text' => $paymentHistory['date_added']->format(sysLanguage::getDateFormat('short'))),
			array('text' => $paymentHistory['payment_method']),
			array('text' => stripslashes($paymentHistory['gateway_message'])),
			array(
				'align' => 'center',
				'text'  => '<span class="ui-icon ' . $iconClass . '">'
			),
			array('text' => $currencies->format($paymentHistory['payment_amount'])),
			array('text' => (is_array($cardInfo) ? $cardInfo['cardNumber'] : '')),
			array('text' => (is_array($cardInfo) ? $cardInfo['cardExpMonth'] . ' / ' . $cardInfo['cardExpYear'] : '')),
			array('text' => (is_array($cardInfo) && isset($cardInfo['cardCvvNumber']) ? $cardInfo['cardCvvNumber'] : 'N/A'))
		)
	));
}

echo $paymentHistoryTable->draw();