<?php
if (isset($_GET['customer_id'])){
	$cID = $_GET['customer_id'];
}

require(sysConfig::getDirFsCatalog() . 'includes/modules/dataManagementModules/modules.php');
DataManagementModules::loadModule('customers');
$ExportModule = DataManagementModules::getModule('customers');

include(sysConfig::getDirFsCatalog() . 'includes/functions/crypt.php');

$appContent = $App->getAppContentFile();

if ($App->getAppPage() == 'new'){
	$userAccount = new rentalStoreUser($cID);
	$userAccount->loadPlugins();
	if (isset($cID)){
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_EDIT'));
	}
	else {
		sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_NEW'));
	}
}
else {
	sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE_DEFAULT'));
}
