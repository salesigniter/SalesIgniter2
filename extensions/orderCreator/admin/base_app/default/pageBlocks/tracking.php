<?php
$tracking = array(
	array(
		'id'      => 'usps',
		'heading' => sysLanguage::get('TABLE_HEADING_USPS_TRACKING'),
		'link'    => 'http://trkcnfrm1.smi.usps.com/PTSInternetWeb/InterLabelInquiry.do?origTrackNum='
	),
	array(
		'id'      => 'ups',
		'heading' => sysLanguage::get('TABLE_HEADING_UPS_TRACKING'),
		'link'    => 'http://wwwapps.ups.com/etracking/tracking.cgi?InquiryNumber2=&InquiryNumber3=&InquiryNumber4=&InquiryNumber5=&TypeOfInquiryNumber=T&UPS_HTML_Version=3.0&IATA=us&Lang=en&submit=Track+Package&InquiryNumber1='
	),
	array(
		'id'      => 'fedex',
		'heading' => sysLanguage::get('TABLE_HEADING_FEDEX_TRACKING'),
		'link'    => 'http://www.fedex.com/Tracking?action=track&language=english&cntry_code=us&tracknumbers='
	),
	array(
		'id'      => 'dhl',
		'heading' => sysLanguage::get('TABLE_HEADING_DHL_TRACKING'),
		'link'    => 'http://track.dhl-usa.com/atrknav.asp?action=track&language=english&cntry_code=us&ShipmentNumber='
	)
);

$trackingTable = htmlBase::newElement('table')
	->setCellPadding(3)
	->setCellSpacing(0);

$TrackingNumbers = $Editor->InfoManager->getInfo('tracking');
foreach($tracking as $tracker){
	$inputField = htmlBase::newInput()
		->isMultiple(true)
		->setValue(isset($TrackingNumbers[$tracker['id']]) ? $TrackingNumbers[$tracker['id']] : '')
		->setName($tracker['id'] . '_tracking_number')
		->setLabel($tracker['heading'])
		->setLabelPosition('top')
		->attr(
		array(
			'size'      => 40,
			'maxlength' => 40
		));

	/*$trackButton = htmlBase::newElement('button')
		->setHref($tracker['link'], false, '_blank')
		->setText('Track');
	if (array_key_exists($fieldName, $orderInfo)){
		$inputField->setValue($orderInfo[$fieldName]);
		$trackButton->attr('data-track_number', $orderInfo[$fieldName]);
	}
	else {
		$trackButton->disable();
	}*/

	/*$bodyCols[] = array(
		'text' => $trackButton->draw()
	);*/

	$trackingTable->addBodyRow(array(
		'columns' => array(
			array('text' => $inputField->draw())
		)
	));
}

echo $trackingTable->draw();
