<?php
$EmailTemplates = Doctrine_Core::getTable('EmailTemplates');
if (isset($_GET['template_id'])){
	$Template = $EmailTemplates->find((int)$_GET['template_id']);
}
else {
	$Template = $EmailTemplates->create();
}

$Module = EmailModules::getModule($_POST['email_module']);

$Template->template_status = $_POST['template_status'];
$Template->template_name = $_POST['template_name'];
$Template->email_module = $_POST['email_module'];
$Template->email_module_event_key = $_POST['module_event_key'];
$Template->template_settings = json_encode(array(
	'global' => array(
		'send_to' => $_POST['send_to'],
		'send_duplicate_to' => $_POST['send_to_extra'],
		'attachment' => ($_POST['attachment_pdf'] != '' ? $_POST['attachment_pdf'] : false)
	),
	'module' => $Module->prepareEventSettingsJson($_POST)
));

foreach(sysLanguage::getLanguages() as $lInfo){
	if (!empty($_POST['email_subject'][$lInfo['id']])){
		$Template->Description[$lInfo['id']]->email_templates_subject = $_POST['email_subject'][$lInfo['id']];
		$Template->Description[$lInfo['id']]->email_templates_content = $_POST['email_text'][$lInfo['id']];
		$Template->Description[$lInfo['id']]->language_id = $lInfo['id'];
	}
}
$Template->save();

EventManager::attachActionResponse(array(
	'success' => true
), 'json');
