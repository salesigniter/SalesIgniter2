<?php
$Installer = new ModuleInstaller($_GET['moduleType'], $_GET['module'], (isset($_GET['extName']) ? $_GET['extName'] : null), (isset($_GET['modulePath']) ? $_GET['modulePath'] : null));
$Installer->install();

if (SesRequestInfo::isAjax() === true){
	EventManager::attachActionResponse(array(
		'success' => true
	), 'json');
}
else {
	EventManager::attachActionResponse(itw_app_link(tep_get_all_get_params(array('action', 'module', 'modulePath'))), 'redirect');
}
?>