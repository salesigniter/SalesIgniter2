<?php
	class PurchaseTypeTabReservation_tab_disabled_dates{

	private $heading;
	private $displayOrder = 5;

	public function __construct() {
		$this->setHeading(sysLanguage::get('PURCHASE_TYPE_TAB_RESERVATION_HEADING'));
	}

	public function setHeading($val) {
		$this->heading = $val;
	}

	public function getHeading() {
		return $this->heading;
	}

	public function getDisplayOrder() {
		return $this->displayOrder;
	}

	public function setDisplayOrder($val) {
		$this->displayOrder = $val;
	}

	public function addTab(&$TabsObj, Product $Product) {
		$PurchaseTypeCls = PurchaseTypeModules::getModule('reservation');

		$typeName = $PurchaseTypeCls->getCode();
		$typeText = sysLanguage::get('PURCHASE_TYPE_TAB_RESERVATION_HEADING_DISABLED_DATES');

		/*Start Hidden Dates*/

		$Qcheck = Doctrine_Query::create()
			->select('MAX(hidden_dates_id) as nextId')
			->from('PayPerRentalHiddenDates')
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

		$TableHidden = htmlBase::newElement('table')
			->setCellPadding(3)
			->setCellSpacing(0)
			->addClass('ui-widget ui-widget-content pprHiddenTable')
			->css(array(
			'width' => '100%'
		))
			->attr('data-next_id', $Qcheck[0]['nextId'] + 1)
			->attr('language_id', Session::get('languages_id'));

		$TableHidden->addHeaderRow(array(
			'addCls' => 'ui-state-hover pprHiddenTableHeader',
			'columns' => array(
				array('text' => '<div style="float:left;width:80px;">' . sysLanguage::get('TABLE_HEADING_HIDDEN_START_DATE') . '</div>' .
					'<div style="float:left;width:150px;">' . sysLanguage::get('TABLE_HEADING_HIDDEN_END_DATE') . '</div>' .
					'<div style="float:left;width:40px;">' . htmlBase::newElement('icon')->setType('insert')
					->addClass('insertIconHidden')->draw() .
					'</div><br style="clear:both"/>'
				)
			)
		));

		$deleteIcon = htmlBase::newElement('icon')->setType('delete')->addClass('deleteIconHidden')->draw();
		$QhiddenDates = Doctrine_Query::create()
			->from('PayPerRentalHiddenDates')
			->where('products_id=?', $Product->getId())
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

		$hiddenList = htmlBase::newElement('list')
			->addClass('hiddenList');

		foreach($QhiddenDates as $iHidden){
			$hiddenid = $iHidden['hidden_dates_id'];
			$hiddenStartDate = htmlBase::newElement('input')
				->addClass('ui-widget-content date_hidden')
				->setName('pprhidden[' . $hiddenid . '][start_date]')
				->attr('size', '15')
				->val(strftime('%Y-%m-%d', strtotime($iHidden['hidden_start_date'])));

			$hiddenEndDate = htmlBase::newElement('input')
				->addClass('ui-widget-content date_hidden')
				->setName('pprhidden[' . $hiddenid . '][end_date]')
				->attr('size', '15')
				->val(strftime('%Y-%m-%d', strtotime($iHidden['hidden_end_date'])));

			$divLi1 = '<div style="float:left;width:80px;">' . $hiddenStartDate->draw() . '</div>';
			$divLi2 = '<div style="float:left;width:80px;">' . $hiddenEndDate->draw() . '</div>';
			$divLi5 = '<div style="float:left;width:40px;">' . $deleteIcon . '</div>';

			$liObj = new htmlElement('li');
			$liObj->css(array(
				'font-size' => '.8em',
				'list-style' => 'none',
				'line-height' => '1.1em',
				'border-bottom' => '1px solid #cccccc',
				'cursor' => 'crosshair'
			))
				->html($divLi1 . $divLi2 . $divLi5 . '<br style="clear:both;"/>');
			$hiddenList->addItemObj($liObj);
		}
		$TableHidden->addBodyRow(array(
			'columns' => array(
				array('align' => 'center', 'text' => $hiddenList->draw(), 'addCls' => 'hiddenDatesPPR')
			)
		));

		/*End Hidden Dates*/
		$mainTable = htmlBase::newElement('table')->setCellPadding(3)->setCellSpacing(0);
		$mainTable->addBodyRow(array(
			'columns' => array(
				array('addCls' => 'main', 'text' => sysLanguage::get('TEXT_PAY_PER_RENTAL_HIDDEN_DATES')),
				array('addCls' => 'main', 'text' => $TableHidden)
			)
		));

		$TabsObj->addTabHeader('productDisabledDatesTab_' . $typeName, array('text' => $typeText))
			->addTabPage('productDisabledDatesTab_' . $typeName, array('text' => $mainTable->draw()));
	}
}

?>