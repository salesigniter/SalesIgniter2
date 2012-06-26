<?php
$Templates = Doctrine_Core::getTable('EmailTemplates');
if (isset($_GET['template_id'])){
	$Template = $Templates->find((int)$_GET['template_id']);
	$boxHeading = sysLanguage::get('TEXT_INFO_HEADING_EDIT');
	$boxIntro = sysLanguage::get('TEXT_INFO_EDIT_INTRO');
	$Module = EmailModules::getModule($Template->email_module);
}
else {
	$Template = $Templates->getRecord();
	$boxHeading = sysLanguage::get('TEXT_INFO_HEADING_NEW');
	$boxIntro = sysLanguage::get('TEXT_INFO_INSERT_INTRO');
	$Module = EmailModules::getModule($_GET['module']);
}

$TemplateSettings = json_decode($Template->template_settings, true);

$infoBox = htmlBase::newElement('infobox');
$infoBox->setHeader('<b>' . $boxHeading . '</b>');
$infoBox->setButtonBarLocation('top');

$saveButton = htmlBase::newElement('button')->addClass('saveButton')->usePreset('save');
$cancelButton = htmlBase::newElement('button')->addClass('cancelButton')->usePreset('cancel');

$infoBox->addButton($saveButton)->addButton($cancelButton);

$infoBox->addContentRow($boxIntro);

$hiddenField = htmlBase::newInput()
	->setType('hidden')
	->setName('email_module')
	->setValue($Module->getCode());

$TemplateStatus = htmlBase::newRadio()
	->addGroup(array(
	'name' => 'template_status',
	'checked' => $Template->template_status,
	'separator' => '<br>',
	'data' => array(
		array('value' => '0', 'label' => 'Disabled', 'labelPosition' => 'after'),
		array('value' => '1', 'label' => 'Enabled', 'labelPosition' => 'after')
	)
));
$infoBox->addContentRow('<b>Template Status</b> ( When disabled it will not send an email for this template )<br>' . $TemplateStatus->draw() . $hiddenField->draw());

$TemplateName = htmlBase::newInput()
	->setName('template_name')
	->css('width', '100%')
	->val($Template->template_name);
$infoBox->addContentRow('<b>Template Name</b> ( Used only so you can identify different emails )<br>' . $TemplateName->draw());

$GlobalSendTo = htmlBase::newSelectbox()
	->setName('send_to')
	->addOption('admin', 'Administrator')
	->addOption('customer', 'Customer')
	->selectOptionByValue((isset($TemplateSettings['global']['send_to']) ? $TemplateSettings['global']['send_to'] : 'customer'));

$infoBox->addContentRow('<b>Send Email To</b><br>' . $GlobalSendTo->draw());

$GlobalSendToExtra = htmlBase::newTextarea()
	->setName('send_to_extra')
	->val((isset($TemplateSettings['global']['send_to_extra']) ? $TemplateSettings['global']['send_to_extra'] : ''));

$infoBox->addContentRow('<b>Send Duplicate Email To</b> ( example: me@mydomain.com;you@yourdomain.com )<br>' . $GlobalSendToExtra->draw());

$ModulesSettingsContainers = '';
$ModuleEvent = htmlBase::newSelectbox()
	->setName('module_event_key')
	->selectOptionByValue($Template->email_module_event_key);
