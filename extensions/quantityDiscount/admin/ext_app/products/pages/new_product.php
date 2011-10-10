<?php
/*
	Quantity Discount Extension Version 1
	
	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class quantityDiscount_admin_products_new_product extends Extension_quantityDiscount
{

	public function __construct() {
		parent::__construct();
	}

	public function load() {
		if ($this->enabled === false) {
			return;
		}

		EventManager::attachEvent('NewProductPricingTabBottom', null, $this);
	}

	public function NewProductPricingTabBottom($tInfo, Product &$Product, &$inputTable, PurchaseTypeBase &$PurchaseType) {
		if ($PurchaseType->getConfigData('QUANTITIY_DISCOUNT_ENABLED') == 'True'){
			$typeName = $PurchaseType->getCode();
			if ($Product !== false && $Product->getId() > 0){
				$discounts = Doctrine_Query::create()
					->from('ProductsQuantityDiscounts')
					->where('products_id = ?', $Product->getId())
					->andWhere('purchase_type = ?', $typeName)
					->orderBy('quantity_from asc')
					->execute();
			}
			$inputName = 'pricing[' . $typeName . '][' . $tInfo['id'] . '][discounts]';

			$mainTable = htmlBase::newElement('table')->setCellPadding(3)->setCellSpacing(0)->css('width', '450px');
			$mainTable->addHeaderRow(array(
					'addCls' => 'ui-widget-header',
					'columns' => array(
						array('colspan' => '4', 'align' => 'center', 'text' => 'Quantity Discounts')
					)
				));

			$mainTable->addHeaderRow(array(
					'addCls' => 'ui-state-default',
					'columns' => array(
						array('text' => sysLanguage::get('TABLE_HEADING_QUANTITY_FROM')),
						array('text' => sysLanguage::get('TABLE_HEADING_QUANTITY_TO')),
						array('text' => sysLanguage::get('TABLE_HEADING_PRICE_NET')),
						array('text' => sysLanguage::get('TABLE_HEADING_PRICE_GROSS'))
					)
				));

			for($i = 1; $i < (EXTENSION_QUANTITY_DISCOUNT_LEVELS + 1); $i++){
				$qtyFromInput = htmlBase::newElement('input')
					->setName($inputName . '[' . $i . '][from]')
					->attr('size', '3');

				$qtyToInput = htmlBase::newElement('input')
					->setName($inputName . '[' . $i . '][to]')
					->attr('size', '3');

				$priceInput = htmlBase::newElement('input')
					->addClass('netPricing')
					->setName($inputName . '[' . $i . '][price]')
					->attr('size', '6');

				$priceInputGross = htmlBase::newElement('input')
					->addClass('grossPricing')
					->attr('size', '6');

				if (isset($discounts)){
					if (isset($discounts[$i - 1])){
						$qtyFromInput->setValue($discounts[$i - 1]->quantity_from);
						$qtyToInput->setValue($discounts[$i - 1]->quantity_to);
						$priceInput->setValue($discounts[$i - 1]->price);
						$priceInputGross->setValue($discounts[$i - 1]->price);
					}
				}

				$mainTable->addBodyRow(array(
						'columns' => array(
							array('align' => 'center', 'text' => $qtyFromInput),
							array('align' => 'center', 'text' => $qtyToInput),
							array('align' => 'center', 'text' => $priceInput),
							array('align' => 'center', 'text' => $priceInputGross)
						)
					));
			}

			$inputTable->addBodyRow(array(
					'columns' => array(
						array('colspan' => 2, 'text' => $mainTable->draw())
					)
				));
		}
	}
}

?>