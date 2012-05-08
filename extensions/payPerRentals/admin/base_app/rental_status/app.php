<?php
	$appContent = $App->getAppContentFile();
	if ($App->getAppPage() == 'default'){
		$App->addJavascriptFile('ext/jQuery/external/iColorPicker/jquery.icolorpicker.js');
	}
	if (isset($_GET['rID'])){
		$App->setInfoBoxId($_GET['rID']);
	}
sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE'));
