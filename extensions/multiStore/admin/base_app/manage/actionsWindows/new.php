<?php
$Stores = Doctrine_Core::getTable('Stores');
if (isset($_GET['store_id'])){
	$Store = $Stores->find((int)$_GET['store_id']);
	$boxHeading = sysLanguage::get('TEXT_INFO_HEADING_EDIT');
}
else {
	$Store = $Stores->getRecord();
	$boxHeading = sysLanguage::get('TEXT_INFO_HEADING_NEW');
}

$StoreNameInput = htmlBase::newInput()
	->setLabel(sysLanguage::get('TEXT_STORES_NAME'))
	->setLabelPosition('above')
	->setName('stores_name')
	->setValue($Store->stores_name);

$StoreDomainInput = htmlBase::newInput()
	->setLabel(sysLanguage::get('TEXT_STORES_DOMAIN'))
	->setLabelPosition('above')
	->setName('stores_domain')
	->setValue($Store->stores_domain);

$StoreSslDomainInput = htmlBase::newInput()
	->setLabel(sysLanguage::get('TEXT_STORES_SSL_DOMAIN'))
	->setLabelPosition('above')
	->setName('stores_ssl_domain')
	->setValue($Store->stores_ssl_domain);

$StoreEmailInput = htmlBase::newInput()
	->setLabel(sysLanguage::get('TEXT_STORES_EMAIL'))
	->setLabelPosition('above')
	->setName('stores_data[contact][email]')
	->setValue($Store->stores_data['contact']['email']);

$StoreDescriptionInput = htmlBase::newElement('ck_editor')
	->setLabel(sysLanguage::get('TEXT_STORES_DESCRIPTION'))
	->setLabelPosition('above')
	->setName('stores_data[description]')
	->attr('rows', '20')
	->attr('cols', '90')
	->html($Store->stores_data['description']);

$StoreTemplateInput = htmlBase::newSelectbox()
	->setLabel(sysLanguage::get('TEXT_STORES_TEMPLATE'))
	->setLabelPosition('above')
	->setName('stores_data[template]')
	->selectOptionByValue($Store->stores_data['template']);

$dir = new DirectoryIterator(sysConfig::getDirFsCatalog() . 'templates/');
$ignoreTemplates = array('email', 'help', 'help-text');
$templatesArray = array();
foreach($dir as $fileObj){
	if ($fileObj->isDot() || $fileObj->isDir() === false){
		continue;
	}
	if (in_array(strtolower($fileObj->getBasename()), $ignoreTemplates)){
		continue;
	}

	$StoreTemplateInput->addOption($fileObj->getBasename(), ucfirst($fileObj->getBasename()));
}

$DefaultStoreInput = htmlBase::newRadioGroup()
	->setLabel(sysLanguage::get('TEXT_STORES_IS_DEFAULT'))
	->setLabelPosition('above')
	->setName('is_default')
	->setChecked((int)$Store->is_default)
	->addInput(htmlBase::newRadio()
	->setValue('0')
	->setLabel(sysLanguage::get('TEXT_NO'))
	->setLabelPosition('after'))
	->addInput(htmlBase::newRadio()
	->setValue('1')
	->setLabel(sysLanguage::get('TEXT_YES'))
	->setLabelPosition('after'));

$HomeRedirectInput = htmlBase::newRadioGroup()
	->setLabel(sysLanguage::get('TEXT_STORES_HOME_REDIRECT_STORE_INFO'))
	->setLabelPosition('above')
	->setName('stores_data[home_send_to_infopage]')
	->setChecked((int)$Store->stores_data['home_send_to_infopage'])
	->addInput(htmlBase::newRadio()
	->setValue('0')
	->setLabel(sysLanguage::get('TEXT_NO'))
	->setLabelPosition('after'))
	->addInput(htmlBase::newRadio()
	->setValue('1')
	->setLabel(sysLanguage::get('TEXT_YES'))
	->setLabelPosition('after'));

$StoreCountryInput = htmlBase::newSelectbox()
	->setLabel(sysLanguage::get('TEXT_STORES_COUNTRY'))
	->setLabelPosition('above')
	->setName('stores_data[address][country]')
	->attr('data-zone_input_name', 'stores_data[address][zone]')
	->selectOptionByValue((int)$Store->stores_data['address']['country']);
$Countries = Doctrine_Core::getTable('Countries')
	->findAll();
foreach($Countries as $Country){
	$StoreCountryInput->addOption(
		$Country->countries_id,
		$Country->countries_name
	);
}

$Zones = Doctrine_Core::getTable('Zones')
	->findByZoneCountryId((int)$Store->stores_data['address']['country']);
if ($Zones->count() > 0){
	$StoreZoneInput = htmlBase::newSelectbox()
		->selectOptionByValue((int)$Store->stores_data['address']['zone']);
	foreach($Zones as $Zone){
		$StoreZoneInput->addOption($Zone->zone_id, $Zone->zone_name);
	}
}
else {
	$StoreZoneInput = htmlBase::newInput()
		->setValue($Store->stores_data['address']['zone']);
}
$StoreZoneInput
	->addClass('storeZone')
	->setName('stores_data[address][zone]')
	->setLabel(sysLanguage::get('TEXT_STORES_ZONE'))
	->setLabelPosition('above');

$StoreAddressInput = htmlBase::newInput()
	->setLabel(sysLanguage::get('TEXT_STORES_STREET_ADDRESS'))
	->setLabelPosition('above')
	->setName('stores_data[address][street]')
	->setValue($Store->stores_data['address']['street']);

