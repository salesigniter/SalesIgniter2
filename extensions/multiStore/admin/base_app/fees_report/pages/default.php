<?php
$ReportTable = htmlBase::newElement('table')
	->setCellPadding(2)
	->setCellSpacing(0)
	->css('width', '100%');

$MultiStores = $appExtension->getExtension('multiStore');
foreach($MultiStores->getStoresArray() as $sInfo){
	$QOrderTotal = Doctrine_Query::create()
		->select('SUM(ot.value) as total')
		->from('Orders o')
		->leftJoin('o.OrdersTotal ot')
		->leftJoin('o.OrdersToStores o2s')
		->where('o2s.stores_id = ?', $sInfo['stores_id'])
		->andWhere('ot.module_type = ?', 'total');

	if (isset($_GET['date_from']) && isset($_GET['date_to'])){
		$QOrderTotal->andWhere('o.date_purchased >= ?', $_GET['date_from'])
			->andWhere('o.date_purchased <= ?', $_GET['date_to']);
	}

	$OrderTotal = $QOrderTotal->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

	$Total = $OrderTotal[0]['total'];

	$QBilled = Doctrine_Query::create()
		->select(
		'SUM(fee_royalty + fee_royalty_discount) as Royalty, ' .
			'SUM(fee_management + fee_management_discount) as Management, ' .
			'SUM(fee_marketing + fee_marketing_discount) as Marketing, ' .
			'SUM(fee_labor + fee_labor_discount) as Labor, ' .
			'SUM(fee_parts + fee_parts_discount) as Parts'
		)
		->from('StoresFeesInvoices')
		->where('stores_id = ?', $sInfo['stores_id'])
		->andWhere('paid = ?', '0');

	if (isset($_GET['date_from']) && isset($_GET['date_to'])){
		$QBilled->andWhere('date_added >= ?', strtotime($_GET['date_from']))
			->andWhere('date_added <= ?', strtotime($_GET['date_to']));
	}

	$Billed = $QBilled->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

	$RoyaltyBilled = $Billed[0]['Royalty'];
	$ManagementBilled = $Billed[0]['Management'];
	$MarketingBilled = $Billed[0]['Marketing'];
	$LaborBilled = $Billed[0]['Labor'];
	$PartsBilled = $Billed[0]['Parts'];

	$QPaid = Doctrine_Query::create()
		->select(
		'SUM(fee_royalty + fee_royalty_discount) as Royalty, ' .
			'SUM(fee_management + fee_management_discount) as Management, ' .
			'SUM(fee_marketing + fee_marketing_discount) as Marketing, ' .
			'SUM(fee_labor + fee_labor_discount) as Labor, ' .
			'SUM(fee_parts + fee_parts_discount) as Parts'
		)
		->from('StoresFeesInvoices')
		->where('stores_id = ?', $sInfo['stores_id'])
		->andWhere('paid = ?', '1');

	if (isset($_GET['date_from']) && isset($_GET['date_to'])){
		$QPaid->andWhere('date_added >= ?', strtotime($_GET['date_from']))
			->andWhere('date_added <= ?', strtotime($_GET['date_to']));
	}

	$Paid = $QPaid->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

	$RoyaltyPaid = $Paid[0]['Royalty'];
	$ManagementPaid = $Paid[0]['Management'];
	$MarketingPaid = $Paid[0]['Marketing'];
	$LaborPaid = $Paid[0]['Labor'];
	$PartsPaid = $Paid[0]['Parts'];

	$StoreInfoTable = htmlBase::newElement('table')
		->setCellPadding(2)
		->setCellSpacing(0)
		->css('width', '100%')
		->addHeaderRow(array(
			'columns' => array(
				array(
					'colspan' => 2,
					'addCls' => 'ui-widget-header ui-state-hover',
					'text' => '<b>Store Info</b>'
				)
			)
		));

	$FeesDueTable = htmlBase::newElement('table')
		->setCellPadding(2)
		->setCellSpacing(0)
		->css('width', '100%')
		->addHeaderRow(array(
			'columns' => array(
				array(
					'colspan' => 2,
					'addCls' => 'ui-widget-header ui-state-hover',
					'text' => '<b>Fees Not Billed</b>'
				)
			)
		));

	$FeesBilledTable = htmlBase::newElement('table')
		->setCellPadding(2)
		->setCellSpacing(0)
		->css('width', '100%')
		->addHeaderRow(array(
			'columns' => array(
				array(
					'colspan' => 2,
					'addCls' => 'ui-widget-header ui-state-hover',
					'text' => '<b>Fees Billed</b>'
				)
			)
		));

	$FeesPaidTable = htmlBase::newElement('table')
		->setCellPadding(2)
		->setCellSpacing(0)
		->css('width', '100%')
		->addHeaderRow(array(
			'columns' => array(
				array(
					'colspan' => 2,
					'addCls' => 'ui-widget-header ui-state-hover',
					'text' => '<b>Fees Paid</b>'
				)
			)
		));

	$StoreInfoTable->addBodyRow(array(
			'columns' => array(
				array('css' => array('width' => '100px'), 'text' => 'ID: '),
				array('text' => $sInfo['stores_id'])
			)
		));

	$StoreInfoTable->addBodyRow(array(
			'columns' => array(
				array('text' => 'VAT Number: '),
				array('text' => $sInfo['stores_vat_number'])
			)
		));

	$StoreInfoTable->addBodyRow(array(
			'columns' => array(
				array('text' => 'Reg Number: '),
				array('text' => $sInfo['stores_reg_number'])
			)
		));

	$StoreInfoTable->addBodyRow(array(
			'columns' => array(
				array('text' => 'Address: '),
				array('text' => $sInfo['stores_street_address'])
			)
		));

	$StoreInfoTable->addBodyRow(array(
			'columns' => array(
				array('text' => 'Total Sales: '),
				array('text' => $currencies->format($Total))
			)
		));

	$RoyaltyFeePercent = (float)$sInfo['StoresFees']['fee_royalty'];
	$ManagementFeePercent = (float)$sInfo['StoresFees']['fee_management'];
	$MarketingFeePercent = (float)$sInfo['StoresFees']['fee_marketing'];
	$LaborFeePercent = (float)$sInfo['StoresFees']['fee_labor'];
	$PartsFeePercent = (float)$sInfo['StoresFees']['fee_parts'];

	$RoyaltyFeesOwed = ($Total * $RoyaltyFeePercent);
	if ($RoyaltyFeesOwed > 0){
		$RoyaltyFeesOwed -= $RoyaltyBilled;
		$RoyaltyFeesOwed -= $RoyaltyPaid;
	}

	$ManagementFeesOwed = ($Total * $ManagementFeePercent);
	if ($ManagementFeesOwed > 0){
		$ManagementFeesOwed -= $ManagementBilled;
		$ManagementFeesOwed -= $ManagementPaid;
	}

	$MarketingFeesOwed = ($Total * $MarketingFeePercent);
	if ($MarketingFeesOwed > 0){
		$MarketingFeesOwed -= $MarketingBilled;
		$MarketingFeesOwed -= $MarketingPaid;
	}

	$LaborFeesOwed = ($Total * $LaborFeePercent);
	if ($LaborFeesOwed > 0){
		$LaborFeesOwed -= $LaborBilled;
		$LaborFeesOwed -= $LaborPaid;
	}

	$PartsFeesOwed = ($Total * $PartsFeePercent);
	if ($PartsFeesOwed > 0){
		$PartsFeesOwed -= $PartsBilled;
		$PartsFeesOwed -= $PartsPaid;
	}

	$FeesDueTable->addBodyRow(array(
			'columns' => array(
				array('css' => array('width' => '150px'), 'text' => 'Royalty (' . $RoyaltyFeePercent . '%): '),
				array(
					'addCls' => 'feeOwedRoyalty',
					'attr' => array('data-float_val' => $RoyaltyFeesOwed),
					'text' => $currencies->format($RoyaltyFeesOwed)
				)
			)
		));

	$FeesDueTable->addBodyRow(array(
			'columns' => array(
				array('text' => 'Management (' . $ManagementFeePercent . '%): '),
				array(
					'addCls' => 'feeOwedManagement',
					'attr' => array('data-float_val' => $ManagementFeesOwed),
					'text' => $currencies->format($ManagementFeesOwed)
				)
			)
		));

	$FeesDueTable->addBodyRow(array(
			'columns' => array(
				array('text' => 'Marketing (' . $MarketingFeePercent . '%): '),
				array(
					'addCls' => 'feeOwedMarketing',
					'attr' => array('data-float_val' => $MarketingFeesOwed),
					'text' => $currencies->format($MarketingFeesOwed)
				)
			)
		));

	$FeesDueTable->addBodyRow(array(
			'columns' => array(
				array('text' => 'Labor (' . $LaborFeePercent . '%): '),
				array(
					'addCls' => 'feeOwedLabor',
					'attr' => array('data-float_val' => $LaborFeesOwed),
					'text' => $currencies->format($LaborFeesOwed)
				)
			)
		));

	$FeesDueTable->addBodyRow(array(
			'columns' => array(
				array('text' => 'Parts (' . $PartsFeePercent . '%): '),
				array(
					'addCls' => 'feeOwedParts',
					'attr' => array('data-float_val' => $PartsFeesOwed),
					'text' => $currencies->format($PartsFeesOwed)
				)
			)
		));

	$FeesBilledTable->addBodyRow(array(
			'columns' => array(
				array('css' => array('width' => '150px'), 'text' => 'Royalty: '),
				array('text' => $currencies->format($RoyaltyBilled))
			)
		));

	$FeesBilledTable->addBodyRow(array(
			'columns' => array(
				array('text' => 'Management: '),
				array('text' => $currencies->format($ManagementBilled))
			)
		));

	$FeesBilledTable->addBodyRow(array(
			'columns' => array(
				array('text' => 'Marketing: '),
				array('text' => $currencies->format($MarketingBilled))
			)
		));

	$FeesBilledTable->addBodyRow(array(
			'columns' => array(
				array('text' => 'Labor: '),
				array('text' => $currencies->format($LaborBilled))
			)
		));

	$FeesBilledTable->addBodyRow(array(
			'columns' => array(
				array('text' => 'Parts: '),
				array('text' => $currencies->format($PartsBilled))
			)
		));

	$FeesPaidTable->addBodyRow(array(
			'columns' => array(
				array('css' => array('width' => '150px'), 'text' => 'Royalty: '),
				array('text' => $currencies->format($RoyaltyPaid))
			)
		));

	$FeesPaidTable->addBodyRow(array(
			'columns' => array(
				array('text' => 'Management: '),
				array('text' => $currencies->format($ManagementPaid))
			)
		));

	$FeesPaidTable->addBodyRow(array(
			'columns' => array(
				array('text' => 'Marketing: '),
				array('text' => $currencies->format($MarketingPaid))
			)
		));

	$FeesPaidTable->addBodyRow(array(
			'columns' => array(
				array('text' => 'Labor: '),
				array('text' => $currencies->format($LaborPaid))
			)
		));

	$FeesPaidTable->addBodyRow(array(
			'columns' => array(
				array('text' => 'Parts: '),
				array('text' => $currencies->format($PartsPaid))
			)
		));

	if(Session::get('login_groups_id') != 1){
		$AdminGroups = Doctrine_Core::getTable('AdminGroups')->find(Session::get('login_groups_id'));
		$varExtra = unserialize($AdminGroups->extra_data);
	}else{
		$varExtra['buttonsMultistoreEnabled']['hasCreateInvoice'] = true;
		$varExtra['buttonsMultistoreEnabled']['hasPayInvoice'] = true;
	}

	$GenerateInvoice = htmlBase::newElement('button')
		->attr('data-store_id', $sInfo['stores_id'])
		->addClass('genInvoice')
		->setText('Create Invoice');


	$AddPayment = htmlBase::newElement('button')
		->attr('data-store_id', $sInfo['stores_id'])
		->addClass('addPayment')
		->setText('Pay Invoice')
		->css(array(
			'margin-left' => '3px'
		));

	$ReportTable->addBodyRow(array(
			'columns' => array(
				array(
					'colspan' => 4,
					'addCls' => 'ui-widget-header',
					'css' => array(
						'vertical-align' => 'middle',
						'line-height' => '15pt'
					),
					'text' => '<div style="float:right;font-size:7pt;">' .
						((($varExtra['buttonsMultistoreEnabled']['hasCreateInvoice']))?$GenerateInvoice->draw():'') .
						((($varExtra['buttonsMultistoreEnabled']['hasPayInvoice']))?$AddPayment->draw():'') .
						'</div>' .
						'<b>' . $sInfo['stores_name'] . '</b>'
				)
			)
		));
	$ReportTable->addBodyRow(array(
			'columns' => array(
				array(
					'addCls' => 'ui-widget-content',
					'css' => array('vertical-align' => 'top', 'width' => '25%'),
					'text' => $StoreInfoTable
				),
				array(
					'addCls' => 'ui-widget-content',
					'css' => array('vertical-align' => 'top', 'width' => '25%'),
					'text' => $FeesDueTable
				),
				array(
					'addCls' => 'ui-widget-content',
					'css' => array('vertical-align' => 'top', 'width' => '25%'),
					'text' => $FeesBilledTable
				),
				array(
					'addCls' => 'ui-widget-content',
					'css' => array('vertical-align' => 'top', 'width' => '25%'),
					'text' => $FeesPaidTable
				)
			)
		));
	$ReportTable->addBodyRow(array('columns' => array(array('text' => '&nbsp;'))));
}
?>
<div class="pageHeading"><?php
	echo sysLanguage::get('HEADING_TITLE_SALES_REPORT');
	?></div>
