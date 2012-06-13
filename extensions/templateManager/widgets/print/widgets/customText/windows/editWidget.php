<?php
$customText = (isset($WidgetSettings->custom_text) ? $WidgetSettings->custom_text : '');

$WidgetSettingsTable->addBodyRow(array(
	'columns' => array(
		array(
			'colspan' => 2,
			'text'    => '<b>Widget Properties</b>'
		)
	)
));

$WidgetSettingsTable->addBodyRow(array(
	'columns' => array(
		array(
			'valign' => 'top',
			'text'   => 'Enter Text:'
		),
		array('text' => '<textarea class="makeHtmlEditor simple" name="custom_text" style="width:300px;height:200px;">' . $customText . '</textarea>')
	)
));
