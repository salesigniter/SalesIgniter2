<?php
class LateFees_admin_customers_edit extends Extension_lateFees {

	public function __construct(){
		parent::__construct('lateFees');
	}

	public function load(){
		if ($this->enabled === false) return;

		EventManager::attachEvents(array(
				'AdminCustomerEditBuildTabs'
			), null, $this);
	}

	public function AdminCustomerEditBuildTabs($Customer, &$tabsObj){
		global $currencies;
		$totalFees = 0;

		$FeesGrid = htmlBase::newElement('newGrid')
			->addClass('lateFeesGrid')
			->usePagination(false);

		$FeesGrid->addButtons(array(
				htmlBase::newElement('button')->setText('Void Fee')->addClass('voidButton passProtect')->disable(),
				//htmlBase::newElement('button')->setText('Modify Fee')->addClass('modifyButton')->disable(),
				htmlBase::newElement('button')->setText('Mark Paid')->addClass('paidButton passProtect')->disable()
			));

		$FeesGrid->addHeaderRow(array(
				'columns' => array(
					array('align' => 'left', 'text' => sysLanguage::get('TABLE_HEADING_DATE_ADDED')),
					array('text' => sysLanguage::get('TABLE_HEADING_IS_PAID')),
					array('text' => sysLanguage::get('TABLE_HEADING_ORDERED_PRODUCT')),
					array('align' => 'right', 'text' => sysLanguage::get('TABLE_HEADING_FEE_AMOUNT'))
				)
			));

		foreach($this->getAllFees($Customer->customers_id) as $Fee){
			if ($Fee['fee_status'] == $this->openStatusId()){
				$totalFees += $Fee['fee_amount'];
			}

			$paidIcon = htmlBase::newElement('icon');
			switch($Fee['fee_status']){
				case ($this->openStatusId()):
					$paidIcon->setType('circleClose')->attr('tooltip', sysLanguage::get('TOOLTIP_TEXT_FEE_OPEN'));
					break;
				case ($this->paidStatusId()):
					$paidIcon->setType('circleCheck')->attr('tooltip', sysLanguage::get('TOOLTIP_TEXT_FEE_PAID'));
					break;
				case ($this->voidStatusId()):
					$paidIcon->setType('alert')->attr('tooltip', sysLanguage::get('TOOLTIP_TEXT_FEE_VOIDED'));
					break;
			}

			$FeesGrid->addBodyRow(array(
					'rowAttr' => array(
						'data-fee_id' => $Fee['fee_id'],
						'data-is_open' => ($Fee['fee_status'] == $this->openStatusId() ? 'true' : 'false'),
						'data-is_paid' => ($Fee['fee_status'] == $this->paidStatusId() ? 'true' : 'false'),
						'data-is_void' => ($Fee['fee_status'] == $this->voidStatusId() ? 'true' : 'false')
					),
					'columns' => array(
						array('text' => $Fee['date_added']),
						array('align' => 'center', 'text' => $paidIcon->draw()),
						array('text' => $Fee['OrdersProducts']['products_name']),
						array('align' => 'right', 'text' => $currencies->format($Fee['fee_amount']))
					)
				));

			$FeesGrid->addBodyRow(array(
					'addCls' => 'gridInfoRow',
					'columns' => array(
						array(
							'colspan' => 4,
							'text' => '<table cellpadding="1" cellspacing="0" border="0" width="75%">' .
								'<tr>' .
								'<td><b>' . sysLanguage::get('TEXT_DATE_PAID') . '</b></td>' .
								'<td> ' . $Fee['date_paid'] . '</td>' .
								'</tr>' .
								'<tr>' .
								'<td><b>' . sysLanguage::get('TEXT_INFO_COMMENTS') . '</b></td>' .
								'<td>'  . $Fee['comments'] . '</td>' .
								'</tr>' .
								'</table>'
						)
					)
				));
		}

		$FeesTotal = htmlBase::newElement('span')
			->addClass('feesTotal')
			->html($currencies->format($totalFees));

		$tabsObj
			->addTabHeader('lateFeesTab', array('text' => sysLanguage::get('TAB_LATE_FEES')))
			->addTabPage('lateFeesTab', array('text' => sysLanguage::get('TEXT_INFO_FEES_TOTAL') . $FeesTotal->draw() . '<br><br>' . $FeesGrid->draw()));
	}
}