<?php
class PurchaseTypeTabMembershipRental_tab_general
{

	private $heading;

	private $displayOrder = 1;

	public function __construct() {
		$this->setHeading(sysLanguage::get('TAB_PURCHASE_TYPE_HEADING_GENERAL'));
	}

	public function getDisplayOrder() {
		return $this->displayOrder;
	}

	public function setDisplayOrder($val) {
		$this->displayOrder = $val;
	}

	public function setHeading($val) {
		$this->heading = $val;
	}

	public function getHeading() {
		return $this->heading;
	}

	public function addTab(&$TabsObj, Product $Product, $PurchaseType) {
		$PurchaseType->loadData($Product->getId());

		$pricingTypeName = $PurchaseType->getCode();
		$pricingTypeText = $PurchaseType->getTitle();

		$enabledInput = htmlBase::newElement('checkbox')
			->setName('products_type[]')
			->setChecked($PurchaseType->getData('status') == 1)
			->val($pricingTypeName);

		$inputTable = htmlBase::newElement('table')
			->setCellPadding(2)
			->setCellSpacing(0);

		$inputTable->addBodyRow(array(
				'columns' => array(
					array('text' => sysLanguage::get('TEXT_PRODUCTS_ENABLED')),
					array('text' => $enabledInput)
				)
			));

		$Qmembership = Doctrine_Query::create()
			->from('Membership m')
			->leftJoin('m.MembershipPlanDescription md')
			->where('md.language_id = ?', Session::get('languages_id'))
			->orderBy('sort_order')
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

		$tableGrid = htmlBase::newElement('table')
			->setCellPadding(2)
			->setCellSpacing(0);

		$tableGrid->addHeaderRow(array(
				'columns' => array(
					array('text' => sysLanguage::get('TABLE_HEADING_MEMBERSHIP')),
					array('text' => 'Not Enabled For Product')
				)
			));

		$enabledMemberships = explode(';', $Product->getMembershipEnabled());
		foreach($Qmembership as $mInfo){
			$planId = $mInfo['plan_id'];
			$planName = $mInfo['MembershipPlanDescription'][0]['name'];
			$checked = false;

			foreach($enabledMemberships as $checkedMembership){
				if($planId == $checkedMembership){
					$checked = true;
					break;
				}
			}

			$htmlCheckbox = htmlBase::newElement('checkbox')
				->setName('rental_membership_enabled[]')
				->setChecked($checked)
				->setValue($planId);

			$tableGrid->addBodyRow(array(
					'columns' => array(
						array('text' => $planName),
						array('text' => $htmlCheckbox->draw(), 'align' => 'center')
					)
				));
		}

		$keepItPrice = htmlBase::newElement('input')
			->setName('products_keepit_price')
			->setValue($Product->getKeepPrice());

		$inputTable->addBodyRow(array(
				'columns' => array(
					array('text' => sysLanguage::get('TEXT_ENABLED_MEMBERSHIPS')),
					array('text' => $tableGrid->draw())
				)
			));

		$inputTable->addBodyRow(array(
				'columns' => array(
					array('text' => sysLanguage::get('TEXT_KEEP_PRICE')),
					array('text' => $keepItPrice->draw())
				)
		));

		EventManager::notify('NewProductGeneralTabBottom', $Product, &$inputTable, &$PurchaseType);

		$TabsObj->addTabHeader('purchaseTypeMembershipRentalSettingsTabGeneral', array('text' => $this->getHeading()))
			->addTabPage('purchaseTypeMembershipRentalSettingsTabGeneral', array('text' => $inputTable));
	}
}
