<?php
$infoBox = htmlBase::newElement('infobox');
$infoBox->setHeader('<b>' . sysLanguage::get('TEXT_INFO_HEADING_EDIT') . '</b>');
$infoBox->setButtonBarLocation('top');

$saveButton = htmlBase::newElement('button')->addClass('saveButton')->usePreset('save');
$cancelButton = htmlBase::newElement('button')->addClass('cancelButton')->usePreset('cancel');

$infoBox->addButton($saveButton)->addButton($cancelButton);

$Config = new ModuleConfigReader(
	$_GET['module'],
	$_GET['moduleType'],
	(isset($_GET['modulePath']) ? $_GET['modulePath'] : false)
);

$tabs = array();
$tabId = 1;
foreach($Config->getConfig() as $tabKey => $tabInfo){
	if (!isset($tabs[$tabKey])){
		$tabs[$tabKey] = array(
			'panelId' => 'page-' . $tabId,
			'panelHeader' => $tabInfo['title'],
			'panelDescription' => $tabInfo['description'],
			'panelTable' => htmlBase::newElement('table')
				->addClass('configTable')
				->setCellPadding(5)
				->setCellSpacing(0)
		);
		$tabId++;
	}

	foreach($tabInfo['config'] as $cfg){
		$tabs[$tabKey]['panelTable']->addBodyRow(array(
			'columns' => array(
				array(
					'text'   => '<span class="ui-icon ui-icon-blue ui-icon-alert" style="display:none" tooltip="This field has been edited"></span>',
					'addCls' => 'editedInfo',
					'valign' => 'top'
				),
				array(
					'text'   => '<b>' . $cfg->getTitle() . '</b>',
					'addCls' => 'main',
					'valign' => 'top'
				),
				array(
					'text'   => $Config->getInputField($cfg),
					'addCls' => 'main',
					'valign' => 'top'
				),
				array(
					'text'   => $cfg->getDescription(),
					'addCls' => 'main',
					'valign' => 'top'
				)
			)
		));
	}
}

EventManager::notify(
	'ModuleEditWindowAddFields',
	&$tabs,
	$_GET['module'],
	$_GET['moduleType'],
	$Config
);

$tabPanel = htmlBase::newElement('tabs')
	->addClass('makeTabPanel')
	->setId('module_tabs');
foreach($tabs as $pInfo){
	$tabPanel->addTabHeader($pInfo['panelId'], array('text' => $pInfo['panelHeader']))
		->addTabPage($pInfo['panelId'], array('text' => $pInfo['panelTable']));
}

EventManager::notify(
	'ModuleEditWindowBeforeDraw',
	&$tabPanel,
	$_GET['module'],
	$_GET['moduleType'],
	$Config
);

$infoBox->addContentRow($tabPanel->draw());

EventManager::attachActionResponse($infoBox->draw(), 'html');
?>
