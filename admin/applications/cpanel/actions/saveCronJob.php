<?php
$Response = array(
	'success' => false,
	'error' => array(
		'message' => 'An Unknown Error Occurred!'
	)
);

$cPanelCron = false;
$CronJobs = Doctrine_Core::getTable('CronJobs');
if (isset($_GET['cron_id'])){
	$CronJob = $CronJobs->find((int)$_GET['cron_id']);

	$cPanelCron = getCronSettings($CronJob->id);
}
else {
	$CronJob = $CronJobs->getRecord();
}
$CronJob->job_modules = implode(',', $_POST['cron_module']);
$CronJob->save();

$apiParams = array(
	'command' => 'wget -q -O /dev/null "http://' . sysConfig::get('HTTP_DOMAIN_NAME') . '/processCron_cPanel.php?id=' . $CronJob->id . '"',
	'minute'  => $_POST['cron_minute'],
	'hour'    => $_POST['cron_hour'],
	'day'     => $_POST['cron_day'],
	'month'   => $_POST['cron_month'],
	'weekday' => $_POST['cron_weekday']
);

if ($cPanelCron !== false){
	$apiAction = 'edit_line';
	$apiParams['linekey'] = $cPanelCron['linekey'];
}
else {
	$apiAction = 'add_line';
}

$Result = $cPanel->api2_query(
	sysConfig::get('MY_SERVER_CPANEL_USERNAME'),
	'Cron',
	$apiAction,
	$apiParams
);
$ResultJSON = json_decode($Result, true);

if ($ResultJSON['cpanelresult']['data'][0]['status'] == 1){
	$Response['success'] = true;
}else{
	$CronJob->delete();
	$Response['error'] = array(
		'message' => $ResultJSON['cpanelresult']['data'][0]['statusmsg']
	);
}

EventManager::attachActionResponse($Response, 'json');
