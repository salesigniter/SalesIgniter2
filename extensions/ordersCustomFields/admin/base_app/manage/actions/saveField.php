<?php
$OrdersCustomFields = Doctrine_Core::getTable('OrdersCustomFields');
if (isset($_GET['fID'])){
	$Field = $OrdersCustomFields->find((int)$_GET['fID']);
}
else {
	$Field = $OrdersCustomFields->create();
}

$Field->field_identifier = $_POST['field_identifier'];
$Field->input_type = $_POST['input_type'];
$Field->input_required = (isset($_POST['input_required']));
$Field->display_order = $_POST['display_order'];

foreach($_POST['field_name'] as $lId => $fieldName){
	$Field->Description[$lId]->field_name = $fieldName;
	$Field->Description[$lId]->language_id = $lId;
}

$Field->Options->delete();

if ($_POST['input_type'] == 'select' || $_POST['input_type'] == 'select_other' || $_POST['input_type'] == 'select_address'){
	$lID = Session::get('languages_id');

	$i = 0;
	foreach($_POST['option_name'] as $index => $val){
		if (!empty($val)){
			$OptionToField = $Field->Options->getTable()->create();
			$OptionToField->display_order = $_POST['option_sort'][$index];

			$Option = $OptionToField->Option->getTable()->create();
			$Option->extra_data = urldecode($_POST['option_data'][$index]);

			$Option->Description[$lID]->option_name = $val;
			$Option->Description[$lID]->language_id = $lID;

			$OptionToField->Option = $Option;

			$Field->Options->add($OptionToField);
			$i++;
		}
	}
}

//echo '<pre>';print_r($Field->toArray(true));itwExit();
$Field->save();

$iconCss = array(
	'float'    => 'right',
	'position' => 'relative',
	'top'      => '-4px',
	'right'    => '-4px'
);

$deleteIcon = htmlBase::newElement('icon')->setType('circleClose')->setTooltip('Click to delete field')
	->setHref(itw_app_link('appExt=ordersCustomFields&action=removeField&field_id=' . $Field->field_id))
	->css($iconCss);

$editIcon = htmlBase::newElement('icon')->setType('wrench')->setTooltip('Click to edit field')
	->setHref(itw_app_link('appExt=ordersCustomFields&windowAction=edit&action=getFieldWindow&field_id=' . $Field->field_id))
	->css($iconCss);

$newFieldWrapper = new htmlElement('div');
$newFieldWrapper->css(array(
	'float'   => 'left',
	'width'   => '15em',
	'height'  => '10em',
	'padding' => '.5em',
	'margin'  => '.5em'
))->addClass('ui-widget ui-widget-content ui-corner-all draggableField')
	->html('<b><span class="fieldName" field_id="' . $Field->field_id . '">' . $Field->Description[Session::get('languages_id')]['field_name'] . '</span></b>' . $deleteIcon->draw() . $editIcon->draw() . '<br />' . sysLanguage::get('TEXT_TYPE') . '<span class="fieldType">' . $Field->input_type . '</span><br />Required: ' . ($Field->input_required == '1' ? 'Yes' : 'No') . '<br />Display Order: ' . $Field->display_order);

EventManager::attachActionResponse($newFieldWrapper->draw(), 'html');
?>