<?php
$EmailTemplates = Doctrine_Core::getTable('EmailTemplates');
$toDelete = explode(',', $_GET['template_id']);
foreach($toDelete as $templateId){
	$Template = $EmailTemplates->find($templateId);
	if ($Template){
		$Template->delete();
	}
}

EventManager::attachActionResponse(array(
	'success' => true
), 'json');
