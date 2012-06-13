<?php
$Fields = Doctrine_Core::getTable('OrdersCustomFields');
if (isset($_GET['field_id'])){
	$Field = $Fields->find((int)$_GET['field_id']);
	$header = '<b>' . 'Edit Field' . '</b>';
}
else {
	$Field = $Fields->create();
	$header = '<b>' . 'Create New Field' . '</b>';
}

$windowAction = $_GET['windowAction'];

$fieldNames = htmlBase::newElement('table')
	->setCellPadding('3')
	->setCellSpacing('0');

foreach(sysLanguage::getLanguages() as $lInfo){
	$langId = $lInfo['id'];

	$fieldNameInput = htmlBase::newElement('input')
		->setName('field_name[' . $langId . ']');

	if ($Field->Description->count() > 0){
		$fieldNameInput->setValue($Field->Description[$langId]['field_name']);
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
	->setName('input_required')
	->setChecked(($Field->input_required == 1));

$sizeOfOptions = $Field->Options->count();
$selectInputOptions = htmlBase::newElement('table')
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
			'text'   => '<span class="ui-icon ui-icon-circle-plus addSelectOption" tooltip="Add An Option" data-next_id="' . $sizeOfOptions . '" data-next_sort="' . $sizeOfOptions . '"></span>'
		)
	)
));

$lId = Session::get('languages_id');
foreach($Field->Options as $i => $Option){
	$nameInput = htmlBase::newElement('input')->addClass('text')->setType('text')->setName('option_name[' . $i . ']');
	$sortInput = htmlBase::newElement('input')->addClass('sort')->setType('hidden')->setName('option_sort[' . $i . ']');
	$dataInput = htmlBase::newElement('input')->addClass('data')->setType('hidden')->setName('option_data[' . $i . ']');

	$Option = $Option->Option;

	$nameInput->setValue($Option->Description[$lId]->option_name);
	$sortInput->setValue($Option->sort_order);
	$dataInput->setValue(urlencode($Option->extra_data));

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
	->addOption('select_other', 'Select Box With Other')
	->selectOptionByValue($Field->input_type);

if ($Field->input_type != 'select' && $Field->input_type != 'select_other' && $Field->input_type != 'select_address'){
	$optionsWrapper->css('display', 'none');
}

$finalTable = htmlBase::newElement('table')->setCellPadding('3')->setCellSpacing('0')->css('width', '100%');

if ($Field->field_id > 0){
	$finalTable->attr('field_id', $Field->field_id);
}

$finalTable->addBodyRow(array(
	'columns' => array(
		array(
			'addCls' => 'main',
			'text'   => '<b>' . sysLanguage::get('ENTRY_FIELD_IDENTIFIER') . '</b>'
		)
	)
));

$finalTable->addBodyRow(array(
	'columns' => array(
		array(
			'addCls' => 'main',
			'text'   => htmlBase::newInput()
			->setName('field_identifier')
			->val($Field->field_identifier)
			->draw()
		)
	)
));

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