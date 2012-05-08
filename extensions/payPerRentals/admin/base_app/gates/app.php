<?php
	$appContent = $App->getAppContentFile();

	if (isset($_GET['fID'])){
		$App->setInfoBoxId($_GET['fID']);
	}
sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE'));