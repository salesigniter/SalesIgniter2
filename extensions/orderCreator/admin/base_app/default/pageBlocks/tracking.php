<?php
$tracking = array(
	array(
		'heading' => sysLanguage::get('TABLE_HEADING_USPS_TRACKING'),
		'link'    => 'http://trkcnfrm1.smi.usps.com/PTSInternetWeb/InterLabelInquiry.do?origTrackNum=',
		'data'    => array('usps_track_num', 'usps_track_num2')
	),
	array(
		'heading' => sysLanguage::get('TABLE_HEADING_UPS_TRACKING'),
		'link'    => 'http://wwwapps.ups.com/etracking/tracking.cgi?InquiryNumber2=&InquiryNumber3=&InquiryNumber4=&InquiryNumber5=&TypeOfInquiryNumber=T&UPS_HTML_Version=3.0&IATA=us&Lang=en&submit=Track+Package&InquiryNumber1=',
		'data'    => array('ups_track_num', 'ups_track_num2')
	),
	array(
		'heading' => sysLanguage::get('TABLE_HEADING_FEDEX_TRACKING'),
		'link'    => 'http://www.fedex.com/Tracking?action=track&language=english&cntry_code=us&tracknumbers=',
		'data'    => array('fedex_track_num', 'fedex_track_num2')
	),
	array(
		'heading' => sysLanguage::get('TABLE_HEADING_DHL_TRACKING'),
		'link'    => 'http://track.dhl-usa.com/atrknav.asp?action=track&language=english&cntry_code=us&ShipmentNumber=',
		'data'    => array('dhl_track_num', 'dhl_track_num2')
	)
);

$trackingTable = htmlBase::newElement('table')->setCellPadding(3)->setCellSpacing(0);
$orderInfo = $Editor->getOrderInfo();

foreach($tracking as $tracker){
	$bodyCols = array(
		array('text' => '<b>' . $tracker['heading'] . ':</b> ')
	);
	foreach($tracker['data'] as $fieldName){
		$inputField = htmlBase::newElement('input')
			->setName($fieldName)
			->attr(array(
			'size'      => 40,
			'maxlength' => 40
		));

		$trackButton = htmlBase::newElement('button')->setHref($tracker['link'], false, '_blank')->setText('Track');
		if (array_key_exists($fieldName, $orderInfo)){
			$inputField->setValue($orderInfo[$fieldName]);
			$trackButton->attr('data-track_number', $orderInfo[$fieldName]);
		}
		else {
			$trackButton->disable();
		}

		$bodyCols[] = array(
			'text' => $inputField->draw()
		);
		$bodyCols[] = array(
			'text' => $trackButton->draw()
		);
	}
	$trackingTable->addBodyRow(array(
		'columns' => $bodyCols
	));
}

echo $trackingTable->draw();
