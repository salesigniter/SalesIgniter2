<?php
$Qcoupons = Doctrine_Query::create()
	->from('Coupons c')
	->leftJoin('c.CouponsDescription cd')
	->where('c.coupon_type != ?', 'G')
	->andWhere('cd.language_id = ?', Session::get('languages_id'))
	->orderBy('cd.coupon_name');

if (isset($_GET['status']) && $_GET['status'] != '*'){
	$Qcoupons->andWhere('c.coupon_active = ?', $_GET['status']);
}

$tableGrid = htmlBase::newGrid()
	->setMainDataKey('coupon_id')
	->usePagination(true)
	->setQuery($Qcoupons);

$tableGrid->addButtons(array(
	htmlBase::newElement('button')->usePreset('new')->addClass('newButton'),
	htmlBase::newElement('button')->usePreset('email')->addClass('emailButton')->disable(),
	htmlBase::newElement('button')->usePreset('edit')->addClass('editButton')->disable(),
	htmlBase::newElement('button')->usePreset('delete')->addClass('deleteButton')->disable(),
	htmlBase::newElement('button')->usePreset('report')->addClass('reportButton')->disable()
));

$tableGrid->addHeaderRow(array(
	'columns' => array(
		array('text' => sysLanguage::get('TABLE_HEADING_COUPON_NAME')),
		array('text' => sysLanguage::get('TABLE_HEADING_COUPON_AMOUNT')),
		array('text' => sysLanguage::get('TABLE_HEADING_COUPON_CODE')),
		array('text' => sysLanguage::get('TABLE_HEADING_COUPON_STATUS')),
		array('text' => sysLanguage::get('TABLE_HEADING_INFO'))
	)
));

$Coupons = &$tableGrid->getResults();
if ($Coupons){
	foreach($Coupons as $cInfo){
		$couponActive = $cInfo['coupon_active'];
		$couponId = $cInfo['coupon_id'];
		$couponCode = $cInfo['coupon_code'];
		$couponAmount = $cInfo['coupon_amount'];
		$couponMinOrder = $cInfo['coupon_minimum_order'];
		$couponType = $cInfo['coupon_type'];
		$couponStartDate = $cInfo['coupon_start_date'];
		$couponExpireDate = $cInfo['coupon_expire_date'];
		$usesPerUser = $cInfo['uses_per_user'];
		$usesPerCoupon = $cInfo['uses_per_coupon'];
		//$restrictToProducts = $cInfo['restrict_to_products'];
		//$restrictToCategories = $cInfo['restrict_to_categories'];
		$dateCreated = $cInfo['date_created'];
		$dateModified = $cInfo['date_modified'];

		$couponName = $cInfo['CouponsDescription'][sysLanguage::getId()]['coupon_name'];

		if ($couponType == 'P'){
			if ($couponAmount == round($couponAmount)){
				$cAmount = number_format($couponAmount);
			}
			else {
				$cAmount = number_format($couponAmount, 2);
			}
			$cAmount .= '%';
		}
		elseif ($couponType == 'S') {
			$cAmount = sysLanguage::get('TEXT_FREE_SHIPPING');
		}
		else {
			$cAmount = $currencies->format($couponAmount);
		}

		$arrowIcon = htmlBase::newElement('icon')->setType('info');

		$statusIcon = htmlBase::newElement('icon');
		if ($couponActive == 'Y'){
			$statusIcon->setType('circleCheck')->setTooltip('Click to disable')
				->setHref(itw_app_link('action=setflag&flag=N&cID=' . $couponId));
		}
		else {
			$statusIcon->setType('circleClose')->setTooltip('Click to enable')
				->setHref(itw_app_link('action=setflag&flag=Y&cID=' . $couponId));
		}

		$tableGrid->addBodyRow(array(
			'rowAttr' => array(
				'data-coupon_id' => $couponId
			),
			'columns' => array(
				array('text' => $couponName),
				array(
					'align' => 'center',
					'text'  => $cAmount
				),
				array(
					'align' => 'center',
					'text'  => $couponCode
				),
				array(
					'align' => 'center',
					'text'  => $statusIcon->draw()
				),
				array(
					'align' => 'center',
					'text'  => $arrowIcon->draw()
				)
			)
		));

		$tableGrid->addBodyRow(array(
			'addCls'  => 'gridInfoRow',
			'columns' => array(
				array(
					'colspan' => 6,
					'text'    => '<table cellpadding="1" cellspacing="0" border="0" width="75%">' .
						'<tr>' .
						'<td><b>' . sysLanguage::get('TEXT_INFO_COUPON_MIN_ORDER') . '</b></td>' .
						'<td> ' . $currencies->format($couponMinOrder) . '</td>' .
						'</tr>' .
						'<tr>' .
						'<td><b>' . sysLanguage::get('TEXT_INFO_COUPON_STARTDATE') . '</b></td>' .
						'<td>' . $couponStartDate->format(sysLanguage::getDateFormat('long')) . '</td>' .
						'<td><b>' . sysLanguage::get('TEXT_INFO_COUPON_USES_COUPON') . '</b></td>' .
						'<td>' . $usesPerCoupon . '</td>' .
						'</tr>' .
						'<tr>' .
						'<td><b>' . sysLanguage::get('TEXT_INFO_COUPON_FINISHDATE') . '</b></td>' .
						'<td>' . $couponExpireDate->format(sysLanguage::getDateFormat('long')) . '</td>' .
						'<td><b>' . sysLanguage::get('TEXT_INFO_COUPON_USES_USER') . '</b></td>' .
						'<td>' . $usesPerUser . '</td>' .
						'</tr>' .
						'<tr>' .
						'<td><b>' . sysLanguage::get('TEXT_INFO_DATE_CREATED') . '</b></td>' .
						'<td>' . $dateCreated->format(sysLanguage::getDateFormat('long')) . '</td>' .
						'</tr>' .
						'<tr>' .
						'<td><b>' . sysLanguage::get('TEXT_INFO_DATE_MODIFIED') . '</b></td>' .
						'<td>' . $dateModified->format(sysLanguage::getDateFormat('long')) . '</td>' .
						'</tr>' .
						'</table>'
				)
			)
		));
	}
}

echo $tableGrid->draw();
