<?php
$Fields = Doctrine_Core::getTable('CustomersCustomFields');

if (isset($_GET['field_id'])){
	$Field = $Fields->find((int)$_GET['field_id']);
}
else {
	$Field = $Fields->getRecord();
}

$infoBox = htmlBase::newElement('infobox');
$infoBox->setHeader('<b>' . ($Field->field_id > 0 ? sysLanguage::get('TEXT_ACTION_WINDOW_HEADING_EDIT_FIELD') : sysLanguage::get('TEXT_ACTION_WINDOW_HEADING_NEW_FIELD')) . '</b>');
$infoBox->setButtonBarLocation('top');

$saveButton = htmlBase::newElement('button')->attr('data-action', 'saveField')->addClass('saveButton')->usePreset('save');
$cancelButton = htmlBase::newElement('button')->addClass('cancelButton')->usePreset('cancel');

$infoBox->addButton($saveButton)->addButton($cancelButton);

$fieldKey = htmlBase::newElement('input')
	->setName('field_key')
	->val($Field->field_key)
	->setRequired(true)
	->attr('data-validate', 'true')
	->attr('pattern', '[a-zA-Z0-9_]+');

$fieldNames = htmlBase::newElement('table')->setCellPadding('3')->setCellSpacing('0');
foreach(sysLanguage::getLanguages() as $lInfo){
	$langId = $lInfo['id'];

	$fieldNameInput = htmlBase::newElement('input')
		->setName('field_name[' . $langId . ']')
		->setRequired(true)
		->setValidation(true, '[a-zA-Z ]+');

	if ($Field->Description && isset($Field->Description[$langId])){
		$fieldNameInput->setValue($Field->Description[$langId]->field_name);
	}

	$fieldNames->addBodyRow(array(
		'columns' => array(
			array('addCls' => 'main', 'text' => $lInfo['showName']()),
			array('addCls' => 'main', 'text' => $fieldNameInput)
		)
	));
}

$selectInputOptions = htmlBase::newElement('table')->setCellPadding('3')->setCellSpacing('0')
	->addBodyRow(array(
	'columns' => array(
		array('addCls' => 'main', 'text' => '<b><u>' . sysLanguage::get('TABLE_HEADING_OPTION_TEXT') . '</u></b>'),
		array('addCls' => 'main', 'text' => '<b><u>' . sysLanguage::get('TABLE_HEADING_SORT_ORDER') . '</u></b>')
	)
));

$lId = Session::get('languages_id');
for($i=0; $i<15; $i++){
	$nameInput = htmlBase::newElement('input')->setName('option_name[' . $i . ']');
	$sortInput = htmlBase::newElement('input')->setName('option_sort[' . $i . ']')->setSize('4');

	if ($Field->Options && isset($Field->Options[$i])){
		$Option = $Field->Options[$i]->Option;

		$nameInput->setValue($Option->Description[$lId]->option_name);
		$sortInput->setValue($Option->sort_order);
	}

	$selectInputOptions->addBodyRow(array(
		'columns' => array(
			array('addCls' => 'main', 'text' => $nameInput),
			array('addCls' => 'main', 'text' => $sortInput)
		)
	));
}

$optionsWrapper = new htmlElement('div');
$optionsWrapper->attr('id', 'selectOptions');
$optionsWrapper->append($selectInputOptions);

$inputTypeMenu = htmlBase::newElement('selectbox')
	->setName('input_type')
	->change('showOptionEntry(this)')
	->selectOptionByValue($Field->input_type);

$inputTypeMenu->addOption('text', 'Text')
	->addOption('textarea', 'Textarea')
	->addOption('select', 'Select Box')
	->addOption('upload', 'Image Upload');

$showOnGroup = htmlBase::newElement('checkbox')
	->addGroup(array(
	'name' => 'show_on[]',
	'labelPosition' => 'after',
	'separator' => '<br>',
	'data' => array(
		array(
			'label' => sysLanguage::get('ENTRY_SHOW_ON_SITE'),
			'value' => 'site',
			'checked' => ($Field->show_on_site == '1')
		),
		array(
			'label' => sysLanguage::get('ENTRY_SHOW_ON_PRODUCT_LISTING'),
			'value' => 'listing',
			'checked' => ($Field->show_on_listing == '1')
		)
	)
));

if ($Field->input_type != 'select'){
	$optionsWrapper->css('display', 'none');
}

$finalTable = htmlBase::newElement('table')
	->setCellPadding('3')
	->setCellSpacing('0')
	->attr('field_id', $Field->field_id);

$finalTable->addBodyRow(array('columns' => array(
	array('addCls' => 'main', 'text' => '<b>' . sysLanguage::get('ENTRY_FIELD_KEY') . '</b>')
)));

$finalTable->addBodyRow(array('columns' => array(
	array('addCls' => 'main', 'text' => $fieldKey)
)));

$finalTable->addBodyRow(array('columns' => array(
	array('addCls' => 'main', 'text' => '<b>' . sysLanguage::get('ENTRY_FIELD_NAME') . '</b>')
)));

$finalTable->addBodyRow(array('columns' => array(
	array('addCls' => 'main', 'text' => $fieldNames)
)));

$finalTable->addBodyRow(array('columns' => array(
	array('addCls' => 'main', 'text' => '<b>' . sysLanguage::get('ENTRY_INPUT_TYPE') . '</b>')
)));

$finalTable->addBodyRow(array('columns' => array(
	array('addCls' => 'main', 'text' => $inputTypeMenu)
)));

$finalTable->addBodyRow(array('columns' => array(
	array('addCls' => 'main', 'text' => $optionsWrapper)
)));

$finalTable->addBodyRow(array('columns' => array(
	array('addCls' => 'main', 'text' => '<b>Show On</b>')
)));
$finalTable->addBodyRow(array('columns' => array(
	array('addCls' => 'main', 'text' => $showOnGroup)
)));

EventManager::notify('CustomersCustomFieldsNewOptions', $Field, $finalTable, $windowAction);

$infoBox->addContentRow($finalTable->draw());

EventManager::notify('CustomersCustomFieldsNewEditFieldWindowBeforeDraw', $infoBox, $Group);

EventManager::attachActionResponse($infoBox->draw(), 'html');
