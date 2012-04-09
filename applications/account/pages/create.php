<?php
$hasPost = (isset($_POST) && !empty($_POST));

$BillingPasswordInput = htmlBase::newElement('input')
	->setType('password')
	->attr('maxlength', '40')
	->setName('password')
	->setRequired(true);

$BillingPasswordConfirmInput = htmlBase::newElement('input')
	->setType('password')
	->attr('maxlength', '40')
	->setName('confirmation')
	->setRequired(true);
if (sysConfig::get('ACCOUNT_NEWSLETTER') == 'true'){
	$NewsletterInput = htmlBase::newElement('checkbox')
		->setLabel(sysLanguage::get('ENTRY_NEWSLETTER'))
		->setLabelPosition('before')
		->setName('newsletter')
		->setValue('1');
}

$FormTable = htmlBase::newElement('formTable');

if (sysConfig::get('ACCOUNT_COMPANY') == 'true'){
	$CompanyInput = htmlBase::newElement('input')
		->setName('company')
		->setRequired((sysConfig::get('ACCOUNT_COMPANY_REQUIRED') == 'true'));
	if ($hasPost === true){
		$CompanyInput->val($_POST['company']);
	}
	$FormTable->addRow(sysLanguage::get('ENTRY_COMPANY'));
	$FormTable->addRow($CompanyInput);
}

$EmailInput = htmlBase::newElement('input')
	->setType('email')
	->setName('email_address')
	->setRequired(true);
if ($hasPost === true){
	$EmailInput->val($_POST['email_address']);
}

$FormTable->addRow(sysLanguage::get('ENTRY_EMAIL_ADDRESS'));
$FormTable->addRow($EmailInput);

if (sysConfig::get('ACCOUNT_GENDER') == 'true'){
	$GenderInput = htmlBase::newElement('radio')
		->addGroup(array(
		'name' => 'gender',
		'checked' => ($hasPost === true ? $_POST['gender'] : 'm'),
		'required' => (sysConfig::get('ACCOUNT_GENDER_REQUIRED') == 'true'),
		'data' => array(
			array('id' => 'm', 'text' => sysLanguage::get('MALE')),
			array('id' => 'f', 'text' => sysLanguage::get('FEMALE'))
		)
	));
	$FormTable->addRow(sysLanguage::get('ENTRY_GENDER'));
	$FormTable->addRow($GenderInput);
}
if (sysConfig::get('ACCOUNT_DOB') == 'true'){
	$DateOfBirthInput = htmlBase::newElement('input')
		->setType('date')
		->setName('dob')
		->setRequired((sysConfig::get('ACCOUNT_DOB_REQUIRED') == 'true'));
	if ($hasPost === true){
		$DateOfBirthInput->val($_POST['dob']);
	}

	$FormTable->addRow(sysLanguage::get('ENTRY_DATE_OF_BIRTH'));
	$FormTable->addRow($DateOfBirthInput);
}

$FirstNameInput = htmlBase::newElement('input')
	->setName('firstname')
	->setRequired(true);

$LastNameInput = htmlBase::newElement('input')
	->setName('lastname')
	->setRequired(true);
if ($hasPost === true){
	$FirstNameInput->val($_POST['firstname']);
	$LastNameInput->val($_POST['lastname']);
}

$FormTable->addRow(sysLanguage::get('ENTRY_FIRST_NAME'), sysLanguage::get('ENTRY_LAST_NAME'));
$FormTable->addRow($FirstNameInput, $LastNameInput);

$StreetAddressInput = htmlBase::newElement('input')
	->setName('street_address')
	->setRequired(true);
if ($hasPost === true){
	$StreetAddressInput->val($_POST['street_address']);
}
$FormTable->addRow(sysLanguage::get('ENTRY_STREET_ADDRESS'));
$FormTable->addRow($StreetAddressInput);

if (sysConfig::get('ACCOUNT_SUBURB') == 'true'){
	$SuburbInput = htmlBase::newElement('input')
		->setName('suburb')
		->setRequired((sysConfig::get('ACCOUNT_SUBURB_REQUIRED') == 'true'));
	if ($hasPost === true){
		$SuburbInput->val($_POST['suburb']);
	}
	$FormTable->addRow(sysLanguage::get('ENTRY_SUBURB'));
	$FormTable->addRow($SuburbInput);
}

