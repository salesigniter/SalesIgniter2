<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stephen
 * Date: 3/26/11
 * Time: 5:14 PM
 * To change this template use File | Settings | File Templates.
 */

$TemplateManagerLayouts = Doctrine_Core::getTable('TemplateManagerLayouts');
if (isset($_GET['layout_id'])){
	$Layout = $TemplateManagerLayouts->find((int) $_GET['layout_id']);
}else{
	$Layout = $TemplateManagerLayouts->getRecord();
}

$PageTypeSelect = htmlBase::newElement('selectbox')
	->setName('pageType')
	->selectOptionByValue($Layout->page_type)
	->addOption('', 'Please Select A Layout Type');

TemplateManagerLayoutTypeModules::loadModules();
foreach(TemplateManagerLayoutTypeModules::getModules() as $Module){
	if ($Module->getCode() == 'email'){
		continue;
	}
	$PageTypeSelect->addOption($Module->getCode(), $Module->getTitle());
}

$SettingsTable = htmlBase::newElement('table')
->setCellPadding(3)
->setCellSpacing(0);

$SettingsTable->addBodyRow(array(
	'columns' => array(
		array('text' => 'Layout Name:'),
		array('text' => htmlBase::newElement('input')
		->setName('layoutName')
		->attr('id', 'layoutName')
		->val($Layout->layout_name)
		->draw())
	)
));

$SettingsTable->addBodyRow(array(
	'columns' => array(
		array('text' => 'Page Type:'),
		array('text' => $PageTypeSelect->draw())
	)
));

/*if($Layout->Template->Configuration['NAME']->configuration_value == 'codeGeneration'){
	$associativeUrl = htmlBase::newElement('input')
		->setLabel('Show in page:');

}*/

$SaveButton = htmlBase::newElement('button')
	->addClass('saveButton')
	->usePreset('save');

$CancelButton = htmlBase::newElement('button')
	->addClass('cancelButton')
	->usePreset('cancel');

$Infobox = htmlBase::newActionWindow()
	->addButton($SaveButton)
	->addButton($CancelButton)
	->setContent($SettingsTable->draw() . '<div id="layoutSettings"></div>');

EventManager::attachActionResponse($Infobox->draw(), 'html');
