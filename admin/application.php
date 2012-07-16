<?php
/*
	Sales Igniter E-Commerce System
	Version: 1
	
	I.T. Web Experts
	http://www.itwebexperts.com

	Copyright (c) 2010 I.T. Web Experts

	This script and it's source is not redistributable
*/

require('includes/application_top.php');

$action = (isset($_GET['action']) ? $_GET['action'] : '');
$actionWindow = (isset($_GET['window']) ? $_GET['window'] : '');

$pageFunctionFiles = $App->getFunctionFiles($App->getAppPage());
if (!empty($pageFunctionFiles)){
	foreach($pageFunctionFiles as $filePath){
		require($filePath);
	}
}

require($App->getAppFile());

if (!empty($action)){
	EventManager::notify('ApplicationActionsBeforeExecute', $action);

	if ($action == 'getCountryZones'){
		if (isset($_GET['country_id']) && !empty($_GET['country_id'])){
			$ZonesArr = array();
			$Zones = Doctrine_Core::getTable('Zones')
				->findByZoneCountryId((int) $_GET['country_id']);
			if ($Zones->count() > 0){
				foreach($Zones as $Zone){
					$ZonesArr[] = array(
						'id' => $Zone->zone_id,
						'text' => $Zone->zone_name
					);
				}
			}

			EventManager::attachActionResponse(array(
				'success' => true,
				'zones' => $ZonesArr
			), 'json');
		}
	}
	elseif ($action == 'getActionWindow'){
		if (isset($_GET['appExt'])){
			$checkDirs = array(
				sysConfig::getDirFsCatalog() . 'clientData/extensions/' . $_GET['appExt'] . '/admin/base_app/' . $App->getAppName() . '/actionsWindows/',
				sysConfig::getDirFsCatalog() . 'extensions/' . $_GET['appExt'] . '/admin/base_app/' . $App->getAppName() . '/actionsWindows/'
			);
		}else{
			$checkDirs = array(
				sysConfig::getDirFsCatalog() . 'clientData/applications/' . $App->getAppName() . '/actionsWindows/',
				sysConfig::getDirFsAdmin() . 'applications/' . $App->getAppName() . '/actionsWindows/'
			);
		}

		foreach($checkDirs as $dir){
			if (file_exists($dir . $actionWindow . '.php')){
				require($dir . $actionWindow . '.php');
				break;
			}
		}
	}else{
		$actionFiles = $App->getActionFiles($action);
		foreach($actionFiles as $file){
			require($file);
		}
	}

	EventManager::notify('ApplicationActionsAfterExecute');
}

EventManager::notify('ApplicationTemplateBeforeInclude');

require(sysConfig::get('DIR_FS_TEMPLATE') . '/main_page.tpl.php');

EventManager::notify('ApplicationTemplateAfterInclude');

require(sysConfig::getDirFsCatalog() . 'includes/application_bottom.php');
?>