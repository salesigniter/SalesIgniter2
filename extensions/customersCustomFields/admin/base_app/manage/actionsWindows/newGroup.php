<?php
$Groups = Doctrine_Core::getTable('CustomersCustomFieldsGroups');

if (isset($_GET['group_id'])){
	$Group = $Groups->find((int)$_GET['group_id']);
}
else {
	$Group = $Groups->getRecord();
}

$infoBox = htmlBase::newElement('infobox');
$infoBox->setHeader('<b>' . ($Group->group_id > 0 ? sysLanguage::get('TEXT_ACTION_WINDOW_HEADING_EDIT_GROUP') : sysLanguage::get('TEXT_ACTION_WINDOW_HEADING_NEW_GROUP')) . '</b>');
$infoBox->setButtonBarLocation('top');

$saveButton = htmlBase::newElement('button')->attr('data-action', 'saveGroup')->addClass('saveButton')
	->usePreset('save');
$cancelButton = htmlBase::newElement('button')->addClass('cancelButton')->usePreset('cancel');

$infoBox->addButton($saveButton)->addButton($cancelButton);

$groupInput = htmlBase::newInput()
	->setName('group_name')
	->setRequired(true)
	->setValidation(true, '[a-zA-Z0-9 ]+')
	->val($Group->group_name);

$infoBox->addContentRow(sysLanguage::get('ENTRY_GROUP_NAME') . '<br>' . $groupInput->draw());

$sortableList = htmlBase::newElement('sortable_list')
	->css('margin', '5px');

$Fields = $Group->Fields;
if ($Fields && $Fields->count() > 0){
	foreach($Fields as $Field){
		$liObj = new htmlElement('li');
		$liObj->attr('id', 'field_' . $Field->Field->field_id)
			->attr('sort_order', $Field->sort_order)
			->html($Field->Field->Description[Session::get('languages_id')]->field_name);
		$sortableList->addItemObj($liObj);
	}
}
$infoBox->addContentRow('<br><br>Groups Fields - Drag And Drop To Sort');
$infoBox->addContentRow('<hr>');
$infoBox->addContentRow('<span class="ui-icon ui-icon-trash" style="vertical-align:middle;"></span><span style="margin-left:1em;vertical-align:middle;">Drop Here To Remove</span>');
$infoBox->addContentRow('<hr>');
$infoBox->addContentRow($sortableList->draw());

EventManager::notify('CustomersCustomFieldsNewEditGroupWindowBeforeDraw', $infoBox, $Group);

EventManager::attachActionResponse($infoBox->draw(), 'html');
