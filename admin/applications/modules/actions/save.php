<?php
$moduleCode = $_GET['module'];
$moduleType = $_GET['moduleType'];
$modulePath = $_GET['modulePath'];

switch($moduleType){
	case 'accountsReceivable':
		$Module = AccountsReceivableModules::getModule($moduleCode, true);
		$moduleDir = 'accountsReceivableModules';
		break;
	case 'productType':
		$Module = ProductTypeModules::getModule($moduleCode, true);
		$moduleDir = 'productTypeModules';
		break;
	case 'purchaseType':
		$Module = PurchaseTypeModules::getModule($moduleCode, true);
		$moduleDir = 'purchaseTypeModules';
		break;
	case 'orderShipping':
		$Module = OrderShippingModules::getModule($moduleCode, true);
		$moduleDir = 'orderShippingModules';
		break;
	case 'orderTotal':
		$Module = OrderTotalModules::getModule($moduleCode, true);
		$moduleDir = 'orderTotalModules';
		break;
	case 'orderPayment':
		$Module = OrderPaymentModules::getModule($moduleCode, true);
		$moduleDir = 'orderPaymentModules';
		break;
	case 'cronjob':
		$Module = CronJobModules::getModule($moduleCode, true);
		$moduleDir = 'cronjobModules';
		break;
}
$ModuleConfig = $Module->getConfig();

$Modules = Doctrine_Core::getTable('Modules')
	->findOneByModulesCodeAndModulesType($Module->getCode(), $Module->getModuleType());
if (!$Modules){
	$Modules = new Modules();
	$Modules->modules_code = $Module->getCode();
	$Modules->modules_type = $Module->getModuleType();
}

$Configuration = $Modules->ModulesConfiguration;
if (isset($_POST['configuration'])){
	foreach($_POST['configuration'] as $key => $value){
		$Configuration[$key]->configuration_key = $key;

		if (is_array($value)){
			$Glue = $ModuleConfig
				->getConfig($key)
				->getGlue();
			$Configuration[$key]->configuration_value = implode($Glue, $value);
		}
		else {
			$Configuration[$key]->configuration_value = $value;
		}
	}
}

if (file_exists(sysConfig::getDirFsCatalog() . 'includes/modules/' . $moduleDir . '/' . $moduleCode . '/actions/save.php')){
	require(sysConfig::getDirFsCatalog() . 'includes/modules/' . $moduleDir . '/' . $moduleCode . '/actions/save.php');
}

$Modules->save();

EventManager::attachActionResponse(array(
	'success'    => true,
	'moduleType' => $moduleType
), 'json');
?>