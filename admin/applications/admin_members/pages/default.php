<?php
$Qadmin = Doctrine_Query::create()
	->from('Admin a')
	->leftJoin('a.AdminGroups ag.')
	->orderBy('a.admin_firstname');

$tableGrid = htmlBase::newElement('newGrid')
	->usePagination(true)
	->setMainDataKey('admin_id')
	->allowMultipleRowSelect(true)
	->setQuery($Qadmin);

$tableGrid->addButtons(array(
	htmlBase::newElement('button')->usePreset('new')->addClass('newButton'),
	htmlBase::newElement('button')->usePreset('edit')->addClass('editButton passProtect')->disable(),
	htmlBase::newElement('button')->usePreset('delete')->addClass('deleteButton passProtect')->disable()
));

$tableGrid->addHeaderRow(array(
	'columns' => array(
		array('text' => sysLanguage::get('TABLE_HEADING_NAME')),
		array('text' => sysLanguage::get('TABLE_HEADING_EMAIL')),
		array('text' => sysLanguage::get('TABLE_HEADING_GROUPS')),
		array('text' => sysLanguage::get('TABLE_HEADING_INFO'))
	)
));

$infoBoxes = array();
$allGetParams = tep_get_all_get_params(array('mID', 'action'));

$admin = &$tableGrid->getResults();
if ($admin){
	foreach($admin as $aInfo){
		$adminId = $aInfo['admin_id'];
		$adminFirstName = $aInfo['admin_firstname'];
		$adminLastName = $aInfo['admin_lastname'];
		$adminEmail = $aInfo['admin_email_address'];
		$adminGroupName = $aInfo['AdminGroups']['admin_groups_name'];
		$adminLogNum = $aInfo['admin_lognum'];
		$adminDateCreated = $aInfo['admin_created']->format(sysLanguage::getDateFormat('short'));
		$adminDateModified = $aInfo['admin_modified']->format(sysLanguage::getDateFormat('short'));
		$adminDateLastLogin = $aInfo['admin_logdate']->format(sysLanguage::getDateFormat('short'));

		$tableGrid->addBodyRow(array(
			'rowAttr' => array(
				'data-admin_id' => $adminId
			),
			'columns' => array(
				array('text' => $adminFirstName . '&nbsp;' . $adminLastName),
				array('text' => $adminEmail),
				array('text' => $adminGroupName),
				array(
					'text'  => htmlBase::newElement('icon')->setType('info')->draw(),
					'align' => 'center'
				)
			)
		));

		$tableGrid->addBodyRow(array(
			'addCls'  => 'gridInfoRow',
			'columns' => array(
				array(
					'colspan' => 4,
					'text'    => '<table cellpadding="1" cellspacing="0" border="0" width="75%">' .
						'<tr>' .
						'<td><b>' . sysLanguage::get('TEXT_INFO_FULLNAME') . '</b></td>' .
						'<td>' . $adminFirstName . ' ' . $adminLastName . '</td>' .
						'<td><b>' . sysLanguage::get('TEXT_INFO_EMAIL') . '</b></td>' .
						'<td>' . $adminEmail . '</td>' .
						'</tr>' .
						'<tr>' .
						'<td><b>' . sysLanguage::get('TEXT_INFO_GROUP') . '</b></td>' .
						'<td>' . $adminGroupName . '</td>' .
						'<td><b>' . sysLanguage::get('TEXT_INFO_LOGNUM') . '</b></td>' .
						'<td>' . $adminLogNum . '</td>' .
						'</tr>' .
						'<tr>' .
						'<td><b>' . sysLanguage::get('TEXT_INFO_CREATED') . '</b></td>' .
						'<td>' . $adminDateCreated . '</td>' .
						'<td><b>' . sysLanguage::get('TEXT_INFO_LOGDATE') . '</b></td>' .
						'<td>' . $adminDateLastLogin . '</td>' .
						'</tr>' .
						'<tr>' .
						'<td><b>' . sysLanguage::get('TEXT_INFO_MODIFIED') . '</b></td>' .
						'<td>' . $adminDateModified . '</td>' .
						'<td></td>' .
						'<td></td>' .
						'</tr>' .
						'</table>'
				)
			)
		));
	}
}
?>
<div class="ui-widget ui-widget-content ui-corner-all" style="margin-right:5px;margin-left:5px;">
	<div style="margin:5px;"><?php echo $tableGrid->draw();?></div>
</div>