<br />
<div>
	<form action="<?php echo itw_app_link('appExt=multiStore', 'fees_report', 'default');?>" method="get">
		<table>
			<tr>
				<td valign="top" id="dayFilter" class="reportTypeFilter">
					<table width="550px">
						<tr>
							<td style="text-align: center;"><b><u><?php echo sysLanguage::get('TEXT_ENTRY_DATE_FROM');?></u></b><div id="date_from"></div><input type="hidden" name="date_from"></td>
							<td style="text-align: center;"><b><u><?php echo sysLanguage::get('TEXT_ENTRY_DATE_TO');?></u></b><div id="date_to"></div><input type="hidden" name="date_to"></td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td><?php
					echo htmlBase::newElement('button')
					->setType('submit')
					->setText(sysLanguage::get('TEXT_BUTTON_GENERATE'))
					->draw();

					echo htmlBase::newElement('button')
						->setText(sysLanguage::get('TEXT_BUTTON_GENERATE_CSV'))
					    ->setHref(itw_app_link('action=csvExport&appExt=multiStore', 'fees_report', 'default'))
						->draw();
				?></td>
			</tr>
		</table>
	</form>
</div>
<div style="width:100%;float:left;">
	<div class="ui-widget ui-widget-content ui-corner-all" style="width:99%;margin-right:5px;margin-left:5px;">
		<div style="width:99%;margin:5px;" class="reportHolder"><?php
			echo $ReportTable->draw();
		?></div>
	</div>
</div>