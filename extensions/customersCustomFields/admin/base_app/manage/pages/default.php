<div class="mainContainer">
	<div class="ui-widget ui-widget-content ui-corner-all column fieldsColumn">
		<div class="fieldsContainer">
			<?php
			$FieldsGrid = htmlBase::newGrid()
				->allowMultipleRowSelect(true)
				->setId('fields_grid')
				->setMainDataKey('field_id');

			$FieldsGrid->addButtons(array(
				htmlBase::newElement('button')->addClass('newButton')->attr('data-action_window', 'newField')
					->usePreset('new'),
				htmlBase::newElement('button')->addClass('editButton')->attr('data-action_window', 'newField')
					->usePreset('edit')->disable(),
				htmlBase::newElement('button')->addClass('deleteButton')->usePreset('delete')->disable()
			));

			$FieldsGrid->addHeaderRow(array(
				'columns' => array(
					array('text' => sysLanguage::get('TABLE_HEADING_FIELD_NAME')),
					array('text' => sysLanguage::get('TABLE_HEADING_FIELD_TYPE')),
					array('text' => sysLanguage::get('TABLE_HEADING_SHOWN_ON_CUSTOMER_ACCOUNT'))
				)
			));

			$Qfields = Doctrine_Query::create()
				->from('CustomersCustomFields f')
				->leftJoin('f.Description fd')
				->where('fd.language_id = ?', Session::get('languages_id'))
				->execute();
			if ($Qfields->count() > 0){
				foreach($Qfields->toArray(true) as $fInfo){
					$fieldId = $fInfo['field_id'];
					$fieldName = $fInfo['Description'][Session::get('languages_id')]['field_name'];
					$fieldData = $fInfo['field_data'];

					$FieldsGrid->addBodyRow(array(
						'rowAttr' => array(
							'data-field_id' => $fieldId
						),
						'columns' => array(
							array('text' => $fieldName),
							array('text' => $fieldData->type),
							array('text' => ($fieldData->show_on->customer_account == '1' ? 'Yes' : 'No'))
						)
					));
				}
			}
			echo $FieldsGrid->draw();
			?>
		</div>
	</div>
	<div class="ui-widget column buttonsColumn">
		<?php
		echo htmlBase::newElement('button')
			->setIcon('circleTriangleEast')
			->setText('')
			->setId('fieldToGroup')
			->disable()
			->draw();
		?>
	</div>
	<div class="ui-widget ui-widget-content ui-corner-all column groupsColumn">
		<div class="groupsContainer">
			<?php
			$GroupsGrid = htmlBase::newGrid()
				->setId('groups_grid')
				->setMainDataKey('group_id');

			$GroupsGrid->addButtons(array(
				htmlBase::newElement('button')->addClass('newButton')->attr('data-action_window', 'newGroup')
					->usePreset('new'),
				htmlBase::newElement('button')->addClass('editButton')->attr('data-action_window', 'newGroup')
					->usePreset('edit')->disable(),
				htmlBase::newElement('button')->addClass('deleteButton')->usePreset('delete')->disable()
			));

			$GroupsGrid->addHeaderRow(array(
				'columns' => array(
					array('text' => 'Group Name'),
					array('text' => 'Group Fields')
				)
			));

			$Qgroups = Doctrine_Query::create()
				->select('group_id, group_name')
				->from('CustomersCustomFieldsGroups')
				->orderBy('group_name')
				->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
			if ($Qgroups){
				foreach($Qgroups as $gInfo){
					$groupId = $gInfo['group_id'];
					$groupName = $gInfo['group_name'];

					$sortableList = htmlBase::newElement('sortable_list')
						->css('margin', '5px');

					$Qfields = Doctrine_Query::create()
						->select('f.field_id, fd.field_name, f2g.sort_order')
						->from('CustomersCustomFields f')
						->leftJoin('f.Description fd')
						->leftJoin('f.Groups f2g')
						->where('fd.language_id = ?', Session::get('languages_id'))
						->andWhere('f2g.group_id = ?', $groupId)
						->orderBy('f2g.sort_order')
						->execute();
					if ($Qfields->count() > 0){
						foreach($Qfields->toArray(true) as $fInfo){
							$liObj = new htmlElement('li');
							$liObj->attr('id', 'field_' . $fInfo['field_id'])
								->attr('sort_order', $fInfo['Groups'][0]['sort_order'])
								->html($fInfo['Description'][Session::get('languages_id')]['field_name']);
							$sortableList->addItemObj($liObj);
						}
					}

					$GroupsGrid->addBodyRow(array(
						'rowAttr' => array(
							'data-group_id' => $groupId
						),
						'columns' => array(
							array('text' => $groupName),
							array('text' => $sortableList->draw())
						)
					));
				}
			}
			echo $GroupsGrid->draw();
			?>
		</div>
	</div>
</div>
