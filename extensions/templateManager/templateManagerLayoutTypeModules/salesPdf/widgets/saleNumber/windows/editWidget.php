<?php
$selectType = isset($WidgetSettings->type) ? $WidgetSettings->type : '';
$selectText = isset($WidgetSettings->text) ? $WidgetSettings->text : '';
$showRev = isset($WidgetSettings->showRevisionNumber);

$TypeSelect = '<select name="type">
<option value="top" ' . (($selectType == 'top') ? 'selected="selected"' : '') . '>Top</option>
<option value="bottom" ' . (($selectType == 'bottom') ? 'selected="selected"' : '') . '>Bottom</option>
<option value="left" ' . (($selectType == 'left') ? 'selected="selected"' : '') . '>Left</option>
<option value="right" ' . (($selectType == 'right') ? 'selected="selected"' : '') . '>Right</option>
</select> ';

$ShowRevCheckbox = htmlBase::newElement('checkbox')
	->setName('show_rev')
	->setChecked($showRev);

$textArea = htmlBase::newElement('textarea')
	->setName('text')
	->val($selectText)
	->attr('rows', '5')
	->attr('cols', '15');

$WidgetSettingsTable->addBodyRow(array(
	'columns' => array(
		array(
			'colspan' => 2,
			'text'    => '<b> Sale Number Widget Properties</b>'
		)
	)
));

$WidgetSettingsTable->addBodyRow(array(
		'columns' => array(
			array('text' => 'Show Revision:'),
			array('text' => $ShowRevCheckbox)
		)
	));

$WidgetSettingsTable->addBodyRow(array(
	'columns' => array(
		array('text' => 'Line Type:'),
		array('text' => $TypeSelect)
	)
));

$WidgetSettingsTable->addBodyRow(array(
	'columns' => array(
		array('text' => 'Text:'),
		array('text' => $textArea->draw())
	)
));
