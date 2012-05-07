<?php
$Languages = Doctrine_Core::getTable('Languages');
if (isset($_GET['language_id'])){
	$Language = $Languages->find((int)$_GET['language_id']);
}
else {
	$Language = $Languages->getRecord();
}

$CodeInput = htmlBase::newElement('input')
	->setLabel(sysLanguage::get('ENTRY_CODE'))
	->setLabelPosition('before')
	->setLabelSeparator('<br>')
	->setName('code');

$NameInput = htmlBase::newElement('input')
	->setLabel(sysLanguage::get('ENTRY_NAME'))
	->setLabelPosition('before')
	->setLabelSeparator('<br>')
	->setName('name');

$DateFormatInput = htmlBase::newElement('input')
	->setLabel(sysLanguage::get('ENTRY_DATE_FORMAT'))
	->setLabelPosition('before')
	->setLabelSeparator('<br>')
	->setName('date_format');

$DateFormatShortInput = htmlBase::newElement('input')
	->setLabel(sysLanguage::get('ENTRY_DATE_FORMAT_SHORT'))
	->setLabelPosition('before')
	->setLabelSeparator('<br>')
	->setName('date_format_short');

$DateFormatLongInput = htmlBase::newElement('input')
	->setLabel(sysLanguage::get('ENTRY_DATE_FORMAT_LONG'))
	->setLabelPosition('before')
	->setLabelSeparator('<br>')
	->setName('date_format_long');

$DateTimeFormatInput = htmlBase::newElement('input')
	->setLabel(sysLanguage::get('ENTRY_DATE_TIME_FORMAT'))
	->setLabelPosition('before')
	->setLabelSeparator('<br>')
	->setName('date_time_format');

$DefaultCurrencyInput = htmlBase::newElement('input')
	->setLabel(sysLanguage::get('ENTRY_DEFAULT_CURRENCY'))
	->setLabelPosition('before')
	->setLabelSeparator('<br>')
	->setName('default_currency');

$HtmlParamsInput = htmlBase::newElement('input')
	->setLabel(sysLanguage::get('ENTRY_HTML_PARAMS'))
	->setLabelPosition('before')
	->setLabelSeparator('<br>')
	->setName('html_params');

$HtmlCharsetInput = htmlBase::newElement('input')
	->setLabel(sysLanguage::get('ENTRY_HTML_CHARSET'))
	->setLabelPosition('before')
	->setLabelSeparator('<br>')
	->setName('html_charset');

$HiddenInput = htmlBase::newElement('input')
	->setType('hidden')
	->setName('filePath');

if (file_exists($Language->directory . '/settings.xml')){
	$langData = simplexml_load_file(
		$Language->directory . '/settings.xml',
		'SimpleXMLExtended'
	);

	$CodeInput->attr('readonly', 'readonly')->val((string)$langData->code);
	$NameInput->attr('readonly', 'readonly')->val((string)$langData->name);
	$DateFormatInput->val((string)$langData->date_format);
	$DateFormatShortInput->val((string)$langData->date_format_short);
	$DateFormatLongInput->val((string)$langData->date_format_long);
	$DateTimeFormatInput->val((string)$langData->date_time_format);
	$DefaultCurrencyInput->val((string)$langData->default_currency);
	$HtmlParamsInput->val((string)$langData->html_params);
	$HtmlCharsetInput->val((string)$langData->html_charset);
	$HiddenInput->val($Language->directory);
}

$infoBox = htmlBase::newElement('infobox');
$infoBox->setHeader('<b>' . ($Language->languages_id > 0 ? sysLanguage::get('TEXT_INFO_HEADING_EDIT') : sysLanguage::get('TEXT_INFO_HEADING_NEW')) . '</b>');
$infoBox->setButtonBarLocation('top');

$saveButton = htmlBase::newElement('button')->addClass('saveButton')->usePreset('save');
$cancelButton = htmlBase::newElement('button')->addClass('cancelButton')->usePreset('cancel');

$infoBox->addButton($saveButton)->addButton($cancelButton);

$infoBox->addContentRow($CodeInput->draw() . $HiddenInput->draw());
$infoBox->addContentRow($NameInput->draw());
$infoBox->addContentRow($DateFormatInput->draw());
$infoBox->addContentRow($DateFormatShortInput->draw());
$infoBox->addContentRow($DateFormatLongInput->draw());
$infoBox->addContentRow($DateTimeFormatInput->draw());
$infoBox->addContentRow($DefaultCurrencyInput->draw());
$infoBox->addContentRow($HtmlParamsInput->draw());
$infoBox->addContentRow($HtmlCharsetInput->draw());

if (!isset($_GET['language_id']) && sysConfig::exists('GOOGLE_API_SERVER_KEY') && sysConfig::get('GOOGLE_API_SERVER_KEY') != ''){
	$dbConn = $manager->getCurrentConnection();
	$importer = $dbConn->import;
	$Tables = $importer->listTables();
	$langTables = array();
	foreach($Tables as $tableName){
		if ($tableName == 'languages'){
			continue;
		}

		$TableColumns = $importer->listTableColumns($tableName);
		foreach($TableColumns as $columnName => $cInfo){
			if ($columnName == 'language_id' || $columnName == 'languages_id'){
				$langTables[] = $tableName;
			}
		}
	}

	$loadedModels = Doctrine_Core::getLoadedModelFiles();
	foreach($loadedModels as $modelName => $modelPath){
		$Model = Doctrine_Core::getTable($modelName);
		$RecordInst = $Model->getRecordInstance();
		if (method_exists($RecordInst, 'newLanguageProcess')){
			$modelPath = str_replace(sysConfig::getDirFsCatalog(), '', $modelPath);
			$extName = null;
			if (substr($modelPath, 0, 10) == 'extensions'){
				$pathArr = explode('/', $modelPath);
				$ext = $appExtension->getExtension($pathArr[1]);
				if ($ext){
					$extName = $ext->getExtensionName();
				}
				else {
					$extName = $pathArr[1];
				}
			}
			$langModels[] = array(
				'modelPath' => str_replace(sysConfig::getDirFsCatalog(), '', $modelPath),
				'modelName' => $modelName,
				'extName'   => $extName,
				'tableName' => $Model->getTableName()
			);
		}
	}

	$translateList = '<br><br><input type="checkbox" class="selectAll"><b><u>Select Extra Tanslations</u></b><br>';
	foreach($langModels as $mInfo){
		$showName = $mInfo['modelName'];
		if (is_null($mInfo['extName']) === false){
			$showName = $mInfo['extName'];
		}
		$translateList .= '<input type="checkbox" name="translate_model[]" value="' . $mInfo['modelName'] . '"> ' . $showName . ' ( ' . $mInfo['tableName'] . ' )<br>';
	}
	$translateList .= '<br>';

	$dropMenu = htmlBase::newElement('selectbox')->setName('toLanguage');
	foreach($googleLanguages as $langCode => $langName){
		$dropMenu->addOption($langCode, $langName);
	}

	$infoBox->addContentRow($dropMenu->draw() . '<br><small>* This may take a few minutes.</small>');
	$infoBox->addContentRow($translateList);
}

EventManager::attachActionResponse($infoBox->draw(), 'html');
