<?php
$Qstatus = Doctrine_Query::create()
	->select('s.status_id, sd.status_name, s.status_types')
	->from('SystemStatuses s')
	->leftJoin('s.Description sd')
	->where('sd.language_id = ?', (int)Session::get('languages_id'))
	->orderBy('s.status_id');

$tableGrid = htmlBase::newGrid()
	->usePagination(true)
	->allowMultipleRowSelect(true)
	->setMainDataKey('status_id')
	->setQuery($Qstatus);

$tableGrid->addButtons(array(
	htmlBase::newElement('button')->usePreset('new')->addClass('newButton'),
	htmlBase::newElement('button')->usePreset('edit')->addClass('editButton')->disable(),
	htmlBase::newElement('button')->usePreset('delete')->addClass('deleteButton')->disable()
));

$tableGrid->addHeaderRow(array(
	'columns' => array(
		array('text' => sysLanguage::get('TABLE_HEADING_STATUS')),
		array('text' => sysLanguage::get('TABLE_HEADING_STATUS_TYPES'))
	)
));

$Statuses = &$tableGrid->getResults();
if ($Statuses){
	$id = 0;
	foreach($Statuses as $Status){
		$id = $Status['status_id'];

		$Qcheck = Doctrine_Query::create()
			->select('COUNT(*) AS count')
			->from('AccountsReceivableSales')
			->where('sale_status_id = ?', $id)
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

		$tableGrid->addBodyRow(array(
			'rowAttr' => array(
				'data-status_id'  => $id,
				'data-can_delete' => ($remove_status === true ? 'true' : 'false')
			),
			'columns' => array(
				array('text' => $Status['Description'][Session::get('languages_id')]['status_name']),
				array('text' => ucwords(implode(', ', explode(',', $Status['status_types']))))
			)
		));
	}
}

echo $tableGrid->draw();
