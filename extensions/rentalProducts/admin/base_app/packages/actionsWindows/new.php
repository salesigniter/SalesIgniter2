<?php
$Membership = Doctrine_Core::getTable('Membership');
if (isset($_GET['pID'])){
	$Package = $Membership->find((int) $_GET['pID']);
	$boxHeading = sysLanguage::get('TEXT_INFO_HEADING_EDIT_PACKAGE');
	$boxIntro = sysLanguage::get('TEXT_INFO_EDIT_INTRO');
}else{
	$Package = $Membership->getRecord();
	$boxHeading = sysLanguage::get('TEXT_INFO_HEADING_NEW_PACKAGE');
	$boxIntro = sysLanguage::get('TEXT_INFO_INSERT_INTRO');
}

$infoBox = htmlBase::newElement('infobox');
$infoBox->setHeader('<b>' . $boxHeading . '</b>');
$infoBox->setButtonBarLocation('top');

$saveButton = htmlBase::newElement('button')->addClass('saveButton')->usePreset('save');
$cancelButton = htmlBase::newElement('button')->addClass('cancelButton')->usePreset('cancel');

$infoBox->addButton($saveButton)->addButton($cancelButton);

$infoBox->addContentRow($boxIntro);

$nameInputs = '';
foreach(sysLanguage::getLanguages() as $lInfo){
	$htmlInput = htmlBase::newElement('input')
		->setName('name[' . $lInfo['id'] . ']');
	if (!empty($Package->MembershipPlanDescription[$lInfo['id']]->name)){
		$htmlInput->val($Package->MembershipPlanDescription[$lInfo['id']]->name);
	}

	$nameInputs .= '<br>' . $lInfo['showName']('&nbsp;') . ': ' . $htmlInput->draw();
}
$infoBox->addContentRow(sysLanguage::get('TEXT_ENTRY_PACKAGE_NAME') . $nameInputs);

$infoBox->addContentRow(htmlBase::newElement('input')->setName('sort_order')->setLabel(sysLanguage::get('TEXT_ENTRY_DISPLAY_ORDER'))->setLabelSeparator('<br>')->setLabelPosition('before')->val($Package->sort_order)->draw());
$infoBox->addContentRow(htmlBase::newElement('checkbox')->setName('default_plan')->setLabel(sysLanguage::get('TEXT_ENTRY_DEFAULT_PLAN'))->setLabelSeparator('<br>')->setLabelPosition('before')->val('1')->setChecked($Package->default_plan == 1)->draw());
$infoBox->addContentRow(htmlBase::newElement('input')->setName('membership_months')->setLabel(sysLanguage::get('TEXT_ENTRY_MEMBERSHIP_MONTHS'))->setLabelSeparator('<br>')->setLabelPosition('before')->val($Package->membership_months)->draw());
$infoBox->addContentRow(htmlBase::newElement('input')->setName('membership_days')->setLabel(sysLanguage::get('TEXT_ENTRY_MEMBERSHIP_DAYS'))->setLabelSeparator('<br>')->setLabelPosition('before')->val($Package->membership_days)->draw());
$infoBox->addContentRow(htmlBase::newElement('input')->setName('no_of_titles')->setLabel(sysLanguage::get('TEXT_ENTRY_NO_OF_TITLES'))->setLabelSeparator('<br>')->setLabelPosition('before')->val($Package->no_of_titles)->draw());
$infoBox->addContentRow(sysLanguage::get('TEXT_ENTRY_TAX_CLASS') . '<br>' . tep_draw_pull_down_menu('rent_tax_class_id', $tax_class_array, $Package->rent_tax_class_id, 'onchange="updateGross()"'));
$infoBox->addContentRow(htmlBase::newElement('input')->setName('price')->setLabel(sysLanguage::get('TEXT_ENTRY_PRICE'))->setLabelSeparator('<br>')->setLabelPosition('before')->val($Package->price)->draw());
$infoBox->addContentRow(htmlBase::newElement('input')->setName('gross_price')->setLabel(sysLanguage::get('TEXT_ENTRY_PRICE_GROSS'))->setLabelSeparator('<br>')->setLabelPosition('before')->val($Package->price)->draw());
$infoBox->addContentRow(htmlBase::newElement('checkbox')->setName('free_trial')->setLabel(sysLanguage::get('TEXT_ENTRY_FREE_TRIAL'))->setLabelSeparator('<br>')->setLabelPosition('before')->val('1')->setChecked($Package->free_trial == 1)->draw());
$infoBox->addContentRow(htmlBase::newElement('input')->setName('free_trial_amount')->setLabel(sysLanguage::get('TEXT_ENTRY_FREE_TRIAL_AMOUNT'))->setLabelSeparator('<br>')->setLabelPosition('before')->val($Package->free_trial_amount)->draw());

EventManager::notify('MembershipPackageEditWindowBeforeDraw', $infoBox, $Package);

$javaScript = '<script language="javascript">' . "\n" .
	'var tax_rates = new Array();' . "\n";

for($i=0, $n=sizeof($tax_class_array); $i<$n; $i++){
	if ($tax_class_array[$i]['id'] > 0){
		$javaScript .= 'tax_rates["' . $tax_class_array[$i]['id'] . '"] = ' . tep_get_tax_rate_value($tax_class_array[$i]['id']) . ';' . "\n";
	}
}

$javaScript .= '$(document).ready(function (){' . "\n" .
	'updateGross();' . "\n" .
	'});' . "\n" .
	'</script>' . "\n";

EventManager::attachActionResponse($javaScript . $infoBox->draw(), 'html');
?>