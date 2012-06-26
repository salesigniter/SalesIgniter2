<?php
/*
	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

require('includes/application_top.php');

$CronId = (int)$_GET['id'];
$CronJob = Doctrine_Core::getTable('CronJobs')
	->find($CronId);
if ($CronJob && $CronJob->count() > 0){
	$Modules = explode(',', $CronJob->job_modules);
	foreach($Modules as $ModuleName){
		$CronModule = CronJobModules::getModule($ModuleName);
		if ($CronModule && $CronModule->isEnabled() === true){
			$CronModule->process();
		}
	}
}
