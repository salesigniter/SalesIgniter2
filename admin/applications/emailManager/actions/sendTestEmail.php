<?php
$toSend = explode(',', $_POST['template_id']);
$sendTo = $_POST['send_to'];
$Templates = Doctrine_Core::getTable('EmailTemplates');
foreach($toSend as $id){
	$Template = $Templates->find($id);

	$Module = EmailModules::getModule($Template->email_module);

	$Module->process($Template->email_module_event_key, array(
		'testMode' => true,
		'sendTo' => $sendTo
	));
}

EventManager::attachActionResponse(array(
	'success' => true
), 'json');
