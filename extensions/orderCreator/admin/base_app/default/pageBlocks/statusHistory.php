<?php
if (isset($_GET['oID'])){
	echo '<br /><h2><u>' . sysLanguage::get('HEADING_STATUS_HISTORY') . '</u></h2>';

	$historyTable = htmlBase::newElement('newGrid');

	$historyTable->addHeaderRow(array(
		'columns' => array(
			array('text' => sysLanguage::get('TABLE_HEADING_DATE_ADDED')),
			array('text' => sysLanguage::get('TABLE_HEADING_CUSTOMER_NOTIFIED')),
			array('text' => sysLanguage::get('TABLE_HEADING_STATUS')),
			array('text' => sysLanguage::get('TABLE_HEADING_COMMENTS'))
		)
	));

	if ($Editor->hasStatusHistory()){
		foreach($Editor->getStatusHistory() as $history){
			if ($history['customer_notified'] == '1'){
				$icon = '<img src="images/icons/tick.gif"/>';
			}
			else {
				$icon = '<img src="images/icons/cross.gif"/>';
			}

			$historyTable->addBodyRow(array(
				'columns' => array(
					array(
						'align' => 'center',
						'text'  => $history['date_added']->format(sysLanguage::getDateFormat('long'))
					),
					array(
						'align' => 'center',
						'text'  => $icon
					),
					array('text' => $history['OrdersStatus']['OrdersStatusDescription'][Session::get('languages_id')]['orders_status_name']),
					array('text' => nl2br(stripslashes($history['comments']))),
				)
			));
		}
	}
	else {
		$historyTable->addBodyRow(array(
			'columns' => array(
				array(
					'align'   => 'center',
					'colspan' => 5,
					'text'    => sysLanguage::get('TEXT_NO_ORDER_HISTORY')
				)
			)
		));
	}
	echo $historyTable->draw();
}
