<?php
/*
$numberInput = htmlBase::newInput()
->setLabel(sysLanguage::get('ENTRY_NUMBER'))
->setLabelPosition('bottom')
->setName('customers_number')
->setRequired(false)
->attr('size', 12)
->attr('maxlength', 12)
->val((isset($_POST['customers_number']) ? $_POST['customers_number'] : $Customer->customers_number));

$frozenInput = htmlBase::newCheckbox()
->setLabel(sysLanguage::get('ENTRY_FROZEN'))
->setLabelPosition('right')
->setName('customers_account_frozen')
->setRequired(false)
->setChecked((isset($_POST['customers_account_frozen']) ? true : ($Customer->customers_account_frozen == 1)))
->val(1);
*/
$usernameInput = htmlBase::newInput()
->setLabel(sysLanguage::get('ENTRY_USERNAME'))
->setLabelPosition('bottom')
->setName('customers_username')
->setRequired(true)
->val((isset($_POST['customers_username']) ? $_POST['customers_username'] : $Customer->customers_username));

$passwordInput = htmlBase::newInput()
->setLabel(sysLanguage::get('ENTRY_PASSWORD'))
->setLabelPosition('bottom')
->setName('customers_password')
->setRequired(false);

$firstNameInput = htmlBase::newInput()
->setLabel(sysLanguage::get('ENTRY_FIRST_NAME'))
->setLabelPosition('bottom')
->setName('customers_firstname')
->setRequired(true)
->attr('size', 32)
->attr('maxlength', 32)
->val((isset($_POST['customers_firstname']) ? $_POST['customers_firstname'] : $Customer->customers_firstname));

$lastNameInput = htmlBase::newInput()
->setLabel(sysLanguage::get('ENTRY_LAST_NAME'))
->setLabelPosition('bottom')
->setName('customers_lastname')
->setRequired(true)
->attr('size', 32)
->attr('maxlength', 32)
->val((isset($_POST['customers_lastname']) ? $_POST['customers_lastname'] : $Customer->customers_lastname));

$emailAddressInput = htmlBase::newElement('email')
->setLabel(sysLanguage::get('ENTRY_EMAIL_ADDRESS'))
->setLabelPosition('bottom')
->setName('customers_email_address')
->setRequired(true)
->attr('maxlength', 96)
->val((isset($_POST['customers_email_address']) ? $_POST['customers_email_address'] : $Customer->customers_email_address));

$streetAddressInput = htmlBase::newInput()
->setLabel(sysLanguage::get('ENTRY_STREET_ADDRESS'))
->setLabelPosition('bottom')
->setName('entry_street_address')
->setRequired(true)
->attr('size', 64)
->attr('maxlength', 64)
->val((isset($_POST['entry_street_address']) ? $_POST['entry_street_address'] : $Customer->AddressBook[0]->entry_street_address));

$streetAddress2Input = htmlBase::newInput()
->setLabel(sysLanguage::get('ENTRY_STREET_ADDRESS') . ' 2')
->setLabelPosition('bottom')
->setName('entry_street_address2')
->setRequired(true)
->attr('size', 64)
->attr('maxlength', 64)
->val((isset($_POST['entry_street_address2']) ? $_POST['entry_street_address2'] : ''));

$postcodeInput = htmlBase::newInput()
->setLabel(sysLanguage::get('ENTRY_POST_CODE'))
->setLabelPosition('bottom')
->setName('entry_postcode')
->setRequired(true)
->attr('size', 10)
->attr('maxlength', 10)
->val((isset($_POST['entry_postcode']) ? $_POST['entry_postcode'] : $Customer->AddressBook[0]->entry_postcode));

$cityInput = htmlBase::newInput()
->setLabel(sysLanguage::get('ENTRY_CITY'))
->setLabelPosition('bottom')
->setName('entry_city')
->setRequired(true)
->attr('size', 32)
->attr('maxlength', 32)
->val((isset($_POST['entry_city']) ? $_POST['entry_city'] : $Customer->AddressBook[0]->entry_city));

