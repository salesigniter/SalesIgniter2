<?php
	if (isset($_GET['cID'])){
		$cID = $_GET['cID'];
	}

	require('includes/classes/data_populate/export.php');
	include(sysConfig::getDirFsCatalog() . 'includes/functions/crypt.php');

	$appContent = $App->getAppContentFile();

	if ($App->getAppPage() == 'edit'){
		$userAccount = new rentalStoreUser($cID);
		$userAccount->loadPlugins();
	}
?>