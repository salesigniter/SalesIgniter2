<?php
	$CustomersCustomFields = Doctrine_Core::getTable('CustomersCustomFields');
	if (isset($_GET['fID'])){
		$Field = $CustomersCustomFields->find((int)$_GET['fID']);
	}else{
		$Field = $CustomersCustomFields->create();
	}

	$Field->input_type = $_POST['input_type'];
	$Field->search_key = $_POST['search_key'];
	$Field->show_on_site = (isset($_POST['show_on_site']) ? '1' : '0');
	$Field->show_on_tab = (isset($_POST['show_on_tab']) ? '1' : '0');
	$Field->show_on_listing = (isset($_POST['show_on_listing']) ? '1' : '0');
	$Field->show_name_on_listing = (isset($_POST['show_name_on_listing']) ? '1' : '0');
	$Field->show_on_labels = (isset($_POST['show_on_labels']) ? '1' : '0');
	$Field->labels_max_chars = $_POST['labels_max_chars'];
	$Field->include_in_search = (isset($_POST['include_in_search']) ? '1' : '0');
	EventManager::notify('CustomersCustomFieldsSaveOptions', &$Field);

	$FieldDescription =& $Field->Description;
	foreach($_POST['field_name'] as $lId => $fieldName){
		$FieldDescription[$lId]->field_name = $fieldName;
		$FieldDescription[$lId]->language_id = $lId;
	}

	$Field->save();

	$OptionsToFields = Doctrine_Query::create()
	->from('CustomersCustomFieldsOptionsToFields o2f')
	->leftJoin('o2f.Options o')
	->leftJoin('o.Description od')
	->where('o2f.field_id = ?', $Field->field_id)
	->execute();
	if ($OptionsToFields){
		$OptionsToFields->delete();
	}

	if ($_POST['input_type'] == 'select'){
		$lID = Session::get('languages_id');

		$i=0;
		foreach($_POST['option_name'] as $index => $val){
			if (!empty($val)){
				$Option = new CustomersCustomFieldsOptions();
				$Option->sort_order = $_POST['option_sort'][$index];

				$Option->Description[$lID]->option_name = $val;
				$Option->Description[$lID]->language_id = $lID;

				$Option->Options[]->field_id = $Field->field_id;

				$Option->save();
				$i++;
			}
		}
	}


	$iconCss = array(
 		'float'    => 'right',
		'position' => 'relative',
		'top'      => '-4px',
		'right'    => '-4px'
	);

 	$deleteIcon = htmlBase::newElement('icon')->setType('circleClose')->setTooltip('Click to delete field')
 	->setHref(itw_app_link('appExt=customersCustomFields&action=removeField&field_id=' . $Field->field_id))
 	->css($iconCss);

 	$editIcon = htmlBase::newElement('icon')->setType('wrench')->setTooltip('Click to edit field')
 	->setHref(itw_app_link('appExt=customersCustomFields&windowAction=edit&action=getFieldWindow&fID=' . $Field->field_id))
 	->css($iconCss);

	$newFieldWrapper = new htmlElement('div');
	$newFieldWrapper->css(array(
		'float'   => 'left',
		'width'   => '150px',
		'height'  => '50px',
		'padding' => '4px',
		'margin'  => '3px'
	))->addClass('ui-widget ui-widget-content ui-corner-all draggableField')
	->html('<b><span class="fieldName" field_id="' . $Field->field_id . '">' . $Field->Description[Session::get('languages_id')]['field_name'] . '</span></b>' . $deleteIcon->draw() . $editIcon->draw() . '<br />' . sysLanguage::get('TEXT_TYPE') . '<span class="fieldType">' . $Field->input_type . '</span><br />' . sysLanguage::get('TEXT_SHOWN_ON_SITE') . ($Field->show_on_site == '1' ? 'Yes' : 'No'));

	EventManager::attachActionResponse($newFieldWrapper->draw(), 'html');
?>