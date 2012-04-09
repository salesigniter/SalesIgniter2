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

	public function addTab(htmlWidget_tabs &$TabsObj, Product $Product, PurchaseType_reservation $PurchaseType) {
		global $appExtension;
		$PayPerRentalTypes = Doctrine_Query::create()
			->from('PayPerRentalTypes')
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

		$typeName = $PurchaseType->getCode();

		$DiscountTabInfo = array(
			array('id' => 0, 'text' => 'Standard')
		);
		$MultiStore = $appExtension->getExtension('multiStore');
		if ($MultiStore && $MultiStore->isEnabled() === true){
			$DiscountTabInfo = array();
			foreach($MultiStore->getStoresArray() as $sInfo){
				$DiscountTabInfo[] = array(
					'id' => $sInfo['stores_id'],
					'text' => $sInfo['stores_name']
				);
			}
		}

		$StoresTabs = htmlBase::newElement('tabs');
		foreach($DiscountTabInfo as $tInfo){
			$storeId = $tInfo['id'];
			$storeName = $tInfo['text'];

			$TypesTabs = htmlBase::newElement('tabs');
			foreach($PayPerRentalTypes as $iType){
				$TypeId = $iType['pay_per_rental_types_id'];

				$DiscountTable = htmlBase::newElement('table');
				$DiscountTable->addHeaderRow(array(
						'columns' => array(
							array('text' => 'From'),
							array('text' => 'To'),
							array('text' => 'Amount'),
							array('text' => 'Method')
						)
					));

				for($i=0; $i<5; $i++){
					$FromInput = htmlBase::newElement('input')
						->attr('size', '4')
						->setName('ppr_discounts[' . $storeId . '][' . $TypeId . '][' . $i . '][from]');

					$ToInput = htmlBase::newElement('input')
						->attr('size', '4')
						->setName('ppr_discounts[' . $storeId . '][' . $TypeId . '][' . $i . '][to]');

					$AmountInput = htmlBase::newElement('input')
						->attr('size', '4')
						->setName('ppr_discounts[' . $storeId . '][' . $TypeId . '][' . $i . '][amount]');

					$TypeInput = htmlBase::newElement('selectbox')
						->addOption('fixed', 'Fixed')
						->addOption('percent', 'Percent')
						->setName('ppr_discounts[' . $storeId . '][' . $TypeId . '][' . $i . '][type]');

					$Discounts = Doctrine_Query::create()
						->from('ProductsPayPerRentalDiscounts')
						->where('discount_stage = ?', $i)
						->andWhere('store_id = ?', $storeId)
						->andWhere('ppr_type = ?', $TypeId)
						->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
					if ($Discounts && sizeof($Discounts) > 0){
						$FromInput->val($Discounts[0]['discount_from']);
						$ToInput->val($Discounts[0]['discount_to']);
						$AmountInput->val($Discounts[0]['discount_amount']);
						$TypeInput->selectOptionByValue($Discounts[0]['discount_type']);
					}

					$DiscountTable->addBodyRow(array(
							'columns' => array(
								array('text' => $FromInput),
								array('text' => $ToInput),
								array('text' => $AmountInput),
								array('text' => $TypeInput)
							)
						));
				}

				$TypesTabs->addTabHeader('productPricingTab_' . $storeId . '_' . $typeName . '_discounts_' . $TypeId, array('text' => $iType['pay_per_rental_types_name']))
					->addTabPage('productPricingTab_' . $storeId . '_' . $typeName . '_discounts_' . $TypeId, array('text' => $DiscountTable->draw()));
			}
			$StoresTabs->addTabHeader('productPricingTab_' . $storeId . '_' . $typeName . '_discounts', array('text' => $storeName))
				->addTabPage('productPricingTab_' . $storeId . '_' . $typeName . '_discounts', array('text' => $TypesTabs->draw()));
		}
		$TabsObj->addTabHeader('productPricingTab_' . $typeName . '_discounts', array('text' => $this->getHeading()))
			->addTabPage('productPricingTab_' . $typeName . '_discounts', array('text' => $StoresTabs->draw()));
	}
}

?>