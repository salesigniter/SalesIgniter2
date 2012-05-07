<?php
function updateProgressBar($name, $message) {
	$Check = Doctrine_Manager::getInstance()
		->getCurrentConnection()
		->fetchAssoc('select name from progress_bar where name="' . $name . '"');
	if (sizeof($Check) > 0){
		$query = 'update progress_bar set message = "' . $message . '" where name = "' . $name . '"';
	}
	else {
		$query = 'insert into progress_bar (message, name) values ("' . $message . '", "' . $name . '")';
	}
	Doctrine_Manager::getInstance()
		->getCurrentConnection()
		->exec($query);
}

if (!isset($_GET['language_id'])){
	$progressBarName = 'newLanguage';

	$Ftp = new SystemFTP();
	$Ftp->connect();

	if (sysConfig::exists('GOOGLE_API_SERVER_KEY') && sysConfig::get('GOOGLE_API_SERVER_KEY') != ''){
		$languages = sysLanguage::getGoogleLanguages();
	}
	$langCode = (isset($languages) ? $_POST['code'] : $_POST['code']);
	$langName = (isset($languages) ? $languages[$langCode] : $_POST['name']);

	$catalogAbsPath = sysConfig::getDirFsCatalog();
	$newLangPath = $catalogAbsPath . 'includes/languages/' . strtolower($langCode) . '/';
	$globalLangPath = $catalogAbsPath . 'includes/languages/english/';

	$exclude = array($catalogAbsPath . 'includes/languages');

	/*
	 * Search all folders for global.xml files
	 *
	 * @TODO: Update when application page specific language files are created
	 */
	$Directory = new RecursiveDirectoryIterator($catalogAbsPath);
	$Iterator = new RecursiveIteratorIterator($Directory);
	$Regex = new RegexIterator($Iterator, '/^.+global\.xml$/i', RegexIterator::GET_MATCH);

	updateProgressBar($progressBarName, 'Copying All Language Files');

	$files = array();
	foreach($Regex as $arr){
		$skipFile = false;
		/*
			 * Exclude any files inside the folders specified in the exclude array
			 */
		foreach($exclude as $excludeDir){
			if (stristr($arr[0], $excludeDir)){
				$skipFile = true;
				break;
			}
		}

		if ($skipFile === false){
			$Ftp->updateFileFromString(
				$newLangPath . str_replace(array($catalogAbsPath, 'language_defines/'), '', $arr[0]),
				sysLanguage::translateFile($arr[0], $langCode, $langName, true)
			);
		}
	}

	/*
	 * Copy the global file for the admin and set the permissions
	 */
	updateProgressBar($progressBarName, 'Translating File: ' . $newLangPath . 'admin/global.xml');
	$Ftp->updateFileFromString(
		$newLangPath . 'admin/global.xml',
		sysLanguage::translateFile($globalLangPath . 'admin/global.xml', $langCode, $langName, true)
	);

	/*
	 * Copy the global file for the catalog and set the permissions
	 */
	updateProgressBar($progressBarName, 'Translating File: ' . $newLangPath . 'catalog/global.xml');
	$Ftp->updateFileFromString(
		$newLangPath . 'catalog/global.xml',
		sysLanguage::translateFile($globalLangPath . 'catalog/global.xml', $langCode, $langName, true)
	);

	$success = false;
	if (is_dir($newLangPath)){
		$success = true;

		updateProgressBar($progressBarName, 'Copying settings file and applying changes');
		$langData = simplexml_load_file(
			$globalLangPath . 'settings.xml',
			'SimpleXMLExtended'
		);

		$langData->date_format->setCData($_POST['date_format']);
		$langData->date_format_short->setCData($_POST['date_format_short']);
		$langData->date_format_long->setCData($_POST['date_format_long']);
		$langData->date_time_format->setCData($_POST['date_time_format']);
		$langData->default_currency->setCData($_POST['default_currency']);
		$langData->html_params->setCData($_POST['html_params']);
		$langData->html_charset->setCData($_POST['html_charset']);
		$langData->name->setCData($langName);
		$langData->code->setCData($langCode);
		$langData->html_params->setCData('dir=ltr lang=' . $langCode);

		$Ftp->updateFileFromString($newLangPath . 'settings.xml', $langData->asXML());

		$newLang = new Languages();
		$newLang->code = $langCode;
		$newLang->name = $langName;
		$newLang->directory = strtolower($langCode);

		$newLang->save();

		if (sysConfig::exists('GOOGLE_API_SERVER_KEY') && sysConfig::get('GOOGLE_API_SERVER_KEY') != ''){
			$Translated = sysLanguage::translateText($langName, $newLang->languages_id);
			$newLang->name_real = $Translated[0];
		}
		else {
			$newLang->name_real = $langName;
		}
		$newLang->save();

		if (isset($_POST['translate_model'])){
			foreach($_POST['translate_model'] as $modelName){
				$Model = Doctrine_Core::getTable($modelName);
				$RecordInst = $Model->getRecordInstance();
				if (method_exists($RecordInst, 'newLanguageProcess')){
					updateProgressBar($progressBarName, 'Translating Description Table: ' . $modelName);
					$RecordInst->newLanguageProcess(Session::get('languages_id'), $newLang->languages_id);
				}
			}
		}
	}
}else{
	$Language = Doctrine_Query::create()
		->from('Languages')
		->where('languages_id = ?', $_GET['language_id'])
		->execute();

	$settingsFile = sysConfig::getDirFsCatalog() . 'includes/languages/' . $Language->directory . '/settings.xml';
	$langData = simplexml_load_file(
		$settingsFile,
		'SimpleXMLExtended'
	);

	$langData->date_format->setCData($_POST['date_format']);
	$langData->date_format_short->setCData($_POST['date_format_short']);
	$langData->date_format_long->setCData($_POST['date_format_long']);
	$langData->date_time_format->setCData($_POST['date_time_format']);
	$langData->default_currency->setCData($_POST['default_currency']);
	$langData->html_params->setCData($_POST['html_params']);
	$langData->html_charset->setCData($_POST['html_charset']);

	$fileObj = fopen($settingsFile, 'w+');
	if ($fileObj){
		ftruncate($fileObj, -1);
		fwrite($fileObj, $langData->asXML());
		fclose($fileObj);
	}
}

EventManager::attachActionResponse(array(
	'success' => true
), 'json');
