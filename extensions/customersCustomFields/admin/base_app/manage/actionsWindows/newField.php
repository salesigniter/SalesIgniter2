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

$saveButton = htmlBase::newElement('button')
	->attr('data-action', 'saveField')
	->addClass('saveButton')
	->usePreset('save');
$cancelButton = htmlBase::newElement('button')
	->addClass('cancelButton')
	->usePreset('cancel');

$infoBox
	->addButton($saveButton)
	->addButton($cancelButton);

$fieldKey = htmlBase::newElement('input')
	->setName('field_key')
	->val($Field->field_key)
	->setRequired(true)
	->attr('data-validate', 'true')
	->attr('pattern', '[a-zA-Z0-9_]+');

$fieldDefaultValue = htmlBase::newElement('input')
	->setName('field_default_value')
	->val($Field->field_data->default_value);

$fieldNames = htmlBase::newElement('table')
	->setCellPadding('3')
	->setCellSpacing('0');
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

$requiredCheckbox = htmlBase::newElement('checkbox')
	->setLabel(sysLanguage::get('ENTRY_INPUT_REQUIRED'))
	->setLabelPosition('right')
	->setName('input_required')
	->setChecked(($Field->field_data->required == 1));

$isMultipleCheckbox = htmlBase::newElement('checkbox')
	->setLabel(sysLanguage::get('ENTRY_INPUT_MULTIPLE'))
	->setLabelPosition('right')
	->setName('is_multiple')
	->setChecked(($Field->field_data->multiple == 1));

$useInSearchCheckbox = htmlBase::newElement('checkbox')
	->setLabel('Use In Searches')
	->setLabelPosition('right')
	->setName('use_in_search')
	->setChecked(($Field->field_data->use_in_search == 1));

$sizeOfOptions = $Field->Options->count();

$InputOptions = htmlBase::newElement('table')
	->setCellPadding('3')
	->setCellSpacing('0')
	->css('width', '100%')
	->addHeaderRow(array(
	'columns' => array(
		array(
			'addCls' => 'main',
			'text'   => '<b><u>' . sysLanguage::get('TABLE_HEADING_OPTION_TEXT') . '</u></b>'
		),
		array(
			'addCls' => 'main',
			'text'   => '<span class="ui-icon ui-icon-circle-plus addOption" tooltip="Add An Option" data-next_id="' . $sizeOfOptions . '" data-next_sort="' . $sizeOfOptions . '"></span>'
		)
	)
));

$lId = Session::get('languages_id');
foreach($Field->Options as $i => $Option){
	$nameInput = htmlBase::newElement('input')
		->addClass('text')
		->setType('text')
		->setName('option_name[' . $i . ']')
		->setValue($Option->Description[$lId]->option_name);

	$sortInput = htmlBase::newElement('input')
		->addClass('sort')
		->setType('hidden')
		->setName('option_sort[' . $i . ']')
		->setValue($Option->display_order);

	$InputOptions->addBodyRow(array(
		'rowAttr' => array(
			'data-field_id' => $i
		),
		'columns' => array(
			array(
				'text'   => $nameInput
			),
			array(
				'text'   => /*'<span class="ui-icon ui-icon-wrench editData" tooltip="Edit Data"></span>' .*/
				'<span class="ui-icon ui-icon-arrowthick-1-n moveOptionUp" tooltip="Move Up"></span>' .
					'<span class="ui-icon ui-icon-arrowthick-1-s moveOptionDown" tooltip="Move Down"></span>' .
					'<span class="ui-icon ui-icon-circle-minus removeOption" tooltip="Remove Option"></span>' .
					$sortInput->draw()
			)
		)
	));
}

$optionsWrapper = new htmlElement('div');
$optionsWrapper->attr('id', 'inputOptions');
$optionsWrapper->append($InputOptions);

$inputTypeMenu = htmlBase::newElement('selectbox')
	->setName('input_type')
	->change('showOptionEntry(this)')
	->selectOptionByValue($Field->field_data->type);

$inputTypeMenu
	->addOption('text', 'Text')
	->addOption('date', 'Date Picker')
	->addOption('radio', 'Radio Set')
	->addOption('checkbox', 'Checkbox Set')
	->addOption('textarea', 'Textarea')
	->addOption('select', 'Select Box')
	->addOption('upload', 'Image Upload');

