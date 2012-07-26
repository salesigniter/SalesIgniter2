<?php
$Coupons = Doctrine_Core::getTable('Coupons');
if (isset($_GET['coupon_id'])){
	$Coupon = $Coupons->find((int)$_GET['coupon_id']);
	$boxHeading = sysLanguage::get('TEXT_ACTION_WINDOW_HEADING_EDIT');
}
else {
	$Coupon = $Coupons->getRecord();
	$boxHeading = sysLanguage::get('TEXT_ACTION_WINDOW_HEADING_NEW');
	$Coupon->coupon_start_date = new SesDateTime();
	$Coupon->coupon_expire_date = new SesDateTime();
}

$Fieldset = htmlBase::newFieldsetFormBlock();
$Fieldset->setLegend($boxHeading);

$CouponActiveYes = htmlBase::newRadio()
	->setLabel(sysLanguage::get('TEXT_ENABLED'))
	->setLabelPosition('after')
	->setValue('Y');

$CouponActiveNo = htmlBase::newRadio()
	->setLabel(sysLanguage::get('TEXT_DISABLED'))
	->setLabelPosition('after')
	->setValue('N');

$CouponStatusGroup = htmlBase::newRadioGroup()
	->setGroupSeparator('&nbsp;')
	->setName('coupon_active')
	->setChecked($Coupon->coupon_active)
	->addInput($CouponActiveYes)
	->addInput($CouponActiveNo);

$CouponCode = htmlBase::newInput()
	->setName('coupon_code')
	->setValue($Coupon->coupon_code);

$CouponAmount = htmlBase::newInput()
	->setName('coupon_amount')
	->setValue($Coupon->coupon_amount);
$CouponAmount = htmlBase::newElement('span')
	->append($CouponAmount);

$FreeShipping = htmlBase::newCheckbox()
	->setName('coupon_free_ship')
	->setChecked(($Coupon->coupon_type == 'S'))
	->setValue('Y');

$NameFields = array();
foreach(sysLanguage::getLanguages() as $lInfo){
	$Input = htmlBase::newInput()
		->setName('coupon_name[' . $lInfo['id'] . ']')
		->setLabel($lInfo['showName']('&nbsp;'))
		->setLabelPosition('before')
		->setValue($Coupon->CouponsDescription[$lInfo['id']]->coupon_name);

	$NameFields[] = $Input;
}

$DescriptionFields = array();
foreach(sysLanguage::getLanguages() as $lInfo){
	$Input = htmlBase::newTextarea()
		->setName('coupon_description[' . $lInfo['id'] . ']')
		->setLabel($lInfo['showName']('&nbsp;'))
		->setLabelPosition('before')
		->html($Coupon->CouponsDescription[$lInfo['id']]->coupon_description);

	$DescriptionFields[] = $Input;
}

$ProductTypes = array();
ProductTypeModules::loadModules();
foreach(ProductTypeModules::getModules() as $Module){
	$Checkbox = htmlBase::newCheckbox()
		->setLabel($Module->getTitle())
		->setLabelPosition('after')
		->setName('restrict_to_product_type[]')
		->setValue($Module->getCode());
	$ProductTypes[] = $Checkbox;
}

$CouponMinOrder = htmlBase::newInput()
	->setName('coupon_minimum_order')
	->setLabel(sysLanguage::get('TEXT_MINIMUM'))
	->setLabelPosition('above')
	->setValue($Coupon->coupon_minimum_order);
$CouponMaxOrder = htmlBase::newInput()
	->setName('coupon_maximum_order')
	->setLabel(sysLanguage::get('TEXT_MAXIMUM'))
	->setLabelPosition('above')
	->setValue($Coupon->coupon_maximum_order);

$CouponUsageCoupon = htmlBase::newInput()
	->setName('uses_per_coupon')
	->setLabel(sysLanguage::get('TEXT_INFO_COUPON_USES_COUPON'))
	->setLabelPosition('above')
	->setValue($Coupon->uses_per_coupon);
$CouponUsageUser = htmlBase::newInput()
	->setName('uses_per_user')
	->setLabel(sysLanguage::get('TEXT_INFO_COUPON_USES_USER'))
	->setLabelPosition('above')
	->setValue($Coupon->uses_per_user);

$CouponStartDate = htmlBase::newDatePicker()
	->setName('coupon_start_date')
	->setLabel(sysLanguage::get('TEXT_INFO_COUPON_STARTDATE'))
	->setLabelPosition('above')
	->setValue($Coupon->coupon_start_date);
$CouponFinishDate = htmlBase::newDatePicker()
	->setName('coupon_expire_date')
	->setLabel(sysLanguage::get('TEXT_INFO_COUPON_FINISHDATE'))
	->setLabelPosition('above')
	->setValue($Coupon->coupon_expire_date);

$StatusLabel = htmlBase::newLabel()
	->html('<b>' . sysLanguage::get('TEXT_ENTRY_COUPON_STATUS') . '</b><br>' . sysLanguage::get('TEXT_INFO_COUPON_STATUS_HELP'));

$CodeLabel = htmlBase::newLabel()
	->html('<b>' . sysLanguage::get('TEXT_ENTRY_COUPON_CODE') . '</b><br>' . sysLanguage::get('TEXT_INFO_COUPON_CODE_HELP'));
$AmountLabel = htmlBase::newLabel()
	->html('<b>' . sysLanguage::get('TEXT_ENTRY_COUPON_AMOUNT') . '</b><br>' . sysLanguage::get('TEXT_INFO_COUPON_AMOUNT_HELP'));

