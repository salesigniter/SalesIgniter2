<?php
	$appContent = $App->getAppContentFile();
	if (isset($_GET['rID'])){
		$App->setInfoBoxId($_GET['rID']);
	}
sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE'));
