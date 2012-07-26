<?php
$ModuleSelect = htmlBase::newElement('selectbox')
	->setName('module')
	->addOption('', 'Please Select A Module');
DataManagementModules::loadModules();
foreach(DataManagementModules::getModules() as $Module){
	$ModuleSelect->addOption($Module->getCode(), $Module->getTitle(), false, array(
		'data-supported_formats' => htmlspecialchars(json_encode($Module->getSupportedFormats())),
		'data-supported_actions' => htmlspecialchars(json_encode($Module->getSupportedActions()))
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
	<?php
	$Fieldset = htmlBase::newFieldsetFormBlock();
	$Fieldset->setLegend('Import/Export Settings');

	$Fieldset->addBlock('module', 'Select A Module', array(
		array($ModuleSelect)
	));

	$Fieldset->addBlock('format', 'Select A Format', array(
		array($DataFormat)
	));

	$Fieldset->addBlock('action', 'Select An Action', array(
		array($ActionSelect)
	));

	$Fieldset->addBlock('file', 'Select A File ( Import Only )', array(
		array(htmlBase::newElement('label')->html('Select Which File To Work With')),
		array(htmlBase::newElement('input')->setType('file')->setName('file_to_use'))
	));

	$StartRecord = htmlBase::newInput()
		->setSize(5)
		->setLabel('Start Record')
		->setLabelPosition('below')
		->setName('start_record');

	$EndRecord = htmlBase::newInput()
		->setSize(5)
		->setLabel('End Record')
		->setLabelPosition('below')
		->setName('end_record');

	$Fieldset->addBlock('limit', 'Limit Results ( Export Only )', array(
		array(htmlBase::newElement('label')->html('Select Start/End Records To Limit Results ( Leave Empty In Either To Ignore It )')),
		array($StartRecord, $EndRecord)
	));

	$Fieldset->addBlock('button', '', array(
		array($ActionButton)
	));

	echo $Fieldset->draw();
	?>
</form>
