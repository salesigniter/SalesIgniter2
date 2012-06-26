<?php
$tableGrid = htmlBase::newElement('newGrid');

$gridButtons = array(
	htmlBase::newElement('button')->usePreset('details')->addClass('detailsButton')->disable(),
	//htmlBase::newElement('button')->setText('Delete')->addClass('deleteButton')->disable(),
	//htmlBase::newElement('button')->setText('Cancel')->addClass('cancelButton')->disable(),
	//htmlBase::newElement('button')->setText('Invoice')->addClass('invoiceButton')->disable(),
	//htmlBase::newElement('button')->setText('Packing Slip')->addClass('packingSlipButton')->disable()
);

$tableGrid->addButtons($gridButtons);

$gridHeaderColumns = array(
	//array('text' => '&nbsp;'),
	array('text' => 'ID'),
	array('text' => sysLanguage::get('TABLE_HEADING_DATE_PURCHASED')),
	array('text' => sysLanguage::get('TABLE_HEADING_STATUS')),
	array('text' => sysLanguage::get('TABLE_HEADING_ORDER_TOTAL'))
);

$tableGrid->addHeaderRow(array(
	'columns' => $gridHeaderColumns
));

$noOrders = false;
if ($Customer->Sales && $Customer->Sales->count() > 0){
	foreach($Customer->Sales as $sInfo){
		$Sale = AccountsReceivable::getSale($sInfo->sale_module, $sInfo->sale_id);

		$gridBodyColumns = array(
			array('text' => $Sale->getSaleId()),
			array(
				'text'  => $Sale->getDateAdded()->format(sysLanguage::getDateFormat('short')),
				'align' => 'center'
			),
			array(
				'text'  => $Sale->getStatusName(),
				'align' => 'center'
			),
			array(
				'text'  => $Sale->getTotal(true),
				'align' => 'right'
			)
		);

		$tableGrid->addBodyRow(array(
			'rowAttr' => array(
				'data-sale_id' => $Sale->getSaleId()
			),
			'columns' => $gridBodyColumns
		));

		$tableGrid->addBodyRow(array(
			'addCls'  => 'gridInfoRow',
			'columns' => array(
				array(
					'colspan' => sizeof($gridBodyColumns),
					'text'    => '<table cellpadding="1" cellspacing="0" border="0" width="75%">' .
						'<tr>' .
						'<td><b>' . sysLanguage::get('TEXT_DATE_ORDER_CREATED') . '</b></td>' .
						'<td> ' . $Sale->getDateAdded()->format(sysLanguage::getDateFormat('short')) . '</td>' .
						'<td><b>' . sysLanguage::get('TEXT_DATE_ORDER_LAST_MODIFIED') . '</b></td>' .
						'<td>' . $Sale->getDateModified()->format(sysLanguage::getDateFormat('short')) . '</td>' .
						'<td></td>' .
						'</tr>' .
						'</table>'
				)
			)
		));
	}
}
echo $tableGrid->draw();
