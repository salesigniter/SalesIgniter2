<?php
$FieldsToCustomers =& $Customer->Fields;
$FieldsToCustomers->clear();

if (isset($_POST['customers_custom_field'])){
	foreach($_POST['customers_custom_field'] as $fID => $fieldValue){
		$QfieldType = Doctrine_Query::create()
			->select('f.input_type')
			->from('CustomersCustomFields f')
			->where('f.field_id = ?', $fID)
			->execute(array(), Doctrine::HYDRATE_ARRAY);

		$FieldsToCustomers[$fID]->field_id = $fID;
		$FieldsToCustomers[$fID]->field_type = $QfieldType[0]['input_type'];
		if (is_array($fieldValue)){
			$FieldsToCustomers[$fID]->value = implode(';', $fieldValue);
		}else{
			$FieldsToCustomers[$fID]->value = $fieldValue;
		}
	}
}

$Customer->save();
