<?php
$QGiftCertificates = Doctrine_Query::create()
	->from('GiftCertificates gc')
	->leftJoin('gc.Descriptions gcd')
	->leftJoin('gc.PurchaseTypes gcpt')
	->where('gcd.language_id = ?', Session::get('languages_id'))
	->orderBy('gcd.gift_certificates_name');

if (isset($_GET['status']) && $_GET['status'] != '*'){
	$QGiftCertificates->andWhere('gc.gift_certificates_status = ?', $_GET['status']);
}

$tableGrid = htmlBase::newElement('newGrid')
	->setMainDataKey('gift_certificates_id')
	->useSearching(true)
	->useSorting(true)
	->usePagination(true)
	->setPageLimit((isset($_GET['limit']) ? (int)$_GET['limit'] : 25))
	->setCurrentPage((isset($_GET['page']) ? (int)$_GET['page'] : 1))
	->setQuery($QGiftCertificates);

$tableGrid->addButtons(array(
	htmlBase::newElement('button')->usePreset('new')->addClass('newButton'),
	htmlBase::newElement('button')->usePreset('edit')->addClass('editButton')->disable(),
	htmlBase::newElement('button')->usePreset('delete')->addClass('deleteButton')->disable(),
	htmlBase::newElement('button')->usePreset('email')->addClass('emailButton')->disable()
));

$StatusSearch = htmlBase::newElement('selectbox')
	->addOption('', 'All Statuses')
	->addOption('Y', sysLanguage::get('TEXT_GIFT_CERTIFICATES_ACTIVE'))
	->addOption('N',sysLanguage::get('TEXT_GIFT_CERTIFICATES_INACTIVE'))
	->addOption('*',sysLanguage::get('TEXT_GIFT_CERTIFICATES_ALL'));

$tableGrid->addHeaderRow(array(
	'columns' => array(
		array(
			'text'      => sysLanguage::get('TABLE_HEADING_GIFT_CERTIFICATES_NAME'),
			'useSort'   => true,
			'sortKey'   => 'gcd.gift_certificates_name',
			'useSearch' => true,
			'searchObj' => GridSearchObj::Like()
				->useFieldObj(htmlBase::newElement('input')->setName('search_gift_certificates_name'))
				->setDatabaseColumn('gcd.gift_certificates_name')
		),
		array(
			'text'      => sysLanguage::get('TABLE_HEADING_GIFT_CERTIFICATES_PRICE'),
			'useSort'   => true,
			'sortKey'   => 'gc.gift_certificates_price',
			'useSearch' => false,
			'searchObj' => GridSearchObj::Equal()
				->useFieldObj(htmlBase::newElement('input')->setName('search_gift_certificates_price'))
				->setDatabaseColumn('gcd.gift_certificates_price')
		),
		array(
			'text'      => sysLanguage::get('TABLE_HEADING_GIFT_CERTIFICATES_GLOBAL_VALUE'),
			'useSort'   => true,
			'sortKey'   => 'gc.gift_certificates_code',
			'useSearch' => false,
			'searchObj' => GridSearchObj::Equal()
				->useFieldObj(htmlBase::newElement('input')->setName('search_gift_certificates_price'))
				->setDatabaseColumn('gcd.gift_certificates_code')
		),
		array(
			'text'      => sysLanguage::get('TABLE_HEADING_GIFT_CERTIFICATES_STATUS'),
			'useSort'   => true,
			'sortKey'   => 'gc.gift_certificates_status',
			'useSearch' => true,
			'searchObj' => GridSearchObj::Equal()
				->useFieldObj($StatusSearch->setName('search_gift_certificates_status'))
				->setDatabaseColumn('gcd.gift_certificates_status')
		),
		array('text' => '')
	)
));

$GiftCertificates = &$tableGrid->getResults();
if ($GiftCertificates){
	foreach ($GiftCertificates as $gcInfo){
		$giftCertificatesActive = $gcInfo['gift_certificates_status'];
		$giftCertificatesId = $gcInfo['gift_certificates_id'];
		$valuesTable = htmlBase::newElement('table')
			->setCellPadding(2)
			->setCellSpacing(0)
			->css(array('width' => '100%'));
		foreach ($gcInfo['PurchaseTypes'] as $giftCertificatesPurchaseType){
			$valuesTable->addBodyRow(array(
				'columns' => array(
					array('text' => ucwords($giftCertificatesPurchaseType['purchase_type'])),
					array('text' => $currencies->format($giftCertificatesPurchaseType['gift_certificates_value']))
				)
			));
		}
		$giftCertificatesPrice = $gcInfo['gift_certificates_price'];
		$giftCertificatesName = $gcInfo['Descriptions'][Session::get('languages_id')]['gift_certificates_name'];

		$arrowIcon = htmlBase::newElement('icon')->setType('info');

		$statusIcon = htmlBase::newElement('icon');
		if ($giftCertificatesActive == 'Y'){
			$statusIcon->setType('circleCheck')
				->setTooltip('Click to disable')
				->setHref(itw_app_link('appExt=giftCertificates&action=setflag&flag=N&gcID=' . $giftCertificatesId));
		} else{
			$statusIcon->setType('circleClose')
				->setTooltip('Click to enable')
				->setHref(itw_app_link('appExt=giftCertificates&action=setflag&flag=Y&gcID=' . $giftCertificatesId));
		}

		$tableGrid->addBodyRow(array(
			'rowAttr' => array(
				'data-gift_certificates_id' => $giftCertificatesId
			),
			'columns' => array(
				array('text' => $giftCertificatesName),
				array(
					'align' => 'center',
					'text'  => $giftCertificatesPrice
				),
				array(
					'align' => 'center',
					'text'  => $valuesTable->draw()
				),
				array(
					'align' => 'center',
					'text'  => $statusIcon->draw()
				),
				array('text' => '')
			)
		));
	}
}
?>
<div class="ui-widget ui-widget-content ui-corner-all" style="margin-right:5px;margin-left:5px;">
	<div style="margin:5px;"><?php echo $tableGrid->draw();?></div>
</div>