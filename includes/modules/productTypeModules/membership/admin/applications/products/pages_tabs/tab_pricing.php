<?php
$inputNet = htmlBase::newElement('input')
	->addClass('netPricing')
	->setName('products_price[membership]')
	->setId('products_price_membership')
	->val($productsPrice);

$inputGross = htmlBase::newElement('input')
	->addClass('grossPricing')
	->setName('products_price_gross[membership]')
	->setId('products_price_membership_gross')
	->val($productsPrice);

$inputTable = htmlBase::newElement('table')
	->setCellPadding(2)
	->setCellSpacing(0);

$inputTable->addBodyRow(array(
		'columns' => array(
			array('text' => sysLanguage::get('TEXT_PRODUCTS_TAX_CLASS')),
			array('text' => tep_draw_pull_down_menu('products_tax_class_id[membership]', $tax_class_array, 0, 'class="taxClassId" id="tax_class_id_membership"'))
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

echo $inputTable->draw();