if (sysConfig::get('ACCOUNT_FISCAL_CODE') == 'true'){
	$FiscalCodeInput = htmlBase::newElement('input')
		->setName('fiscal_code')
		->setRequired((sysConfig::get('ACCOUNT_FISCAL_CODE_REQUIRED') == 'true'));
	if ($hasPost === true){
		$FiscalCodeInput->val($_POST['fiscal_code']);
	}
	$FormTable->addRow(sysLanguage::get('ENTRY_FISCAL_CODE'));
	$FormTable->addRow($FiscalCodeInput);
}

if (sysConfig::get('ACCOUNT_VAT_NUMBER') == 'true'){
	$VatNumberInput = htmlBase::newElement('input')
		->setName('vat_number')
		->setRequired((sysConfig::get('ACCOUNT_VAT_NUMBER_REQUIRED') == 'true'));
	if ($hasPost === true){
		$VatNumberInput->val($_POST['vat_number']);
	}
	$FormTable->addRow(sysLanguage::get('ENTRY_VAT_NUMBER'));
	$FormTable->addRow($VatNumberInput);
}

if (sysConfig::get('ACCOUNT_CITY_BIRTH') == 'true'){
	$CityBirthInput = htmlBase::newElement('input')
		->setName('city_birth')
		->setRequired((sysConfig::get('ACCOUNT_CITY_BIRTH_REQUIRED') == 'true'));
	if ($hasPost === true){
		$CityBirthInput->val($_POST['city_birth']);
	}
	$FormTable->addRow(sysLanguage::get('ENTRY_CITY_BIRTH'));
	$FormTable->addRow($CityBirthInput);
}

$CityInput = htmlBase::newElement('input')
	->setName('city')
	->setRequired(true);
if ($hasPost === true){
	$CityInput->val($_POST['city']);
}
$FormTable->addRow(sysLanguage::get('ENTRY_CITY'));
$FormTable->addRow($CityInput);

if (sysConfig::get('ACCOUNT_STATE') == 'true'){
	$StateInput = htmlBase::newElement('input')
		->setId('state')
		->setName('state')
		->setRequired((sysConfig::get('ACCOUNT_STATE_REQUIRED') == 'true'));
	if ($hasPost === true){
		$StateInput->val($_POST['state']);
	}
	$FormTable->addRow(sysLanguage::get('ENTRY_STATE'));
	$FormTable->addRow($StateInput);
}

$PostcodeInput = htmlBase::newElement('input')
	->setName('postcode')
	->setRequired((sysConfig::get('ACCOUNT_POSTCODE_REQUIRED') == 'true'));
if ($hasPost === true){
	$PostcodeInput->val($_POST['postcode']);
}
$FormTable->addRow(sysLanguage::get('ENTRY_POST_CODE'));
$FormTable->addRow($PostcodeInput);

$FormTable->addRow(sysLanguage::get('ENTRY_COUNTRY'));
$FormTable->addRow(tep_get_country_list('country', ($hasPost === true ? $_POST['country'] : sysLanguage::get('STORE_COUNTRY'))) . '&nbsp;' . '<a style="display: inline-block;" tooltip="Input Required" class="ui-icon ui-icon-gear ui-icon-required"></a>');

if (sysConfig::get('ACCOUNT_TELEPHONE') == 'true'){
	$TelephoneNumberInput = htmlBase::newElement('input')
		->setType('telephone')
		->setName('telephone')
		->setRequired((sysConfig::get('ACCOUNT_TELEPHONE_REQUIRED') == 'true'));
	if ($hasPost === true){
		$TelephoneNumberInput->val($_POST['telephone']);
	}
	$FormTable->addRow(sysLanguage::get('ENTRY_TELEPHONE_NUMBER'));
	$FormTable->addRow($TelephoneNumberInput);
}
if (sysConfig::get('ACCOUNT_CELLPHONE') == 'true'){
	$CellNumberInput = htmlBase::newElement('input')
		->setType('telephone')
		->setName('cellphone')
		->setRequired((sysConfig::get('ACCOUNT_CELLPHONE_REQUIRED') == 'true'));
	if ($hasPost === true){
		$CellNumberInput->val($_POST['cellphone']);
	}
	$FormTable->addRow(sysLanguage::get('ENTRY_CELLPHONE_NUMBER'));
	$FormTable->addRow($CellNumberInput);
	$FormTable->addRow(sysLanguage::get('ENTRY_CELLPHONE_CARRIER'));

	$Qcarriers = Doctrine_Query::create()
		->from('SmsCarriers')
		->orderBy('carrier_name')
		->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
	$carrierInput = htmlBase::newElement('selectbox')
		->setName('cellphone_carrier')
		->setRequired((sysConfig::get('ACCOUNT_CELLPHONE_REQUIRED') == 'true'));
	foreach($Qcarriers as $cInfo){
		$carrierInput->addOption($cInfo['carrier_id'], $cInfo['carrier_name']);
	}
	$FormTable->addRow($carrierInput);
}
ob_start();

