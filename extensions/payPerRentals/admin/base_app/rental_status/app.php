<?php
	$appContent = $App->getAppContentFile();
	if ($App->getAppPage() == 'default'){
		$App->addJavascriptFile('ext/jQuery/external/iColorPicker/jquery.icolorpicker.js');
	}

sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE'));
