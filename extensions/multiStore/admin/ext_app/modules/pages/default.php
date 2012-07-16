<?php
/*
	Multi Stores Extension Version 1
	
	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class multiStore_admin_modules_default extends Extension_multiStore
{

	public function __construct()
	{
		parent::__construct('multiStore');
	}

	public function load()
	{
		if ($this->isEnabled() === false){
			return;
		}

		EventManager::attachEvents(array(
			'ModuleEditWindowBeforeDraw'
		), null, $this);
	}

	public function ModuleEditWindowBeforeDraw(&$TabPanel, $moduleCode, $moduleType, ModuleConfigReader $ModuleConfig)
	{
		$multiStoreTabs = htmlBase::newElement('tabs')
			->addClass('makeTabPanel')
			->addClass('makeTabsVertical')
			->setId('storeTabs');
		$multiStoreTabs
			->addTabHeader('tab_global', array(
			'text' => 'Global'
		))
			->addTabPage('tab_global', array(
			'text' => $TabPanel->draw()
		));

		$GlobalInput = htmlBase::newRadio()
			->setValue('use_global')
			->setLabel('Use Global')
			->setLabelPosition('after');

		$CustomInput = htmlBase::newRadio()
			->setValue('use_custom')
			->setLabel('Use Custom')
			->setLabelPosition('after');

		$stores = $this->getStoresArray();
		foreach($stores as $sInfo){
			$radioSet = htmlBase::newRadioGroup()
				->setLabel('Configuration Method')
				->setLabelPosition('above')
				->setName('store_show_method[' . $sInfo['stores_id'] . ']')
				->setChecked('use_global')
				->addInput($GlobalInput->attr('onclick', '$(\'#configuration_tabs_' . $sInfo['stores_id'] . '\').hide()'))
				->addInput($CustomInput->attr('onclick', '$(\'#configuration_tabs_' . $sInfo['stores_id'] . '\').show()'));

			$Qcheck = Doctrine_Query::create()
				->select('count(*) as total')
				->from('StoresModulesConfiguration')
				->where('module_type = ?', $moduleType)
				->andWhere('module_code = ?', $moduleCode)
				->andWhere('store_id = ?', $sInfo['stores_id'])
				->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
			if ($Qcheck && $Qcheck[0]['total'] > 0){
				$radioSet->setChecked('use_custom');
			}

			$tabs = array();
			$tabsPages = array();
			$panelId = 1;
			$tabId = 1;
			foreach($ModuleConfig->getConfig() as $tabKey => $tabInfo){
				if (!isset($tabs[$tabKey])){
					$tabs[$tabKey] = array(
						'panelId'          => 'stores-panel-' . $panelId . '-page-' . $tabId,
						'panelHeader'      => $tabInfo['title'],
						'panelDescription' => $tabInfo['description'],
						'panelTable'       => htmlBase::newElement('table')
							->addClass('configTable')
							->setCellPadding(5)
							->setCellSpacing(0)
					);
					$tabId++;
				}

				foreach($tabInfo['config'] as $cfg){
					$Qconfig = Doctrine_Query::create()
						->select('configuration_key, configuration_value, store_id')
						->from('StoresModulesConfiguration')
						->where('module_type = ?', $moduleType)
						->andWhere('module_code = ?', $moduleCode)
						->andWhere('configuration_key = ?', $cfg->getKey())
						->andWhere('store_id = ?', $sInfo['stores_id'])
						->fetchOne();

					if ($Qconfig){
						$cfg->setValue($Qconfig->configuration_value);
					}

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
								'text'   => $ModuleConfig->getInputField($cfg, false, 'store_configuration[' . $sInfo['stores_id'] . ']'),
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

			$configurationTabs = htmlBase::newElement('tabs')
				->addClass('makeTabPanel')
				->setId('configuration_tabs_' . $sInfo['stores_id']);

			if (!$Qcheck || $Qcheck[0]['total'] <= 0){
				//$configurationTabs->hide();
			}

			foreach($tabs as $pInfo){
				$configurationTabs
					->addTabHeader($pInfo['panelId'], array('text' => $pInfo['panelHeader']))
					->addTabPage($pInfo['panelId'], array('text' => $pInfo['panelTable']));
			}

			$multiStoreTabs
				->addTabHeader('storeTabs_store_' . $sInfo['stores_id'], array(
				'text' => $sInfo['stores_name']
			))
				->addTabPage('storeTabs_store_' . $sInfo['stores_id'], array(
				'text' => 'Per store configurations are only different from the global if you have changed them<br>' .
					'otherwise they default back to the global settings automatically.<br /><br />' . $configurationTabs->draw()
			));
			$panelId++;
		}

		$TabPanel = $multiStoreTabs;
	}
}

?>