foreach($Module->getEvents() as $eInfo){
	if (!isset($eventDescription)){
		$eventDescription = $eInfo['description'];
	}
	$ModuleEvent->addOption($eInfo['key'], $eInfo['text'], false, array(
		'data-description' => $eInfo['description']
	));
	if ($eInfo['key'] == $Template->email_module_event_key){
		$eventDescription = $eInfo['description'];
	}

	$ModuleSettings = $Module->getEventSettings(
		$eInfo['key'],
		($eInfo['key'] == $Template->email_module_event_key ? $TemplateSettings['module'] : array())
	);
	if ($ModuleSettings !== false){
		$display = ($eInfo['key'] == $Template->email_module_event_key ? 'block' : 'none');
		$ModulesSettingsContainers .= '<div class="eventSettings" id="event_settings_' . $eInfo['key'] . '" style="display:' . $display . ';">';
		foreach($ModuleSettings['fields'] as $Field){
			$ModulesSettingsContainers .= '<div><b>' . $Field['label'] . '</b><br>' . $Field['input']->draw() . '</div>';
		}
		$ModulesSettingsContainers .= '</div>';
	}
}
$infoBox->addContentRow('<b>Choose When To Send</b><br>' . $ModuleEvent->draw() . '<div id="when_to_send_desc" style="font-style:italic;font-size:.9em;font-weight:bold;">' . $eventDescription . '</div>');
$infoBox->addContentRow($ModulesSettingsContainers);

$standardVars = array();
$conditionVars = array();
$ModuleVariables = $Module->getEventVariables($Template->email_module_event_key);
foreach($ModuleVariables as $vInfo){
	if ($vInfo['conditional'] === true){
		if (!empty($vInfo['conditionCheck'])){
			$key = $vInfo['conditionCheck'];
		}
		else {
			$key = $vInfo['varName'];
		}
		$condition = '&lt;!-- if ($' . $key . ')<br />';
		$condition .= '&nbsp;&nbsp;&nbsp;$' . $vInfo['varName'] . '<br />';
		$condition .= '--&gt;';

		$conditionVars[] = $condition;
	}
	else {
		$standardVars[] = '$' . $vInfo['varName'];
	}
}

$infoBox->addContentRow('<table cellpadding="0" cellspacing="0" border="0" width="100%" id="varsTable">
	<tr>
		<td valign="top" width="33%">
			<div class="ui-widget-header" style="padding:.3em;">' . sysLanguage::get('HEADING_GLOBAL_VARS') . '</div>
			<div class="main globalVars" style="margin:.5em;">$store_name<br>$store_owner<br>$store_owner_email<br>$today_short<br>$today_long<br>$store_url<br></div>
		</td>
		<td valign="top" width="33%" style="padding: 0em 1em;">
			<div class="ui-widget-header" style="padding:.3em;">
				' . sysLanguage::get('HEADING_AVAIL_VARS') . '
			</div>
			<div class="main standardVars" style="margin:.5em;">
				' . implode('<br>', $standardVars) . '
			</div>
		</td>
		<td valign="top" width="33%">
			<div class="ui-widget-header" style="padding:.3em;">
				' . sysLanguage::get('HEADING_COND_VARS') . '
			</div>
			<div class="main conditionVars" style="margin:.5em;">
				' . implode('<br>', $conditionVars) . '
			</div>
		</td>
	</tr>
</table>');

$emailContentRow = '<div class="makeTabPanel" style="margin:.5em;">
	<ul>';

foreach(sysLanguage::getLanguages() as $lInfo){
	$emailContentRow .= '<li><a href="#tab_' . $lInfo['id'] . '"><span>' . $lInfo['showName']() . '</span></a></li>';
}

$emailContentRow .= '</ul>';

foreach(sysLanguage::getLanguages() as $lInfo){
	$emailContentRow .= '<div id="tab_' . $lInfo['id'] . '" lang_name="' . $lInfo['name'] . '">
		<b>Subject:</b>
		<input type="text" class="emailSubject" name="email_subject[' . $lInfo['id'] . ']" value="' . $Template->Description[$lInfo['id']]->email_templates_subject . '" style="width:80%"><br /><br />
		<textarea rows="20" cols="100" style="width:100%" name="email_text[' . $lInfo['id'] . ']" class="makeFCK">' . $Template->Description[$lInfo['id']]->email_templates_content . '</textarea><br /><br />
	</div>';
}

$emailContentRow .= '</div>';
$infoBox->addContentRow($emailContentRow);

EventManager::attachActionResponse($infoBox->draw(), 'html');
