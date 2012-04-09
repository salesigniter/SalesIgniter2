<?php
$windowAction = $_GET['windowAction'];
if ($windowAction == 'new'){
	$header = '<b>' . 'Create New Field' . '</b>';
}
else {
	$Field = Doctrine_Core::getTable('OrdersCustomFields')->findOneByFieldId((int)$_GET['fID']);
	$FieldDescription = $Field->OrdersCustomFieldsDescription;
	$FieldOptions = $Field->OrdersCustomFieldsOptionsToFields;
	$header = '<b>' . $FieldDescription[Session::get('languages_id')]['field_name'] . '</b>';
}

$fieldNames = htmlBase::newElement('table')->setCellPadding('3')->setCellSpacing('0');
foreach(sysLanguage::getLanguages() as $lInfo){
	$langId = $lInfo['id'];

	$fieldNameInput = htmlBase::newElement('input')->setName('field_name[' . $langId . ']');

	if (isset($Field) && $Field !== false){
		$fieldNameInput->setValue($FieldDescription[$langId]['field_name']);
	}

	$fieldNames->addBodyRow(array(
		'columns' => array(
			array(
				'addCls' => 'main',
				'text'   => $lInfo['showName']()
			),
			array(
				'addCls' => 'main',
				'text'   => $fieldNameInput
			)
		)
	));
}

$sortOrder = htmlBase::newElement('input')->setName('sort_order');
$sortOrder->setValue($Field->sort_order);
$requiredCheckbox = htmlBase::newElement('checkbox')
	->setName('input_required');

$sizeOfOptions = sizeof($FieldOptions);
$selectInputOptions = htmlBase::newElement('table')->setCellPadding('3')->setCellSpacing('0')->css('width', '100%')
	->addHeaderRow(array(
	'columns' => array(
		array(
			'addCls' => 'main',
			'text'   => '<b><u>' . sysLanguage::get('TABLE_HEADING_OPTION_TEXT') . '</u></b>'
		),
		array(
			'addCls' => 'main',
			'text'   => '<span class="ui-icon ui-icon-circle-plus addSelectOption" tooltip="Add An Option" data-next_id="' . $sizeOfOptions . '" data-next_sort="' . $sizeOfOptions . '"></span>'
		)
	)
));

$lId = Session::get('languages_id');
for($i = 0; $i < $sizeOfOptions; $i++){
	$nameInput = htmlBase::newElement('input')->addClass('text')->setType('text')->setName('option_name[' . $i . ']');
	$sortInput = htmlBase::newElement('input')->addClass('sort')->setType('hidden')->setName('option_sort[' . $i . ']');
	$dataInput = htmlBase::newElement('input')->addClass('data')->setType('hidden')->setName('option_data[' . $i . ']');

	$Option = $FieldOptions[$i]['OrdersCustomFieldsOptions'];

	$nameInput->setValue($Option['OrdersCustomFieldsOptionsDescription'][$lId]['option_name']);
	$sortInput->setValue($Option['sort_order']);
	$dataInput->setValue(urlencode($Option['extra_data']));

	$selectInputOptions->addBodyRow(array(
		'rowAttr' => array(
			'data-field_id' => $i
		),
		'columns' => array(
			array(
				'text'   => $nameInput
			),
			array(
				'text'   => '<span class="ui-icon ui-icon-wrench editData" tooltip="Edit Data"></span>' .
					'<span class="ui-icon ui-icon-arrowthick-1-n moveOptionUp" tooltip="Move Up"></span>' .
					'<span class="ui-icon ui-icon-arrowthick-1-s moveOptionDown" tooltip="Move Down"></span>' .
					'<span class="ui-icon ui-icon-circle-minus removeOption" tooltip="Remove Option"></span>' .
					$sortInput->draw() .
					$dataInput->draw()
			)
		)
	));
}

$optionsWrapper = new htmlElement('div');
$optionsWrapper->attr('id', 'selectOptions');
$optionsWrapper->append($selectInputOptions);

$inputTypeMenu = htmlBase::newElement('selectbox')
	->setName('input_type')
	->change('showOptionEntry(this)');

$inputTypeMenu->addOption('text', 'Text')
	->addOption('textarea', 'Textarea')
	->addOption('select_address', 'Address Select')
	->addOption('select', 'Select Box Without Other')
	->addOption('select_other', 'Select Box With Other');

if (!isset($Field) || ($Field !== false && ($Field['input_type'] != 'select' && $Field['input_type'] != 'select_other' && $Field['input_type'] != 'select_address'))){
	$optionsWrapper->css('display', 'none');
}

$finalTable = htmlBase::newElement('table')->setCellPadding('3')->setCellSpacing('0')->css('width', '100%');

if (isset($Field) && $Field !== false){
	$inputTypeMenu->selectOptionByValue($Field['input_type']);
	$requiredCheckbox->setChecked(($Field['input_required'] == 1));

	$finalTable->attr('field_id', $Field['field_id']);
}

$finalTable->addBodyRow(array(
	'columns' => array(
		array(
			'addCls' => 'main',
			'text'   => '<b>' . sysLanguage::get('ENTRY_FIELD_NAME') . '</b>'
		)
	)
));

$finalTable->addBodyRow(array(
	'columns' => array(
		array(
			'addCls' => 'main',
			'text'   => $fieldNames
		)
	)
));

$finalTable->addBodyRow(array(
	'columns' => array(
		array(
			'addCls' => 'main',
			'text'   => '<b>' . sysLanguage::get('ENTRY_FIELD_REQUIRED') . '</b>'
		)
	)
));

$finalTable->addBodyRow(array(
	'columns' => array(
		array(
			'addCls' => 'main',
			'text'   => $requiredCheckbox
		)
	)
));

$finalTable->addBodyRow(array(
	'columns' => array(
		array(
			'addCls' => 'main',
			'text'   => '<b>' . sysLanguage::get('ENTRY_INPUT_TYPE') . '</b>'
		)
	)
));

$finalTable->addBodyRow(array(
	'columns' => array(
		array(
			'addCls' => 'main',
			'text'   => $inputTypeMenu
		)
	)
));

$finalTable->addBodyRow(array(
	'columns' => array(
		array(
			'addCls' => 'main',
			'text'   => '<b>' . sysLanguage::get('ENTRY_SORT_ORDER') . '</b>'
		)
	)
));

$finalTable->addBodyRow(array(
	'columns' => array(
		array(
			'addCls' => 'main',
			'text'   => $sortOrder
		)
	)
));

$finalTable->addBodyRow(array(
	'columns' => array(
		array(
			'addCls' => 'main',
			'text'   => $optionsWrapper
		)
	)
));

EventManager::attachActionResponse($finalTable->draw(), 'html');
?>