$NameLabel = htmlBase::newLabel()
	->html('<b>' . sysLanguage::get('TEXT_ENTRY_COUPON_NAME') . '</b><br>' . sysLanguage::get('TEXT_INFO_COUPON_NAME_HELP'));
$DescriptionLabel = htmlBase::newLabel()
	->html('<b>' . sysLanguage::get('TEXT_ENTRY_COUPON_DESCRIPTION') . '</b><br>' . sysLanguage::get('TEXT_INFO_COUPON_DESCRIPTION_HELP'));

$Fieldset->addBlock('general', sysLanguage::get('TEXT_FIELDSET_BLOCK_COUPON_GENERAL'), array(
	array($StatusLabel),
	array($CouponStatusGroup),
	array(htmlBase::newElement('hr')),
	array($CodeLabel, $AmountLabel),
	array($CouponCode, $CouponAmount),
	array(htmlBase::newElement('hr')),
	array($NameLabel, $DescriptionLabel),
	array($NameFields, $DescriptionFields)
));

$TaxCalculation = htmlBase::newRadioGroup()
	->setName('coupon_tax_handling')
	->addInput(htmlBase::newRadio()->setValue('include')->setLabel('Include ( Percent Based Discount Only )')->setLabelPosition('after'))
	->addInput(htmlBase::newRadio()->setValue('exclude')->setLabel('Exclude ( Percent Based Discount Only )')->setLabelPosition('after'));

$FreeShippingLabel = htmlBase::newLabel()
	->html('<b>' . sysLanguage::get('TEXT_ENTRY_COUPON_FREE_SHIPPING') . '</b><br>' . sysLanguage::get('TEXT_INFO_COUPON_FREE_SHIPPING_HELP'));
$TaxCalculationLabel = htmlBase::newLabel()
	->html('<b>' . sysLanguage::get('TEXT_ENTRY_COUPON_TAX_CALCULATION') . '</b><br>' . sysLanguage::get('TEXT_INFO_COUPON_TAX_CALCULATION_HELP'));

$Fieldset->addBlock('taxes', 'Discount Settings', array(
	array($TaxCalculationLabel, $FreeShippingLabel),
	array($TaxCalculation, $FreeShipping)
));

$ExcludedProducts = htmlBase::newSelectToList()
	->setName('coupon_products')
	->setSelected($Coupon->coupon_products)
	->setSize(30);
$Products = Doctrine_Core::getTable('Products')
	->findAll();
foreach($Products as $Product){
	$ExcludedProducts->addOption($Product->products_id, $Product->ProductsDescription[sysLanguage::getId()]->products_name);
}

$ProductListUse = htmlBase::newSelectbox()
	->setLabel(sysLanguage::get('TEXT_ENTRY_COUPON_PRODUCTS_USE'))
	->setLabelPosition('above')
	->setName('coupon_products_use')
	->selectOptionByValue($Coupon->coupon_products_use)
	->addOption('restrict_to', sysLanguage::get('TEXT_COUPON_USE_RESTRICT'))
	->addOption('exclude', sysLanguage::get('TEXT_COUPON_USE_EXCLUDE'));

$Fieldset->addBlock('restrictions', sysLanguage::get('TEXT_FIELDSET_BLOCK_COUPON_RESTRICTIONS'), array(
	array(htmlBase::newLabel()->html('<b>' . sysLanguage::get('TEXT_ENTRY_COUPON_EXCLUDE_PRODUCTS') . '</b><br>' . sysLanguage::get('TEXT_INFO_COUPON_EXCLUDE_PRODUCTS_HELP'))),
	array($ProductListUse),
	array($ExcludedProducts),
	array(htmlBase::newElement('hr')),
	array(htmlBase::newLabel()->html('<b>' . sysLanguage::get('TEXT_ENTRY_COUPON_MIN_MAX') . '</b><br>' . sysLanguage::get('TEXT_ENTRY_COUPON_MIN_MAX_HELP'))),
	array($CouponMinOrder, $CouponMaxOrder),
	array(htmlBase::newElement('hr')),
	array(htmlBase::newLabel()->html('<b>' . sysLanguage::get('TEXT_ENTRY_COUPON_USAGE') . '</b><br>' . sysLanguage::get('TEXT_INFO_COUPON_USAGE_HELP'))),
	array($CouponUsageCoupon, $CouponUsageUser),
	array(htmlBase::newElement('hr')),
	array(htmlBase::newLabel()->html('<b>' . sysLanguage::get('TEXT_ENTRY_COUPON_DATES') . '</b><br>' . sysLanguage::get('TEXT_INFO_COUPON_DATES_HELP'))),
	array($CouponStartDate, $CouponFinishDate)
));

//$infoBox->addContentRow(sysLanguage::get('TEXT_INFO_COUPON_NUMBER_DAYS_MEMBERSHIP') . '<br>' . tep_draw_input_field('number_days_membership', $Coupon->number_days_membership));

$SaveButton = htmlBase::newElement('button')
	->addClass('saveButton')
	->usePreset('save');

$CancelButton = htmlBase::newElement('button')
	->addClass('cancelButton')
	->usePreset('cancel');

$Infobox = htmlBase::newActionWindow()
	->addButton($SaveButton)
	->addButton($CancelButton)
	->setContent($Fieldset->draw());

EventManager::attachActionResponse($Infobox->draw(), 'html');