/*
$telephoneInput = htmlBase::newInput()
->setLabel(sysLanguage::get('ENTRY_TELEPHONE_NUMBER'))
->setLabelPosition('bottom')
->setName('customers_telephone')
->attr('size', 32)
->attr('maxlength', 32)
->val((isset($_POST['customers_telephone']) ? $_POST['customers_telephone'] : $Customer->customers_telephone));

$notesInput = htmlBase::newTextarea()
->setLabel(sysLanguage::get('ENTRY_NOTES_INPUT'))
->setLabelPosition('bottom')
->setName('customers_notes')
->val((isset($_POST['customers_notes']) ? $_POST['customers_notes'] : $Customer->customers_notes));

$faxInput = htmlBase::newInput()
->setLabel(sysLanguage::get('ENTRY_FAX_NUMBER'))
->setLabelPosition('bottom')
->setName('customers_fax')
->attr('size', 32)
->attr('maxlength', 32)
->val((isset($_POST['customers_fax']) ? $_POST['customers_fax'] : $Customer->customers_fax));
*/

$countryInput = htmlBase::newSelectbox()
->attr('data-zone_input_name', 'entry_state')
->setLabel(sysLanguage::get('ENTRY_COUNTRY'))
->setLabelPosition('bottom')
->setName('country')
->setRequired(true)
->selectOptionByValue((isset($_POST['country']) ? $_POST['country'] : $Customer->AddressBook[0]->entry_country_id));
//$countryInput->addOption('', sysLanguage::get('PULL_DOWN_DEFAULT'));
$countries = tep_get_countries();
for($i = 0, $n = sizeof($countries); $i < $n; $i++){
	$countryInput->addOption($countries[$i]['id'], $countries[$i]['text']);
}
/*
if (sysConfig::get('ACCOUNT_GENDER') == 'true'){
	$genderSet = htmlBase::newRadio()
	->addGroup(array(
		'name'    => 'customers_gender',
		'separator' => '<br>',
		'checked' => (isset($_POST['customers_gender']) ? $_POST['customers_gender'] : $Customer->customers_gender),
		'data'    => array(
			array(
				'labelPosition' => 'right',
				'label' => sysLanguage::get('FEMALE'),
				'value' => 'f'
			),
			array(
				'labelPosition' => 'right',
				'label' => sysLanguage::get('MALE'),
				'value' => 'm'
			)
		)
	));
}
*/
//if (sysConfig::get('ACCOUNT_STATE') == 'true'){
	$stateInput = htmlBase::newInput()
	->setLabel(sysLanguage::get('ENTRY_STATE'))
	->setLabelPosition('bottom')
	->setName('entry_state')
	->attr('id', 'state')
	->val((isset($_POST['entry_state']) ? $_POST['entry_state'] : $Customer->AddressBook[0]->entry_state));
//}

/*
if (sysConfig::get('ACCOUNT_DOB') == 'true'){
	$dobInput = htmlBase::newInput()
	->setLabel(sysLanguage::get('ENTRY_DATE_OF_BIRTH'))
	->setLabelPosition('bottom')
	->setName('customers_dob')
	->setId('customers_dob')
	->val((isset($_POST['customers_dob']) ? $_POST['customers_dob'] : $Customer->customers_dob->format(sysLanguage::getDateFormat('short'))));
}
*/
//if (sysConfig::get('ACCOUNT_COMPANY') == 'true'){
	$companyInput = htmlBase::newInput()
	->setLabel(sysLanguage::get('ENTRY_COMPANY'))
	->setLabelPosition('bottom')
	->setName('entry_company')
	->val((isset($_POST['entry_company']) ? $_POST['entry_company'] : $Customer->AddressBook[0]->entry_company));
