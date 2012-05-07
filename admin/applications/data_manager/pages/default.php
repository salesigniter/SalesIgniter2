<?php
$ModuleSelect = htmlBase::newElement('selectbox')
	->setName('module')
	->addOption('', 'Please Select A Module');
DataManagementModules::loadModules();
foreach(DataManagementModules::getModules() as $Module){
	$ModuleSelect->addOption($Module->getCode(), $Module->getTitle(), false, array(
		'data-supported_formats' => htmlentities(json_encode($Module->getSupportedFormats())),
		'data-supported_actions' => htmlentities(json_encode($Module->getSupportedActions()))
	));
}
$DataFormat = htmlBase::newElement('selectbox')
	->setName('module_format')
	->addOption('', 'Please Select A Module');

$ActionSelect = htmlBase::newElement('selectbox')
	->setName('module_action')
	->addOption('', 'Please Select A Module');

$ActionButton = htmlBase::newElement('button')
	->setType('submit')
	->setText('Perform Action');
?>
<form enctype="multipart/form-data" name="dataManager" action="<?php echo itw_app_link('action=perform', 'data_manager', 'default');?>" method="post">
	<div style="margin:1em;">
		<div>Select Which Data To Manage</div>
		<div><?php echo $ModuleSelect->draw();?></div>
		<br>

		<div>Select What Format To Use</div>
		<div><?php echo $DataFormat->draw();?></div>
		<br>

		<div>Select Which Action To Perform</div>
		<div><?php echo $ActionSelect->draw();?></div>
		<br>

		<div>Select Which File To Work With ( Import Only )</div>
		<div><input type="file" name="file_to_use"></div>
		<br>

		<div><?php echo $ActionButton->draw();?></div>
	</div>
</form>