$StorePostcodeInput = htmlBase::newInput()
	->setLabel(sysLanguage::get('TEXT_STORES_POSTCODE'))
	->setLabelPosition('above')
	->setName('stores_data[address][postcode]')
	->setValue($Store->stores_data['address']['postcode']);

$StoreTelephoneInput = htmlBase::newInput()
	->setLabel(sysLanguage::get('TEXT_STORES_TELEPHONE'))
	->setLabelPosition('above')
	->setName('stores_data[contact][telephone]')
	->setValue($Store->stores_data['contact']['telephone']);

$StoreGroupInput = htmlBase::newInput()
	->setLabel(sysLanguage::get('TEXT_STORES_GROUP'))
	->setLabelPosition('above')
	->setName('stores_data[group]')
	->setValue($Store->stores_data['group']);

$StoreOwnerInput = htmlBase::newInput()
	->setLabel(sysLanguage::get('TEXT_STORES_OWNER'))
	->setLabelPosition('above')
	->setName('stores_data[owner]')
	->setValue($Store->stores_data['owner']);

$StoreCurrencyInput = htmlBase::newSelectbox()
	->setLabel(sysLanguage::get('TEXT_STORES_DEFAULT_CURRENCY'))
	->setLabelPosition('above')
	->setName('stores_data[currency]')
	->selectOptionByValue($Store->stores_data['currency']);
foreach(sysCurrency::getCurrencies() as $currency){
	$StoreCurrencyInput->addOption($currency['code'], $currency['title']);
}

$StoreLanguageInput = htmlBase::newSelectbox()
	->setLabel(sysLanguage::get('TEXT_STORES_DEFAULT_LANGUAGE'))
	->setLabelPosition('above')
	->setName('stores_data[language]')
	->selectOptionByValue($Store->stores_data['language']);
foreach(sysLanguage::getLanguages() as $language){
	$StoreLanguageInput->addOption($language['id'], $language['showName'](' '));
}

$StoreGoogleMap = htmlBase::newElement('div')
	->setId('googleMap')
	->css(
	array(
		'width' => '400px',
		'height' => '400px'
	));

$Fieldset = htmlBase::newFieldsetFormBlock();
$Fieldset->setLegend($boxHeading);

$Fieldset->addBlock('mainInfo', 'General Info', array(
	array($StoreNameInput, $StoreTemplateInput),
	array($StoreDomainInput, $StoreSslDomainInput),
	array($StoreLanguageInput, $StoreCurrencyInput),
	//array($StoreGroupInput),
	array($DefaultStoreInput, $HomeRedirectInput),
	array($StoreDescriptionInput)
));

$Fieldset->addBlock('contactInfo', 'Contact Info', array(
	array($StoreOwnerInput),
	array($StoreEmailInput),
	array($StoreTelephoneInput)
));

$Fieldset->addBlock('locationInfo', 'Location Info', array(
	array($StoreAddressInput, $StorePostcodeInput),
	array($StoreZoneInput, $StoreCountryInput)
));
/*
function addCategoryChildren(&$selectBox, $Category){
	if ($Category->Children && $Category->Children->count() > 0){
		foreach($Category->Children as $Child){
			$selectBox->addOption(
				$Child->categories_id,
				$Child->Description[sysLanguage::getId()]->categories_name
			);
			addCategoryChildren($selectBox, $Child);
		}
	}
}

$AllCategories = htmlBase::newSelectbox()
	->addClass('systemCategories')
	->css('height', '400px')
	->attr('multiple', 'multiple');
$Categories = Doctrine_Core::getTable('Categories')
	->findAll();
foreach($Categories as $Category){
	$AllCategories->addOption(
		$Category->categories_id,
		$Category->Description[sysLanguage::getId()]->categories_name
	);
	addCategoryChildren($AllCategories, $Category);
}

$StoreCategories = htmlBase::newSelectbox()
	->addClass('storeCategories')
	->css('height', '400px')
	->attr('multiple', 'multiple');

$CategoriesFieldset = htmlBase::newFieldsetFormBlock();
$CategoriesFieldset->setLegend('Stores Product Categories');
$CategoriesFieldset->addBlock('categorySelect', 'Select Categories', array(
	array(
		$AllCategories,
		array(
			htmlBase::newButton()->usePreset('add')->disable(),
			htmlBase::newButton()->usePreset('remove')->disable()
		),
		$StoreCategories)
));
*/
$checkedCats = array();
foreach($Store->CategoriesToStores as $Category){
	$checkedCats[] = $Category->categories_id;
}
$categoriesList = tep_get_category_tree_list('0', $checkedCats);
$CategoriesFieldset = htmlBase::newFieldsetFormBlock();
$CategoriesFieldset->setLegend('Stores Product Categories');
$CategoriesFieldset->addBlock('categorySelect', 'Select Categories', array(
	array(
		htmlBase::newElement('div')
			->html($categoriesList)
	)
));

$Infobox = htmlBase::newActionWindow()
	->addButton(htmlBase::newElement('button')
	->addClass('saveButton')
	->usePreset('save'))
	->addButton(htmlBase::newElement('button')
	->addClass('cancelButton')
	->usePreset('cancel'))
	->setContent($Fieldset->draw() . $CategoriesFieldset->draw());

EventManager::attachActionResponse($Infobox->draw(), 'html');