if ($Field->field_data->type != 'select' && $Field->field_data->type != 'radio' && $Field->field_data->type != 'checkbox'){
	$optionsWrapper->css('display', 'none');
}

$showOnData = array(
	array(
		'label'   => sysLanguage::get('ENTRY_SHOW_ON_CUSTOMER_ACCOUNT'),
		'value'   => 'customer_account',
		'checked' => ($Field->field_data->show_on->customer_account == '1')
	),
	array(
		'label'   => sysLanguage::get('ENTRY_SHOW_ON_CUSTOMER_LISTING'),
		'value'   => 'customer_listing',
		'checked' => ($Field->field_data->show_on->customer_listing == '1')
	),
	array(
		'label'   => sysLanguage::get('ENTRY_SHOW_ON_ADDRESS_LABELS'),
		'value'   => 'address_labels',
		'checked' => ($Field->field_data->show_on->address_labels == '1')
	)
);

if ($appExtension->isInstalled('orderCreator') && $appExtension->isEnabled('orderCreator')){
	$showOnData[] = array(
		'label'   => sysLanguage::get('ENTRY_SHOW_ON_ORDER_CREATOR'),
		'value'   => 'order_creator',
		'checked' => ($Field->field_data->show_on->order_creator == '1')
	);
}

$showOnGroup = htmlBase::newElement('checkbox')
	->addGroup(array(
	'name'          => 'show_on[]',
	'labelPosition' => 'after',
	'separator'     => '<br>',
	'data'          => $showOnData
));

$finalTable = htmlBase::newElement('table')
	->setCellPadding('3')
	->setCellSpacing('0')
	->attr('field_id', $Field->field_id);

$finalTable->addBodyRow(array(
	'columns' => array(
		array('addCls' => 'main', 'text' => '<b>' . sysLanguage::get('ENTRY_FIELD_KEY') . '</b>')
	)
));

$finalTable->addBodyRow(array(
	'columns' => array(
		array('addCls' => 'main', 'text' => $fieldKey)
	)
));

$finalTable->addBodyRow(array(
	'columns' => array(
		array('addCls' => 'main', 'text' => '<b>' . sysLanguage::get('ENTRY_FIELD_NAME') . '</b>')
	)
));

$finalTable->addBodyRow(array(
	'columns' => array(
		array('addCls' => 'main', 'text' => $fieldNames)
	)
));

$finalTable->addBodyRow(array(
	'columns' => array(
		array('addCls' => 'main', 'text' => $requiredCheckbox)
	)
));

$finalTable->addBodyRow(array(
	'columns' => array(
		array('addCls' => 'main', 'text' => $isMultipleCheckbox)
	)
));

$finalTable->addBodyRow(array(
	'columns' => array(
		array('addCls' => 'main', 'text' => $useInSearchCheckbox)
	)
));

$finalTable->addBodyRow(array(
	'columns' => array(
		array('addCls' => 'main', 'text' => '<b>' . sysLanguage::get('ENTRY_INPUT_DEFAULT_VALUE') . '</b>')
	)
));

$finalTable->addBodyRow(array(
	'columns' => array(
		array('addCls' => 'main', 'text' => $fieldDefaultValue)
	)
));

$finalTable->addBodyRow(array(
	'columns' => array(
		array('addCls' => 'main', 'text' => '<b>' . sysLanguage::get('ENTRY_INPUT_TYPE') . '</b>')
	)
));

$finalTable->addBodyRow(array(
	'columns' => array(
		array('addCls' => 'main', 'text' => $inputTypeMenu)
	)
));

$finalTable->addBodyRow(array(
	'columns' => array(
		array('addCls' => 'main', 'text' => $optionsWrapper)
	)
));

$finalTable->addBodyRow(array(
	'columns' => array(
		array('addCls' => 'main', 'text' => '<b>Show On</b>')
	)
));
$finalTable->addBodyRow(array(
	'columns' => array(
		array('addCls' => 'main', 'text' => $showOnGroup)
	)
));

EventManager::notify('CustomersCustomFieldsNewOptions', $Field, $finalTable);

$infoBox->addContentRow($finalTable->draw());

EventManager::notify('CustomersCustomFieldsNewEditFieldWindowBeforeDraw', $infoBox);

EventManager::attachActionResponse($infoBox->draw(), 'html');
