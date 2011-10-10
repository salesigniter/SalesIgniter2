<?php
	class PurchaseTypeTabReservation_tab_discounts{

	private $heading;
	private $displayOrder = 2;
	public function __construct() {
		$this->setHeading(sysLanguage::get('PURCHASE_TYPE_TAB_RESERVATION_HEADING_DISCOUNTS'));
	}

	public function setHeading($val) {
		$this->heading = $val;
	}

	public function getHeading() {
		return $this->heading;
	}

	public function getDisplayOrder(){
		return $this->displayOrder;
	}

	public function setDisplayOrder($val){
		$this->displayOrder = $val;
	}

	public function addTab(&$TabsObj, Product $Product) {
		$PurchaseTypeCls = PurchaseTypeModules::getModule('reservation');
		$PayPerRentalTypes = Doctrine_Query::create()
			->from('PayPerRentalTypes')
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

		$typeName = $PurchaseTypeCls->getCode();

		$TypesTabs = htmlBase::newElement('tabs');
		foreach($PayPerRentalTypes as $iType){
			$TypeId = $iType['pay_per_rental_types_id'];

			$DiscountTable = htmlBase::newElement('table');
			$DiscountTable->addHeaderRow(array(
					'columns' => array(
						array('text' => 'From'),
						array('text' => 'To'),
						array('text' => 'Percentage')
					)
				));

			for($i=0; $i<5; $i++){
				$FromInput = htmlBase::newElement('input')
					->attr('size', '4')
					->setName('ppr_discounts[' . $TypeId . '][' . $i . '][from]');

				$ToInput = htmlBase::newElement('input')
					->attr('size', '4')
					->setName('ppr_discounts[' . $TypeId . '][' . $i . '][to]');

				$PercentInput = htmlBase::newElement('input')
					->attr('size', '4')
					->setName('ppr_discounts[' . $TypeId . '][' . $i . '][percent]')
					->setLabel('%')
					->setLabelPosition('after');

				$Discounts = Doctrine_Query::create()
					->from('ProductsPayPerRentalDiscounts')
					->where('discount_stage = ?', $i)
					->andWhere('ppr_type = ?', $TypeId)
					->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
				if ($Discounts && sizeof($Discounts) > 0){
					$FromInput->val($Discounts[0]['discount_from']);
					$ToInput->val($Discounts[0]['discount_to']);
					$PercentInput->val($Discounts[0]['discount_percent']);
				}

				$DiscountTable->addBodyRow(array(
						'columns' => array(
							array('text' => $FromInput),
							array('text' => $ToInput),
							array('text' => $PercentInput)
						)
					));
			}

			$TypesTabs->addTabHeader('productPricingTab_' . $typeName . '_discounts_' . $TypeId, array('text' => $iType['pay_per_rental_types_name']))
				->addTabPage('productPricingTab_' . $typeName . '_discounts_' . $TypeId, array('text' => $DiscountTable->draw()));
		}
		$TabsObj->addTabHeader('productPricingTab_' . $typeName . '_discounts', array('text' => $this->getHeading()))
			->addTabPage('productPricingTab_' . $typeName . '_discounts', array('text' => $TypesTabs->draw()));
	}
}

?>