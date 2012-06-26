<?php
/**
 * Sales Igniter E-Commerce System
 * Version: {ses_version}
 *
 * I.T. Web Experts
 * http://www.itwebexperts.com
 *
 * Copyright (c) {ses_copyright} I.T. Web Experts
 *
 * This script and its source are not distributable without the written consent of I.T. Web Experts
 */

require(sysConfig::getDirFsCatalog() . 'includes/classes/whmjson.php');

sysLanguage::set('PAGE_TITLE', sysLanguage::get('HEADING_TITLE'));

$cPanel = new xmlapi(
	sysConfig::get('MY_SERVER_CPANEL_HOST'),
	sysConfig::get('MY_SERVER_CPANEL_USERNAME'),
	sysConfig::get('MY_SERVER_CPANEL_PASSWORD')
);
$cPanel->set_port(2083);
$cPanel->set_debug(0);
$cPanel->set_output('json');

if ($App->getPageName() == 'crontab'){
	function getCronSettings($id = null)
	{
		global $cPanel;
		$Result = $cPanel->api2_query(
			sysConfig::get('MY_SERVER_CPANEL_USERNAME'),
			'Cron',
			'listcron'
		);
		$ResultJSON = json_decode($Result, true);

		if ($id !== null){
			$CurrentCron = false;
			foreach($ResultJSON['cpanelresult']['data'] as $CpCronJob){
				if (preg_match('/\?id=' . $id . '/', $CpCronJob['command'])){
					$CurrentCron = $CpCronJob;
					$CurrentCron['id'] = $id;
					break;
				}
			}
		}
		else {
			$CurrentCron = $ResultJSON['cpanelresult']['data'];
			foreach($CurrentCron as $idx => $CpCronJob){
				preg_match('/\?id=([0-9]+)/', $CpCronJob['command'], $matches);
				if (isset($matches[1])){
					$CurrentCron[$idx]['id'] = $matches[1];
				}
			}
		}
		return $CurrentCron;
	}

	$MinuteValueText = array(
		'*'    => 'Every minute',
		'*/2'  => 'Every other minute',
		'*/5'  => 'Every 5 minutes',
		'*/10' => 'Every 10 minutes',
		'*/15' => 'Every 15 minutes',
		'0,30' => 'Every 30 minutes'
	);
	for($i = 0; $i < 60; $i++){
		$MinuteValueText[$i] = date(':i', mktime(0, $i, 0));
	}

	$HourValueText = array(
		'*'    => 'Every hour',
		'*/2'  => 'Every other hour',
		'*/3'  => 'Every 3 hours',
		'*/4'  => 'Every 4 hours',
		'*/6'  => 'Every 6 hours',
		'0,12' => 'Every 12 hours'
	);
	for($i = 0; $i < 24; $i++){
		$HourValueText[$i] = date('g:i a', mktime($i, 0, 0));
	}

	$DayValueText = array(
		'*'    => 'Every day',
		'*/2'  => 'Every other day',
		'1,15' => '1st and 15th'
	);
	for($i = 1; $i < 32; $i++){
		$DayValueText[$i] = date('jS', mktime(0, 0, 0, 0, $i));
	}

	$MonthValueText = array(
		'*'   => 'Every month',
		'*/2' => 'Every other month',
		'*/4' => 'Every 3 months',
		'1,7' => 'Every 6 months'
	);
	for($i = 1; $i < 13; $i++){
		$MonthValueText[$i] = date('F', mktime(0, 0, 0, $i, 1));
	}

	$WeekdayNames = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
	$WeekdayValueText = array(
		'*'     => 'Every weekday',
		'1-5'   => 'Mon thru Fri',
		'0,6'   => 'Sat and Sun',
		'1,3,5' => 'Mon, Wed, Fri',
		'2,4'   => 'Tues, Thurs'
	);
	for($i = 0; $i < 7; $i++){
		$WeekdayValueText[$i] = $WeekdayNames[$i];
	}
}
