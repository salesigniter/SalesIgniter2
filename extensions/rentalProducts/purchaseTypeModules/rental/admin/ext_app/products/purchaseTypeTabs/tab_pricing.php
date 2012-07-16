<?php
class PurchaseTypeTabRental_tab_pricing
{

	private $heading;

	private $displayOrder = 2;

	public function __construct()
	{
		$this->setHeading(sysLanguage::get('TAB_PURCHASE_TYPE_HEADING_PRICING'));
	}

	public function getDisplayOrder()
	{
		return $this->displayOrder;
	}

	public function setDisplayOrder($val)
	{
		$this->displayOrder = $val;
	}

	public function setHeading($val)
	{
		$this->heading = $val;
	}

	public function getHeading()
	{
		return $this->heading;
	}

	public function addTab(&$TabsObj, Product $Product, $PurchaseType)
	{
		global $tax_class_array, $appExtension;
		if ($PurchaseType->getConfigData('PRICING_ENABLED') == 'True'){
			$pricingTypeName = $PurchaseType->getCode();
			$pricingTypeText = $PurchaseType->getTitle();
			$productsPrice = $PurchaseType->getData('price');
			$productsTaxId = $PurchaseType->getData('tax_class_id');

			$inputNet = htmlBase::newElement('input')
				->addClass('netPricing')
				->setName('pricing[' . $pricingTypeName . '][price]')
				->val($productsPrice);

			$inputGross = htmlBase::newElement('input')
				->addClass('grossPricing')
				->val($productsPrice);

			$inputTable = htmlBase::newElement('table')
				->setCellPadding(2)
				->setCellSpacing(0)
				->addClass('pricingTable');

			$inputTable->addBodyRow(array(
				'columns' => array(
					array('text' => sysLanguage::get('TEXT_PRODUCTS_TAX_CLASS')),
					array('text' => tep_draw_pull_down_menu('pricing[' . $pricingTypeName . '][tax_class_id]', $tax_class_array, $productsTaxId, ' class="taxClass"'))
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

			$TabsObj
				->addTabHeader('purchaseTypeRentalSettingsTabPricing', array('text' => $this->getHeading()))
				->addTabPage('purchaseTypeRentalSettingsTabPricing', array('text' => $inputTable->draw()));
		}
	}
}
