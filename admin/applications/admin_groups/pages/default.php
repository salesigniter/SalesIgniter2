<?php
$Qgroups = Doctrine_Query::create()
	->from('AdminGroups')
	->orderBy('admin_groups_name');

$TableGrid = htmlBase::newElement('newGrid')
	->usePagination(true)
	->setMainDataKey('group_id')
	->allowMultipleRowSelect(true)
	->setQuery($Qgroups);

$TableGrid->addButtons(array(
	htmlBase::newElement('button')->addClass('newButton')->usePreset('new'),
	htmlBase::newElement('button')->addClass('editButton')->usePreset('edit')->disable(),
	htmlBase::newElement('button')->addClass('deleteButton')->usePreset('delete')->disable()
));

$TableGrid->addHeaderRow(array(
	'columns' => array(
		array('text' => sysLanguage::get('TABLE_HEADING_GROUPS_NAME')),
		array('text' => sysLanguage::get('TABLE_HEADING_CUSTOMER_LOGIN_ALLOWED'))
	)
));

$Groups = &$TableGrid->getResults();
if ($Groups){
	foreach($Groups as $group){
		$TableGrid->addBodyRow(array(
			'rowAttr' => array(
				'data-group_id' => $group['admin_groups_id']
			),
			'columns' => array(
				array('text' => $group['admin_groups_name']),
				array('text' => ($group['customer_login_allowed'] == '1' ? 'Yes' : 'No'))
			)
		));
	}
}

echo $TableGrid->draw();
?>
<div id="appTips" title="Did You Know?">
	<ul>
		<li>You can hold the ctrl button down to select multiple groups</li>
		<li>You can press ctrl + a to select all groups</li>
		<li>You can double click on a row to edit the group</li>
	</ul>
</div>