<?php
$Grid = htmlBase::newGrid()
	->setMainDataKey('cron_id')
	->allowMultipleRowSelect(true);

$Grid->addButtons(array(
	htmlBase::newButton()->addClass('backButton')->usePreset('back')->setText(sysLanguage::get('BUTTON_TEXT_BACK_TO_MAIN'))->setHref(itw_app_link(null, 'cpanel', 'default')),
	htmlBase::newButton()->addClass('newButton')->usePreset('new'),
	htmlBase::newButton()->addClass('editButton')->usePreset('edit')->disable(),
	htmlBase::newButton()->addClass('deleteButton')->usePreset('delete')->disable()
));

$Grid->addHeaderRow(array(
	'columns' => array(
		array('text' => 'Minute'),
		array('text' => 'Hour'),
		array('text' => 'Day'),
		array('text' => 'Month'),
		array('text' => 'Weekday'),
		array('text' => 'Command')
	)
));

foreach(getCronSettings() as $CronJob){
	if (!isset($CronJob['command'])){
		continue;
	}

	$Qcheck = Doctrine_Query::create()
		->from('CronJobs')
		->where('id = ?', $CronJob['id'])
		->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
	if (sizeof($Qcheck) > 0){
		$existsInDb = true;
		$command = implode('<br>', explode(',', $Qcheck[0]['job_modules']));
		$cronId = $Qcheck[0]['id'];
	}else{
		$existsInDb = false;
		$command = $CronJob['command_htmlsafe'];
		$cronId = 0;
	}

	$Grid->addBodyRow(array(
		'rowAttr' => array(
			'data-cron_id' => $cronId,
			'data-exists_in_db' => $existsInDb
		),
		'addCls' => ($existsInDb === false ? 'ui-state-error' : ''),
		'columns' => array(
			array('text' => $MinuteValueText[$CronJob['minute']]),
			array('text' => $HourValueText[$CronJob['hour']]),
			array('text' => $DayValueText[$CronJob['day']]),
			array('text' => $MonthValueText[$CronJob['month']]),
			array('text' => $WeekdayValueText[$CronJob['weekday']]),
			array('text' => $command)
		)
	));
}

?>
<div class="ui-widget ui-widget-content ui-corner-all" style="margin:5px;">
	<div style="margin:5px;"><?php echo $Grid->draw();?></div>
</div>
