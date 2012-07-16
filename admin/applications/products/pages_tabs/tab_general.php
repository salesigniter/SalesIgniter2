<?php
$ProductStatusEnabled = htmlBase::newElement('radio')
	->setName('products_status')
	->setLabel(sysLanguage::get('TEXT_PRODUCT_AVAILABLE'))
	->setLabelPosition('right')
	->setValue('1');

$ProductStatusDisabled = htmlBase::newElement('radio')
	->setName('products_status')
	->setLabel(sysLanguage::get('TEXT_PRODUCT_NOT_AVAILABLE'))
	->setLabelPosition('right')
	->setValue('0');

$ProductFeaturedStatusEnabled = htmlBase::newElement('radio')
	->setName('products_featured')
	->setLabel(sysLanguage::get('TEXT_PRODUCT_FEATURED'))
	->setLabelPosition('right')
	->setValue('1');

$ProductFeaturedStatusDisabled = htmlBase::newElement('radio')
	->setName('products_featured')
	->setLabel(sysLanguage::get('TEXT_PRODUCT_NON_FEATURED'))
	->setLabelPosition('right')
	->setValue('0');

$ProductDateAvailable = htmlBase::newElement('input')
	->setName('products_date_available')
	->addClass('useDatepicker');

$ProductOnOrder = htmlBase::newElement('checkbox')
	->setId('productOnOrder')
	->setName('products_on_order')
	->setValue('1');

$ProductDateOrdered = htmlBase::newElement('input')
	->setName('products_date_ordered')
	->addClass('useDatepicker');

$ProductModel = htmlBase::newElement('input')
	->setName('products_model');

$ProductDisplayOrder = htmlBase::newElement('input')
	->setName('products_display_order');

$ProductWeight = htmlBase::newElement('input')
	->setName('products_weight');

if ($Product->getId() > 0){
	if ($Product->isActive()){
		$ProductStatusEnabled->setChecked(true);
	}
	else {
		$ProductStatusDisabled->setChecked(true);
	}

	$ProductFeaturedStatusEnabled->setChecked($Product->isFeatured());
	$ProductFeaturedStatusDisabled->setChecked(!$Product->isFeatured());

	$ProductOnOrder->setChecked($Product->isOnOrder());
	$ProductDateAvailable->setValue($Product->getDateAvailable()->format('Y-m-d'));
	$ProductDateOrdered->setValue($Product->getDateOrdered()->format('Y-m-d'));
	$ProductModel->setValue($Product->getModel());
	$ProductWeight->setValue($Product->getWeight());
	$ProductDisplayOrder->setValue($Product->getDisplayOrder());
}
else {
	$ProductStatusEnabled->setChecked(true);
	$ProductFeaturedStatusDisabled->setChecked(true);
}

$Fieldset = htmlBase::newFieldsetFormBlock();
$Fieldset->setLegend('General Product Information');
$Fieldset->addBlock('status', sysLanguage::get('TEXT_PRODUCTS_STATUS'), array(
	array($ProductStatusEnabled, $ProductStatusDisabled)
));
$Fieldset->addBlock('featured', sysLanguage::get('TEXT_PRODUCTS_FEATURED'), array(
	array($ProductFeaturedStatusEnabled, $ProductFeaturedStatusDisabled)
));
$Fieldset->addBlock('date_avail', sysLanguage::get('TEXT_PRODUCTS_DATE_AVAILABLE'), array(
	array($ProductDateAvailable)
));
$Fieldset->addBlock('on_order', sysLanguage::get('TEXT_PRODUCT_ON_ORDER'), array(
	array($ProductOnOrder)
));
$Fieldset->addBlock('date_ordered', sysLanguage::get('TEXT_PRODUCT_DATE_ORDERED'), array(
	array($ProductDateOrdered)
));
$Fieldset->addBlock('model', sysLanguage::get('TEXT_PRODUCTS_MODEL'), array(
	array($ProductModel)
));
$Fieldset->addBlock('weight', sysLanguage::get('TEXT_PRODUCTS_WEIGHT'), array(
	array($ProductWeight)
));
$Fieldset->addBlock('display_order', sysLanguage::get('TEXT_PRODUCTS_DISPLAY_ORDER'), array(
	array($ProductDisplayOrder)
));

echo $Fieldset->draw();