//}
/*
if (sysConfig::get('ACCOUNT_VAT_NUMBER') == 'true'){
	$vatInput = htmlBase::newInput()
	->setLabel(sysLanguage::get('ENTRY_VAT'))
	->setLabelPosition('bottom')
	->setName('entry_vat')
	->val((isset($_POST['entry_vat']) ? $_POST['entry_vat'] : $Customer->AddressBook[0]->entry_vat));
}

if (sysConfig::get('ACCOUNT_FISCAL_CODE') == 'true'){
	$cifInput = htmlBase::newInput()
	->setLabel(sysLanguage::get('ENTRY_CIF'))
	->setLabelPosition('bottom')
	->setName('entry_cif')
	->val((isset($_POST['entry_cif']) ? $_POST['entry_cif'] : $Customer->AddressBook[0]->entry_cif));
}

if (sysConfig::get('ACCOUNT_CITY_BIRTH') == 'true'){
	$cityBirthInput = htmlBase::newInput()
	->setLabel(sysLanguage::get('ENTRY_CITY_BIRTH'))
	->setLabelPosition('bottom')
	->setName('customers_city_birth')
	->val((isset($_POST['customers_city_birth']) ? $_POST['customers_city_birth'] : $Customer->customers_city_birth));
}

if (sysConfig::get('ACCOUNT_SUBURB') == 'true'){
	$suburbInput = htmlBase::newInput()
	->setLabel(sysLanguage::get('ENTRY_SUBURB'))
	->setLabelPosition('bottom')
	->setName('entry_suburb')
	->val((isset($_POST['entry_suburb']) ? $_POST['entry_suburb'] : $Customer->AddressBook[0]->entry_suburb));
}
*/
$languageInput = htmlBase::newSelectbox()
->setName('customers_language')
->selectOptionByValue((isset($_POST['customers_language']) ? $_POST['customers_language'] : $Customer->language_id))
->setLabel('Preferred Language')
->setLabelPosition('bottom');
foreach(sysLanguage::getLanguages() as $lInfo){
	$languageInput->addOption($lInfo['id'], $lInfo['showName'](' '));
}
/*
 * Build the owner block -- BEGIN
 */
$AccountOwnerBlock = htmlBase::newFieldsetFormBlock()
->setLegend('Account Owner Information')
->addBlock('main', 'Owner Information', array(
	array($firstNameInput, $lastNameInput),
	array($emailAddressInput)/*,
	array($genderSet, $dobInput, $cityBirthInput)*/
))
->addBlock('account', 'Account Information', array(
	array($usernameInput, $passwordInput),
	array($languageInput)/*,
	array($numberInput, $frozenInput, )*/
))/*
->addBlock('contact', 'Contact Information', array(
	array($telephoneInput, $faxInput)
))*/;
/*
 * Build the owner block -- END
 */

/*
	 * Build the company table -- BEGIN
	 */
if (isset($companyInput)){
	$companyTable = htmlBase::newElement('table')
	->setCellPadding(3)
	->setCellSpacing(0);

	$companyTable->addBodyRow(array(
		'columns' => array(
			array(
				'addCls' => 'main',
				'text'   => sysLanguage::get('ENTRY_COMPANY')
			),
			array(
				'addCls' => 'main',
				'text'   => $companyInput
			)
		)
	));
	$CompanyInfoFieldSet = htmlBase::newFieldset()
	->setLegend(sysLanguage::get('CATEGORY_COMPANY'))
	->append($companyTable);
}
/*
	 * Build the company table -- END
	 */

/*
	 * Build the address table -- BEGIN
	 */
$AddressBookBlock = htmlBase::newFieldsetFormBlock()
->setLegend('Address Book')
->addBlock('main', 'Primary Address', array(
	array($streetAddressInput, $streetAddress2Input),
	array($cityInput, $stateInput, $postcodeInput),
	array(/*$suburbInput,*/ $countryInput)/*,
	array($cifInput, $vatInput)*/
));
/*
	 * Build the address table -- END
	 */

echo $AccountOwnerBlock->draw();

$contents = EventManager::notifyWithReturn('CustomerInfoAddTableContainer', $Customer);
if (!empty($contents)){
	foreach($contents as $content){
		echo $content;
	}
}

echo $AddressBookBlock->draw();
