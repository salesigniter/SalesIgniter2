<?php
$CronJobs = Doctrine_Core::getTable('CronJobs');
if (isset($_GET['cron_id'])){
	$CronJob = $CronJobs->find((int)$_GET['cron_id']);
}
else {
	$CronJob = $CronJobs->getRecord();
}
CronJobModules::loadModules();

$infoBox = htmlBase::newElement('infobox');
$infoBox->setHeader('<b>' . ($Group->id > 0 ? sysLanguage::get('WINDOW_HEADING_EDIT') : sysLanguage::get('WINDOW_HEADING_NEW')) . '</b>');
$infoBox->setButtonBarLocation('top');

$saveButton = htmlBase::newElement('button')->addClass('saveButton')->usePreset('save');
$cancelButton = htmlBase::newElement('button')->addClass('cancelButton')->usePreset('cancel');

$infoBox->addButton($saveButton)->addButton($cancelButton);

$CurrentCron = getCronSettings($CronJob->id);
if ($CurrentCron === false){
	$CurrentCron = array(
		'linekey' => '0',
		'minute' => '0',
		'hour' => '0',
		'month' => '*',
		'day' => '*',
		'weekday' => '*',
		'command_htmlsafe' => ''
	);
}

$TemplateSelect = htmlBase::newSelectbox()
	->setName('cron_template')
	->addOption('', 'Common Settings')
	->addOption('* * * * *', 'Every minute')
	->addOption('*/5 * * * *', 'Every 5 minutes')
	->addOption('0,30 * * * *', 'Twice an hour')
	->addOption('0 * * * *', 'Once an hour')
	->addOption('0 0,12 * * *', 'Twice a day')
	->addOption('0 0 * * *', 'Once a day')
	->addOption('0 0 * * 0', 'Once a week')
	->addOption('0 0 1,15 * *', '1st and 15th')
	->addOption('0 0 1 * *', 'Once a month')
	->addOption('0 0 1 1 *', 'Once a year');

$MinuteSelect = htmlBase::newSelectbox()
	->setName('cron_minute')
	->selectOptionByValue($CurrentCron['minute']);
foreach($MinuteValueText as $k => $v){
	$MinuteSelect->addOption($k, $v);
}

$HourSelect = htmlBase::newSelectbox()
	->setName('cron_hour')
	->selectOptionByValue($CurrentCron['hour']);
foreach($HourValueText as $k => $v){
	$HourSelect->addOption($k, $v);
}

$DaySelect = htmlBase::newSelectbox()
	->setName('cron_day')
	->selectOptionByValue($CurrentCron['day']);
foreach($DayValueText as $k => $v){
	$DaySelect->addOption($k, $v);
}

$MonthSelect = htmlBase::newSelectbox()
	->setName('cron_month')
	->selectOptionByValue($CurrentCron['month']);
foreach($MonthValueText as $k => $v){
	$MonthSelect->addOption($k, $v);
}

$WeekdaySelect = htmlBase::newSelectbox()
	->setName('cron_weekday')
	->selectOptionByValue($CurrentCron['weekday']);
foreach($WeekdayValueText as $k => $v){
	$WeekdaySelect->addOption($k, $v);
}

$InputTable = htmlBase::newElement('table')
	->setCellPadding(3)
	->setCellSpacing(0);

$InputTable->addBodyRow(array(
	'columns' => array(
		array(
			'align' => 'center',
			'colspan' => 2,
			'text' => '<b><u>' . sysLanguage::get('ENTRY_CRONJOB_COMMON_SETTINGS') . '</u></b>'
		)
	)
));

$InputTable->addBodyRow(array(
	'columns' => array(
		array('align' => 'center', 'colspan' => 2, 'text' => $TemplateSelect)
	)
));

$InputTable->addBodyRow(array(
	'columns' => array(
		array('colspan' => 2, 'text' => '<hr>')
	)
));

$InputTable->addBodyRow(array(
	'columns' => array(
		array('text' => sysLanguage::get('ENTRY_CRONJOB_MINUTE')),
		array('text' => $MinuteSelect)
	)
));

$InputTable->addBodyRow(array(
	'columns' => array(
		array('text' => sysLanguage::get('ENTRY_CRONJOB_HOUR')),
		array('text' => $HourSelect)
	)
));

$InputTable->addBodyRow(array(
	'columns' => array(
		array('text' => sysLanguage::get('ENTRY_CRONJOB_DAY')),
		array('text' => $DaySelect)
	)
));

$InputTable->addBodyRow(array(
	'columns' => array(
		array('text' => sysLanguage::get('ENTRY_CRONJOB_MONTH')),
		array('text' => $MonthSelect)
	)
));

$InputTable->addBodyRow(array(
	'columns' => array(
		array('text' => sysLanguage::get('ENTRY_CRONJOB_WEEKDAY')),
		array('text' => $WeekdaySelect)
	)
));

$cronJobModules = array();
foreach(CronJobModules::getModules() as $Module){
	$cronJobModules[] = array(
		'labelPosition' => 'after',
		'label' => $Module->getTitle(),
		'value' => $Module->getCode()
	);
}
$CheckboxGroup = htmlBase::newCheckbox()
	->addGroup(array(
	'name' => 'cron_module[]',
	'separator' => '<br>',
	'checked' => explode(',', $CronJob->job_modules),
	'data' => $cronJobModules
));

$InputTable->addBodyRow(array(
	'columns' => array(
		array('text' => sysLanguage::get('ENTRY_CRONJOB_COMMAND')),
		array('text' => $CheckboxGroup)
	)
));


$infoBox->addContentRow($InputTable->draw());

EventManager::notify('AdminAddressFormatNewEditWindowBeforeDraw', $infoBox, $AddressFormat);

EventManager::attachActionResponse($infoBox->draw(), 'html');
