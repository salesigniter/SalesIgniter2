<?php
$WidgetProperties['tableColumns'] = array();
if (isset($_POST['columns'])){
	foreach($_POST['columns'] as $colCode){
		$WidgetProperties['tableColumns'][$colCode] = array(
			'code' => $colCode,
			'display_order' => $_POST['display_order'][$colCode]
		);
		if (isset($_POST['column_properties']) && isset($_POST['column_properties'][$colCode])){
			$WidgetProperties['tableColumns'][$colCode]['column_properties'] = $_POST['column_properties'][$colCode];
		}
	}
}
?>
