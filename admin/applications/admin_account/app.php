<?php
if ($App->getAppPage() == 'editAccount' && Session::exists('confirm_account') === false){
	if (empty($_POST)){
		tep_redirect(itw_app_link(null, null, 'default', 'SSL'));
	}
}

$appContent = $App->getAppContentFile();
sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE'));
