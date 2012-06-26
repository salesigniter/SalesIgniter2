<?php
$Response = array(
	'success' => false,
	'errorMessage' => 'Invalid Cron Job Specified'
);

$CronJobs = Doctrine_Core::getTable('CronJobs');
if (isset($_GET['cron_id'])){
	$toDelete = explode(',', $_GET['cron_id']);
	foreach($toDelete as $jobId){
		$CronJob = $CronJobs->find((int)$jobId);

		$cPanelCron = getCronSettings($CronJob->id);

		$Result = $cPanel->api2_query(
			sysConfig::get('MY_SERVER_CPANEL_USERNAME'),
			'Cron',
			'remove_line',
			array('linekey' => $cPanelCron['linekey'])
		);
		$ResultJSON = json_decode($Result, true);

		if ($ResultJSON['cpanelresult']['data'][0]['status'] == 1){
			$Response['success'] = true;
			$CronJob->delete();
		}else{
			$Response['errorMessage'] = $ResultJSON['cpanelresult']['data'][0]['statusmsg'];
			break;
		}
	}
}

EventManager::attachActionResponse($Response, 'json');
