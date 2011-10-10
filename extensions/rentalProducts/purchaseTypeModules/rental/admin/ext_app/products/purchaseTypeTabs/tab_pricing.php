<?php
class PurchaseTypeTabRental_tab_pricing
{

	private $heading;

	private $displayOrder = 2;

	public function __construct() {
		$this->setHeading(sysLanguage::get('TAB_PURCHASE_TYPE_HEADING_PRICING'));
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
		global $tax_class_array, $appExtension;
		if ($PurchaseType->getConfigData('PRICING_ENABLED') == 'True'){
			$PurchaseType->loadData($Product->getId());

			$pricingTypeName = $PurchaseType->getCode();
			$pricingTypeText = $PurchaseType->getTitle();
			$productsPrice = $PurchaseType->getData('price');
			$productsTaxId = $PurchaseType->getData('tax_class_id');

			$priceTabs = array(
				array(
					'id' => 'global',
					'name' => 'Global'
				)
			);

			$pricingTabs = htmlBase::newElement('tabs')
				->setId('purchaseTypeRentalSettingsTabPricingTabs');

			$MultiStore = $appExtension->getExtension('multiStore');
			if ($MultiStore && $MultiStore->isEnabled()){
				foreach($MultiStore->getStoresArray() as $sInfo){
					if ($sInfo['stores_id'] == 1) continue;
					$priceTabs[] = array(
						'id' => $sInfo['stores_id'],
						'name' => $sInfo['stores_name']
					);
				}
			}

			foreach($priceTabs as $tInfo){
				$inputName = 'pricing[' . $pricingTypeName . '][' . $tInfo['id'] . ']';
				$globalTable = '';
				if ($tInfo['id'] != 'global'){
					if ($PurchaseType->hasData('price', $tInfo['id'])){
						$useGlobal = false;
					}else{
						$useGlobal = true;
					}
					$globalTable = sysLanguage::get('TEXT_ENTRY_USE_GLOBAL') . htmlBase::newElement('radio')
						->addGroup(array(
							'name' => $inputName . '[use_global]',
							'addCls' => 'useGlobalPricing',
							'checked' => ($useGlobal === true ? '1' : '0'),
							'data' => array(
								array('value' => '0', 'label' => sysLanguage::get('TEXT_NO'), 'labelPosition' => 'after'),
								array('value' => '1', 'label' => sysLanguage::get('TEXT_YES'), 'labelPosition' => 'after')
							)
						))
						->draw();
				}

				$productsPrice = $PurchaseType->getData('price', $tInfo['id'], false);
				$productsTaxId = $PurchaseType->getData('tax_class_id', $tInfo['id'], false);

				$inputNet = htmlBase::newElement('input')
					->addClass('netPricing')
					->setName($inputName . '[price]')
					->val($productsPrice);

				$inputGross = htmlBase::newElement('input')
					->addClass('grossPricing')
					->val($productsPrice);

				$inputTable = htmlBase::newElement('table')
					->setCellPadding(2)
					->setCellSpacing(0)
					->addClass('pricingTable');

				if (isset($useGlobal) && $useGlobal === true){
					$inputTable->hide();
				}

				$inputTable->addBodyRow(array(
						'columns' => array(
							array('text' => sysLanguage::get('TEXT_PRODUCTS_TAX_CLASS')),
							array('text' => tep_draw_pull_down_menu($inputName . '[tax_class_id]', $tax_class_array, $productsTaxId, ' class="taxClass"'))
						)
					));
				$inputTable->addBodyRow(array(
						'columns' => array(
							array('text' => sysLanguage::get('TEXT_PRODUCTS_PRICE_NET')),
							array('text' => $inputNet->draw())
						)
					));
				$inputTable->addBodyRow(array(
						'columns' => array(
							array('text' => sysLanguage::get('TEXT_PRODUCTS_PRICE_GROSS')),
							array('text' => $inputGross->draw())
						)
					));

				EventManager::notify('NewProductPricingTabBottom', $tInfo, $Product, &$inputTable, &$PurchaseType);

				$pricingTabs->addTabHeader('purchaseTypeRentalSettingsTabPricingTabs_' . $tInfo['id'], array('text' => $tInfo['name']))
					->addTabPage('purchaseTypeRentalSettingsTabPricingTabs_' . $tInfo['id'], array('text' => $globalTable . $inputTable->draw()));
			}

			$TabsObj->addTabHeader('purchaseTypeRentalSettingsTabPricing', array('text' => $this->getHeading()))
				->addTabPage('purchaseTypeRentalSettingsTabPricing', array('text' => $pricingTabs->draw()));
		}
	}
}
