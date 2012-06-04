<?php
require(sysConfig::getDirFsCatalog() . 'includes/classes/extensionInstaller.php');

$toUninstall = explode(',', $_GET['extension']);
$removeSettings = true;
foreach($toUninstall as $extName){
	$extension = basename($extName);

	$extensionDir = sysConfig::getDirFsCatalog() . 'extensions/' . $extension . '/';
	if (file_exists($extensionDir . 'install/install.php')){
		$className = $extension . 'Install';
		if (!class_exists($className)){
			include($extensionDir . 'install/install.php');
		}
		$ext = new $className;
		$ext->uninstall($removeSettings);
	}
	else {
		$installer = new extensionInstaller($extension);
		$installer->uninstall($removeSettings);
	}
}

EventManager::attachActionResponse(itw_app_link(tep_get_all_get_params(array('action'))), 'redirect');