echo '<table border="0" width="100%" cellspacing="0" cellpadding="2">
		<tr id="logInRow"' . ($userAccount->isLoggedIn() === true ? ' style="display:none"' : '') . '>
			<td class="main">' . sysLanguage::get('TEXT_ALREADY_HAVE_ACCOUNT') . '&nbsp;&nbsp;' .
	htmlBase::newElement('a')
		->setHref(itw_app_link(null, 'account', 'login', 'SSL'))
		->html(sysLanguage::get('TEXT_BUTTON_LOGIN'))
		->attr('id', 'loginButton')
		->draw() .
	'</td>
		</tr>
	</table>';
?>
<div class="">
	<div class="" style="margin-top:10px;margin-bottom:10px;line-height:2em;"><?php echo sysLanguage::get('TEXT_ADDRESS'); ?></div>
	<?php
	echo $FormTable->draw();
	?>
</div>
<div class="">
	<div class="" style="margin-top:10px;margin-bottom:10px;line-height:2em;"><?php echo sysLanguage::get('TEXT_ACCOUNT_SETTINGS'); ?></div>
	<table class="accountSettings" cellpadding="0" cellspacing="0" border="0" style="margin:.3em;">
		<tr>
			<td><?php echo sysLanguage::get('ENTRY_PASSWORD'); ?></td>
			<td><?php echo $BillingPasswordInput->draw(); ?></td>
			<td>
				<div id="pstrength_password"></div>
			</td>
		</tr>
		<tr>
			<td><?php echo sysLanguage::get('ENTRY_PASSWORD_CONFIRMATION'); ?></td>
			<td colspan="2"><?php echo $BillingPasswordConfirmInput->draw(); ?></td>
		</tr>
		<?php if (sysConfig::get('ACCOUNT_NEWSLETTER') == 'true'){ ?>
		<tr>
			<td colspan="3"><?php echo $NewsletterInput->draw(); ?></td>
		</tr>
		<?php } ?>
	</table>
	<?php
	if (sysConfig::get('TERMS_CONDITIONS_CREATE_ACCOUNT') == 'true'){
		?>
		<div onclick="window.document.create_account.terms.checked = !window.document.create_account.terms.checked;" style="margin-top:.5em;padding:.5em;text-align:center;">
			<b><?php echo sprintf(sysLanguage::get('ENTRY_PRIVACY_AGREEMENT'), itw_app_link('appExt=infoPages', 'show_page', 'conditions') . '" onclick="popupWindow(\'' . itw_app_link('appExt=infoPages&dialog=true', 'show_page', 'conditions', 'SSL') . '\',\'800\',\'600\');return false;'); ?></b>&nbsp;<?php echo tep_draw_checkbox_field('terms', '1', false, 'onclick="window.document.create_account.terms.checked = !window.document.create_account.terms.checked;"'); ?>
		</div>
		<?php
	}
	else {
		echo $htmlTerms = htmlBase::newElement('input')
			->setType('hidden')
			->setName('terms')
			->attr('checked', true)
			->setValue('1')
			->draw();
	}
	?>
</div>
<?php
$pageContents = ob_get_contents();
ob_end_clean();

$pageTitle = sysLanguage::get('HEADING_TITLE_CREATE');

$pageButtons = htmlBase::newElement('button')
	->usePreset('continue')
	->setType('submit')
	->draw();

$pageContents = htmlBase::newElement('form')
	->setAction(itw_app_link('action=createAccount', 'account', 'create', 'SSL'))
	->setName('create_account')
	->setMethod('post')
	->html($pageContents)
	->draw();

$pageContent->set('pageTitle', $pageTitle);
$pageContent->set('pageContent', $pageContents);
$pageContent->set('pageButtons', $pageButtons);
?>