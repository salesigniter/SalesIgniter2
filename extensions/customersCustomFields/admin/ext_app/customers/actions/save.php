<?php
$FieldsToCustomers =& $Customer->Fields;
$FieldsToCustomers->delete();

if (isset($_POST['custom_fields'])){
	foreach($_POST['custom_fields'] as $fID => $val){
		$fieldValue = $val;

		$QfieldType = Doctrine_Query::create()
			->select('f.input_type')
			->from('CustomersCustomFields f')
			->where('f.field_id = ?', $fID)
			->execute(array(), Doctrine::HYDRATE_ARRAY);

		$FieldsToCustomers[$fID]->field_id = $fID;
		$FieldsToCustomers[$fID]->field_type = $QfieldType[0]['input_type'];
		$FieldsToCustomers[$fID]->value = $fieldValue;
	}
}

$Customer->save();
