<?php
set_time_limit(0);

DataManagementModules::loadModule($_POST['module']);
$Module = DataManagementModules::getModule($_POST['module']);
$Module->setFormat($_POST['module_format']);
$Module->setAction($_POST['module_action']);
if (isset($_FILES['file_to_use'])){
	$Module->setImportFile($_FILES['file_to_use']['tmp_name']);
}
$Module->beforeActionProcess();
$Module->perform();
$Module->afterActionProcess();

EventManager::attachActionResponse('', 'html');
